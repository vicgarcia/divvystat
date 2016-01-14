<?php
namespace DivvyStat;

use \Carbon\Carbon;
use \Requests;

class Tasks
{
    public static function updateStations(DB $db)
    {
        $api = new Api;
        foreach ($api->getLiveStationData() as $stationData) {
            $db->insertUpdateStation(
                $stationData->landMark,
                $stationData->stationName,
                $stationData->latitude,
                $stationData->longitude
            );
        }
    }

    public static function recordData(DB $db)
    {
        $api = new Api;

        foreach ($api->getLiveStationData() as $station) {
            if (!isset($timestamp)) {
                $timestamp =
                    \DateTime::createFromFormat('Y-m-d h:i:s a', $station->timestamp)
                        ->format('Y-m-d H:i:s');
            }

            $stationId = $station->landMark;
            $totalDocks = $station->totalDocks;
            $availableBikes = $station->availableBikes;

            $db->insertAvailability(
                $stationId,
                $station->statusKey,
                $totalDocks,
                $availableBikes,
                $timestamp
            );
        }
    }

    public static function primeCache(DB $db)
    {
        $url = 'http://divvystat.us/';
        $cache = new \Kaavii\Cache(\Kaavii\Redis::connect());

        // delete stations cache, make request to reprime and get ids to loop thru
        $stations = $db->getStationsData();
        $cache->delete('stations');     // cached for 10 by app, reprime every 5
        $cache->save('stations', $stations, 600);
    }

    public static function dailyCache(DB $divvy)
    {
        $url = 'http://divvystat.us/stations';
        $cache = new \Kaavii\Cache(\Kaavii\Redis::connect());

        $stations = json_decode(Requests::get($url)->body);
        foreach ($stations as $station) {
            $key = 'graph_' . $station->id;
            $graph = $divvy->getRecentUsageBar($station->id);
            $cache->delete($key);
            $cache->save($key, $graph, 86400);
        }
    }

    public static function archiveData(DB $db)
    {
        // use todays date to determine the last month to archive
        $today = Carbon::now();
        // we always keep the data for the prior month for use with last 30 day reporting
        // after the 5th of the month, we archive the month 2 months prior to now
        if ($today->day > 5) {
            $endingMonth = $today->month > 2 ? $today->month - 2 : $today->month + 10;
            $endingYear = $today->month > 2 ? $today->year : $today->year - 1;
        } else {
            $endingMonth = $today->month > 3 ? $today->month - 3 : $today->month + 9;
            $endingYear = $today->month > 3 ? $today->year : $today->year - 1;
        }

        // use the oldest record in the database to determine the first month to drop
        $oldestRecordDate = Carbon::parse($db->getOldestRecordDate());
        $startingMonth = $oldestRecordDate->month;
        $startingYear = $oldestRecordDate->year;

        // build an array of start/end dates for archives to generate
        $addMonth = $startingMonth;
        $addYear = $startingYear;
        $archiveDates = [];
        do {
            // array with start and end date params
            $archiveDate = [];
            if ($addYear < $endingYear) {
                // add first param, start date
                $archiveDate[] = $addYear . '-' . str_pad($addMonth, 2, '0', STR_PAD_LEFT) . '-01';
                // advance counters
                if ($addMonth < 12) {
                    $addMonth = $addMonth + 1;
                } else {
                    $addMonth = 1;
                    $addYear = $addYear + 1;
                }
                // add second param, end date
                $archiveDate[] = $addYear . '-' . str_pad($addMonth, 2, '0', STR_PAD_LEFT) . '-01';
            } else {
                if ($addMonth <= $endingMonth) {
                    // add first param, start date
                    $archiveDate[] = $addYear . '-' . str_pad($addMonth, 2, '0', STR_PAD_LEFT) . '-01';
                    // advance counters
                    $addMonth = $addMonth + 1;
                    // add second param, end date
                    $archiveDate[] = $addYear . '-' . str_pad($addMonth, 2, '0', STR_PAD_LEFT) . '-01';
                } else {
                    break;
                }
            }
            $archiveDates[] = $archiveDate;
        } while(1);

        // end process if we have no archive dates
        if (empty($archiveDates)) {
            return;
        }

        // get mysql credentials, build command
        $credentials = $db->getCredentials();
        $user = $credentials['username'];
        $pass = $credentials['password'];
        $database = $credentials['database'];
        $mysqlCommand = "mysql -u$user -p$pass $database";

        // iterate over aggregated dates
        foreach ($archiveDates as $archiveDate) {
            // parse start/end dates
            $start = $archiveDate[0];
            $end = $archiveDate[1];

            // assemble sql command for archive availabilitys csv file data output
            $sqlCommand = "echo \"select * from availabilitys where timestamp between '$start' and '$end' order by timestamp asc\"";

            // sed command with filename for csv output
            $csvFile = substr($start, 0, -3);
            $sedCommand = "sed 's/\\t/,/g' > availabilitys_$csvFile.csv";

            // execute command for db -> csv
            $command = "$sqlCommand | $mysqlCommand | $sedCommand";
            exec($command);

            // assemble sql command for delete of archived data
            $sqlCommand = "echo \"delete from availabilitys where timestamp < '$end'\"";

            // execute command for db delete
            $command = "$sqlCommand | $mysqlCommand";
            exec($command);
        }
    }
}
