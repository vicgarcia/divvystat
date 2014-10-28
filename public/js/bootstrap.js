require.config({

    paths: {
        jquery: 'libs/jquery/jquery',
        blockui: 'libs/blockui/jquery.blockUI',
        leaflet: 'libs/leaflet/leaflet',
        fullscreen: 'libs/leaflet.fullscreen/Control.FullScreen',
        markers: 'libs/Leaflet.awesome-markers/leaflet.awesome-markers',
        raphael: 'libs/raphael/raphael',
        morris: 'libs/morris/morris',
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
