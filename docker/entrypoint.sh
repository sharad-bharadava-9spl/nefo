#!/bin/sh

# fix key if needed
#if [ -z "$APP_KEY" ]
#then
#  echo "Please re-run this container with an environment variable \$APP_KEY"
#  echo "An example APP_KEY you could use is: "
#  php artisan key:generate --show
#  exit
#fi

#if [ -f /var/lib/docker/volumes/wira/ssl/wira-ssl.crt -a -f /var/lib/docker/volumes/wira/ssl/wira-ssl.key ]
#then
#  a2enmod ssl
#else
#  a2dismod ssl
#fi

# create data directories
for dir in 'data/uploads' 'data/uploads/country' 'data/uploads/id-proofs' 'data/uploads/request' 'data/uploads/users' 'dumps'; do
	mkdir -p "/var/lib/docker/volumes/wira/$dir"
done

chown -R docker:root /var/lib/docker/volumes/wira/data/*
chown -R docker:root /var/lib/docker/volumes/wira/dumps
chmod 777 bootstrap/cache
mkdir storage/logs/
mkdir -p storage/framework/sessions
mkdir storage/framework/views
mkdir storage/framework/cache
chmod -R 777 storage
chown -R www-data:www-data storage/logs/
chmod 777 storage/logs/
chmod g+s storage/logs/

. /etc/apache2/envvars
exec apache2 -DNO_DETACH < /dev/null
