module.exports = function(grunt) {

    // load plugins
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-exec');


    // configure
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        exec: {
            mklib: 'mkdir templates/sass/lib',
            bower: 'bower update --allow-root && bower-installer',
            bourbon: 'cd templates/sass/lib && bourbon install',
            neat: 'cd templates/sass/lib && neat install'
        },
        sass: {
            dist: {
                options: {
                    require: ['sass-css-importer'],
                    sourcemap: 'none'
                },
                files: {
                    'public/style.css': 'templates/sass/style.scss'
                }
            }
        },
        requirejs: {
            compile: {
                options: {
                    baseUrl: "templates/js",
                    mainConfigFile: "templates/js/main.js",
                    name: "main",
                    out: "public/main.js"
                }
            }
        },
        copy: {
            requirejs: {
                src: 'bower_components/requirejs/require.js',
                dest: 'public/require.js'
            },
            fullscreenicons: {
                expand: true,
                flatten: true,
                src: 'templates/sass/lib/leaflet.fullscreen/*.png',
                dest: 'public/'
            },
            markers: {
                expand: true,
                flatten: true,
                src: 'templates/sass/lib/Leaflet.awesome-markers/images/*',
                dest: 'public/images'
            }
        },
        watch: {
            style: {
                files: ['templates/sass/*', 'templates/js/*'],
                tasks: ['compile']
            }
        }
    });
    grunt.registerTask('bourbon', 'ensure bourbon/neat sass libs', function() {
        if (!grunt.file.exists('templates/sass/lib')) {
            grunt.task.run('exec:mklib');
        }
        if (!grunt.file.exists('templates/sass/lib/bourbon')) {
            grunt.task.run('exec:bourbon');
        }
        if (!grunt.file.exists('templates/sass/lib/neat')) {
            grunt.task.run('exec:neat');
        }
    });

    grunt.registerTask('default', []);

    grunt.registerTask('build', [
        'exec:bower',
        'bourbon',
        'sass',
        'requirejs',
        'copy:requirejs',
        'copy:fullscreenicons',
        'copy:markers'
    ]);

    grunt.registerTask('compile', [
        'sass',
        'requirejs',
        'copy:requirejs',
        'copy:fullscreenicons',
        'copy:markers'
    ]);

};
