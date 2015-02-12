to deploy ...

initial install :
    clone the repository into /opt/divvystat
    create database (mysql), create tables (structure.sql)
    populate the stations table (scripts)
    configure redis and mysql in ./config files
    install node stuff (grunt mostly) with 'npm install'
    install bourbon/neat in ./templates/sass/lib

to recompile and deploy :
    switch to root, change into install dir
        'sudo su' then 'cd /opt/divvystat'
    get the lasted stuff in master from git server
        'git pull origin master'
    update bower dependencies and install 3rd party libraries
        'bower update --allow-root && bower-installer'
    recompile js and sass with grunt
        'grunt build'
    restart php server
        'service php5-fpm restart'


