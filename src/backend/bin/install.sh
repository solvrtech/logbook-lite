#!/bin/bash

WEBUSER=$(ps axo user,comm | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1)

sudo chgrp -R "$WEBUSER" var public && sudo chmod ug+rwx var

sudo chmod -R a=r,u+w,a+X public && sudo chmod -R ug+rwx public/api

php bin/logbook-setup && sudo chgrp -R "$WEBUSER" var && sudo chmod -R ug+rwx var && php bin/console cache:clear

url="$(grep "APP_URL" .env | cut -f2 -d "=")"
Green='\033[0;32m'

echo -e "${Green}The setup has been completed."
echo -e "${Green}You can visit your website at $url"
