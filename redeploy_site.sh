#!/usr/bin/env bash
if [ $# -eq 0 ]
then
    echo "Error: no arguments given"
    echo "Expected $0 <hostname>"
    exit
else
    HOSTNAME=$1
fi
#each php file.
sudo chown -R $USER:$USER /var/www/$HOSTNAME #give ownership to currently logged in user
sudo chmod -R 755 /var/www/$HOSTNAME #check that the above command work
cp -r Final/. /var/www/$HOSTNAME #place webpage files
sudo mysql -p < db_redeploy.sql #redeploy the database
