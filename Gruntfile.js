module.exports = function(grunt) {

    // load plugins
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.loadNpmTasks('grunt-contrib-requirejs');


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
        watch: {
            style: {
                files: ['templates/sass/*', 'templates/js/*'],
                tasks: ['sass', 'requirejs']
            }
        }
    });

    // default task
    grunt.registerTask('default', []);
};
