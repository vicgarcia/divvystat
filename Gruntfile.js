module.exports = function(grunt) {

    // load plugins
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-requirejs');
    grunt.loadNpmTasks('grunt-contrib-copy');


    // configure
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
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
            main: {
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
    grunt.registerTask('build', ['requirejs', 'sass', 'copy']);
};
