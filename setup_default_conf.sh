#!/usr/bin/env bash
if [ $# -eq 0 ]
then
    echo "Error: no arguments given"
    echo "Expected $0 <hostname>"
    exit
else
    HOSTNAME=$1
fi
#configuration file for the website
printf "
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName $HOSTNAME
    ServerAlias www.$HOSTNAME
    DocumentRoot /var/www/$HOSTNAME
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
 </VirtualHost>
"
