define([
    'jquery',
    'leaflet',
    'morris',
    'blockui',
    'fullscreen',
    'markers',
], function($, L, Morris) {
    'use strict';

    var generatePopupHtml = function(s) {
        return ' ' +
          '<div id="markerBox-' + s['id'] + '" class="markerBox">' +
            '<h2>' + s['name'] + '</h2>' +
            '<h4>' +
                s['bikes'] + ' bikes available / ' + s['docks'] + ' docks' +
            '</h4>' +
            '<div class="graphNote">available bikes over previous 72 hours</div>' +
            '<div id="markerTimeline-' + s['id'] + '" class="markerTimeline"></div>' +
            '<div class="graphNote">day of week average usage for last 30 days</div>' +
            '<div id="markerGraph-' + s['id'] + '" class="markerGraph"></div>' +
          '</div>';
    };

    var drawTimeLine = function(id, data) {
        return new Morris.Line({       /* timeline chart */
            element: 'markerTimeline-' + id,
            data: data,
            xkey: 'timestamp',
            ykeys: ['bikes'],
            lineColors: ['#00A7E2'],
            labels: ['Available Bikes'],
            gridTextSize: 8,
            hideHover: true
        });
    };

    var drawDaysGraph = function(id, data) {
        return new Morris.Bar({        /* day of week bar graph */
            element: 'markerGraph-' + id,
            data: data,
            xkey: 'day',
            ykeys: ['usage'],
            barColors: ['#00A7E2'],
            labels: ['usage'],
            gridTextSize: 8,
            hideHover: true
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
        map.addLayer(
            L.tileLayer('http://{s}.tile2.opencyclemap.org/transport/{z}/{x}/{y}.png')
        );
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
                L.marker([ station['lat'], station['lng'] ], { icon: icon })
                .addTo(map)
                .bindPopup(generatePopupHtml(station), { closeOnClick: false })
                .on('click', function(e) {
                    this.openPopup();
                    $('#markerBox-' + station['id']).block({
                        message: '<h2>loading...</h2>',
                        css: { backgroundColor: 'white', border: 'none' },
                        overlayCSS: { backgroundColor: 'white', opacity: 1 },
                        fadeIn: 0,
                        fadeOut: 500
                    });
                    $.getJSON('/stations/' + station['id'], function(report) {
                        drawTimeLine(station['id'], report['timeline']);
                        drawDaysGraph(station['id'], report['graph']);
                        $('#markerBox-' + station['id']).unblock();
                    });
                });
            });
        });

        /* load data from json-api for outage tracking charts */
        $.getJSON('/outages', function(data) {
            new Morris.Line({
                element: 'outage-line',
                data: data['line'],
                xkey: 'timestamp',
                ykeys: ['outages'],
                lineColors: ['#00A7E2'],
                labels: ['stations w/ outage'],
                gridTextSize: 8,
                hideHover: true
            });

            new Morris.Bar({
                element: 'outage-bar',
                data: data['bar'],
                xkey: 'day',
                ykeys: ['outages'],
                barColors: ['#00A7E2'],
                labels: ['avg stations w/ outages'],
                gridTextSize: 8,
                hideHover: true
            });
        });
    };

    return { run: run };
});
