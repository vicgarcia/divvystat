define([
    'jquery',
    'mapbox',
    'morris',
    'blockui',
    'fullscreen',
    'markers',
], function($, L, Morris) {
    'use strict';

    L.mapbox.accessToken =
        'pk.eyJ1IjoiYnNjdGVjaG5vbG9neSIsImEiOiJja2hvYXJjMHcwMDJtMnJ0a3h6c2pybGxiIn0.negTKrssVwBNqRY-cnl5zg';

    var generatePopupHtml = function(station) {
        return ' ' +
          '<div id="markerBox-' + station.id + '" class="markerBox">' +
            '<h2>' + station.name + '</h2>' +
            '<h4 id="markerCapacity-' + station.id + '">&nbsp;</h4>' +
            '<div class="markerTimelineHeader">available bikes over previous 72 hours</div>' +
            '<div id="markerTimeline-' + station.id + '" class="markerTimeline"></div>' +
            '<div class="markerGraphHeader">average weekday usage for last 30 days</div>' +
            '<div id="markerGraph-' + station.id + '" class="markerGraph"></div>' +
          '</div>';
    };

    var drawCapacity = function(stationId, data) {
        var element = '#markerCapacity-' + stationId,
            output = data.bikes + ' bikes / ' + data.docks + ' docks';
        $(element).text(output);
    };

    var drawTimeline = function(stationId, data) {
        return new Morris.Line({
            element: 'markerTimeline-' + stationId,
            data: data,
            xkey: 'timestamp',
            ykeys: ['bikes'],
            lineColors: ['#00A7E2'],
            labels: ['Available Bikes'],
            gridTextSize: 8,
            hideHover: 'always'
        });
    };

    var drawGraph = function(stationId, data) {
        return new Morris.Bar({
            element: 'markerGraph-' + stationId,
            data: data,
            xkey: 'day',
            ykeys: ['usage'],
            barColors: ['#00A7E2'],
            labels: ['usage'],
            gridTextSize: 8,
            hideHover: 'always'
        });
    };


    var run = function() {
        /* create map and add to ui */
        var map = L.map('map', {
            fullscreenControl: true,
            attributionControl: false,
            minZoom: 13,
            maxZoom: 16,
        });
        var tile_url = `https://api.mapbox.com/styles/v1/mapbox/streets-v11/tiles/{z}/{x}/{y}?access_token=${L.mapbox.accessToken}`;
        var tiles = L.tileLayer(tile_url, {tileSize: 512, zoomOffset: -1});
        map.addLayer(tiles);
        map.setView([41.90, -87.64], 14);

        /* load markers from json-api for stations on map */
        $.getJSON('/stations', function(data) {
            $.each(data, function(key, station) {
                var icon = L.AwesomeMarkers.icon({
                    prefix: 'fa',
                    icon: 'none',
                    iconColor: 'white',
                    markerColor: 'blue'
                });
                L.marker([ station.latitude, station.longitude ], { icon: icon })
                .addTo(map)
                .bindPopup(generatePopupHtml(station), {
                    autoPanPaddingTopLeft: L.point(60, 40),
                    autoPanPaddingBottomRight: L.point(20, 20),
                    closeOnClick: false,
                    maxWidth: 400,
                    minWidth: 180
                })
                .on('click', function(e) {
                    this.openPopup();
                    $('#markerBox-' + station.id).block({
                        message: '<h2>loading...</h2>',
                        css: { backgroundColor: 'white', border: 'none' },
                        overlayCSS: { backgroundColor: 'white', opacity: 1 },
                        fadeIn: 0,
                        fadeOut: 500
                    });
                    $.getJSON('/stations/' + station.id, function(report) {
                        drawCapacity(station.id, report.capacity);
                        drawTimeline(station.id, report.timeline);
                        drawGraph(station.id, report.graph);
                        $('#markerBox-' + station.id).unblock();
                    });
                });
            });
        });

        /* handle browser window resize */
        $(window).resize(function() {
            var close = $(".leaflet-popup-close-button")[0];
            if (close) close.click();
        });
    };

    return { run: run };
});
