module.exports = function(grunt) {

    // load plugins
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-sass');


    // configure
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        sass: {
            dist: {
                files: {
                    'public/style.css': 'templates/style.scss'
                }
            }
        },
        watch: {
            style: {
                files: 'templates/*',
                tasks: ['sass']
            }
        }
    });

    // default task
    grunt.registerTask('default', []);
};
