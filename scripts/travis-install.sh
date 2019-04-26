#!/usr/bin/env bash

set -e

curl -sSfL -o ~/.phpenv/versions/hhvm/bin/phpunit https://phar.phpunit.de/phpunit-5.7.phar
composer install --no-interaction --prefer-source

if [ $INSTALL_LIBSODIUM = true ]; then
  sudo add-apt-repository ppa:ondrej/php -y
  sudo apt-get update && sudo apt-get install libsodium-dev -y
  if [ $TRAVIS_PHP_VERSION == '5.4' ] || [ $TRAVIS_PHP_VERSION == '5.5' ] || [ $TRAVIS_PHP_VERSION == '5.6' ]; then
    pecl install libsodium-1.0.7
  else
    pecl install libsodium-2.0.11
  fi
fi
