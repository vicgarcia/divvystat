require.config({

    paths: {
        'jquery': 'lib/jquery/jquery',
        'blockui': 'lib/blockui/jquery.blockUI',
        'mapbox': 'lib/mapbox.js/mapbox',
        'fullscreen': 'lib/leaflet.fullscreen/Control.FullScreen',
        'markers': 'lib/Leaflet.awesome-markers/leaflet.awesome-markers',
        'raphael': 'lib/raphael/raphael',
        'morris': 'lib/morrisjs/morris',
    },

    shim: {
        'jquery': {
            exports: '$'
        },
        'blockui': {
            deps: ['jquery'],
            exports: '$'
        },
        'mapbox': {
            exports: 'L'
        },
        'fullscreen': {
            deps: ['mapbox'],
            exports: 'L.map'
        },
        'markers': {
            deps: ['mapbox'],
            exports: 'L.AwesomeMarkers'
        },
        'morris': {
            deps: ['raphael', 'jquery'],
            exports: 'Morris'
        }
    }

});

require(['app'], function(app) { app.run(); });
