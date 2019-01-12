FROM debian:9-slim
RUN apt-get update -qq
# install basic tools
RUN apt-get install -y -qq curl mysql-client zip unzip build-essential libssl-dev git vim-nox redis-tools
# create path for codebase inside container
RUN mkdir /code
COPY . /code/
WORKDIR /code
# install php + composer
RUN apt-get install -y -qq php7.0-cli php7.0-dev php7.0-curl php7.0-mcrypt php7.0-mysql php7.0-redis
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
 && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
 && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
 && rm -f /tmp/composer-setup.*
# install node + npm + bower + grunt
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get install -y -qq nodejs
RUN npm install -g bower bower-installer grunt-cli
# install sass + bourbon + neat
RUN apt-get install -y -qq sass
RUN gem install -q --pre sass-css-importer
RUN gem install -q bourbon --version 3.2.4
RUN gem install -q neat --version 1.8.0