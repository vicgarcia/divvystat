require.config({

    paths: {
        'jquery': 'lib/jquery/jquery',
        'blockui': 'lib/blockui/jquery.blockUI',
        'leaflet': 'lib/leaflet/leaflet',
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
        'leaflet': {
            exports: 'L'
        },
        'fullscreen': {
            deps: ['leaflet'],
            exports: 'L.map'
        },
        'markers': {
            deps: ['leaflet'],
            exports: 'L.AwesomeMarkers'
        },
        'morris': {
            deps: ['raphael', 'jquery'],
            exports: 'Morris'
        }
    }

});

require(['app'], function(app) { app.run(); });
