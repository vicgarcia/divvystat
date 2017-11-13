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
        'pk.eyJ1IjoiYnNjdGVjaG5vbG9neSIsImEiOiJvOTlLTXVnIn0.24fpc2xTfThxnIMZ1n0egQ';

    var generatePopupHtml = function(station) {
        return ' ' +
          '<div id="markerBox-' + station.landmark + '" class="markerBox">' +
            '<h2>' + station.name + '</h2>' +
            '<h4 id="markerCapacity-' + station.landmark + '">&nbsp;</h4>' +
            '<div class="markerTimelineHeader">available bikes over previous 72 hours</div>' +
            '<div id="markerTimeline-' + station.landmark + '" class="markerTimeline"></div>' +
            '<div class="markerGraphHeader">average weekday usage for last 30 days</div>' +
            '<div id="markerGraph-' + station.landmark + '" class="markerGraph"></div>' +
          '</div>';
    };

    var drawCapacity = function(landmark, data) {
        var element = '#markerCapacity-' + landmark,
            output = data.bikes + ' bikes / ' + data.docks + ' docks';
        $(element).text(output);
    };

    var drawTimeline = function(landmark, data) {
        return new Morris.Line({       /* timeline chart */
            element: 'markerTimeline-' + landmark,
            data: data,
            xkey: 'timestamp',
            ykeys: ['bikes'],
            lineColors: ['#00A7E2'],
            labels: ['Available Bikes'],
            gridTextSize: 8,
            hideHover: 'always'
        });
    };

    var drawGraph = function(landmark, data) {
        return new Morris.Bar({        /* day of week bar graph */
            element: 'markerGraph-' + landmark,
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
        var tile_url = 'http://{s}.tiles.mapbox.com/v4/bsctechnology.k2p1dpj1/{z}/{x}/{y}'
                + (L.Browser.retina ? '@2x.png' : '.png')
                + '?access_token='
                + L.mapbox.accessToken;
        var tiles = L.tileLayer(tile_url);
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
                L.marker([ station.lat, station.lng ], { icon: icon })
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
                    $('#markerBox-' + station.landmark).block({
                        message: '<h2>loading...</h2>',
                        css: { backgroundColor: 'white', border: 'none' },
                        overlayCSS: { backgroundColor: 'white', opacity: 1 },
                        fadeIn: 0,
                        fadeOut: 500
                    });
                    $.getJSON('/stations/' + station.landmark, function(report) {
                        drawCapacity(station.landmark, report.capacity);
                        drawTimeline(station.landmark, report.timeline);
                        drawGraph(station.landmark, report.graph);
                        $('#markerBox-' + station.landmark).unblock();
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
