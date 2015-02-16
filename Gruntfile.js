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
            bower: 'bower update --allow-root && bower-installer'
        },
        sass: {
            dist: {
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
            }
        },
        watch: {
            style: {
                files: ['templates/sass/*', 'templates/js/*'],
                tasks: ['build']
            }
        }
    });

    // default task
    grunt.registerTask('default', []);
    grunt.registerTask('build', ['exec:bower', 'sass', 'requirejs', 'copy:requirejs']);
};
