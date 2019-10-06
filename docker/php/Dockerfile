FROM ubuntu:18.04
ENV DEBIAN_FRONTEND=noninteractive

# set timezone
ENV TZ=America/Chicago
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# install basics
RUN apt-get update && apt-get -y upgrade
RUN apt-get install -y curl mysql-client zip unzip build-essential libssl-dev git vim-nox redis-tools software-properties-common

# install php + composer
RUN add-apt-repository -y ppa:ondrej/php
RUN apt-get install -y php7.1-cli php7.1-dev php7.1-curl php7.1-mysql php7.1-mcrypt php-redis
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
 && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
 && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer --snapshot \
 && rm -f /tmp/composer-setup.*

# install node + npm + bower + grunt
RUN curl -sL https://deb.nodesource.com/setup_11.x | bash -
RUN apt-get install -y nodejs
RUN npm install -g bower bower-installer grunt-cli

# install sass + bourbon + neat
RUN apt-get install -y sass
RUN gem install --pre sass-css-importer
RUN gem install bourbon --version 3.2.4
RUN gem install neat --version 1.8.0

# create path for codebase inside container
RUN mkdir /code