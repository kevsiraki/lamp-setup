#!/usr/bin/env bash
#FULLY AUTOMATED SCRIPT 
#install LAMP (Linux, Apache, MySQL, and PHP) stack.

if [ $# -eq 0 ]
then
    echo "Error: no arguments given"
    echo "Expected $0 <hostname>"
    exit
else
    HOSTNAME=$1
fi

sudo apt -y update #update cache command
sudo apt -y  full-upgrade  #update cache command

sudo apt -y install apache2  #command for installation of apache server

sudo apt -y install ufw #install ufw
sudo ufw limit 22 #allows for port 22 to be open
sudo ufw limit ssh #double check ssh is allowed
sudo ufw allow "Apache Full" #allow access to ports 80 and 443 (Full HTTP/S traffic)

# Make sure that NOBODY can access the server without a password
mysql -e "UPDATE mysql.user SET Password = PASSWORD('CHANGEME') WHERE User = 'root'"
# Kill the anonymous users
mysql -e "DROP USER ''@'localhost'"
# Because our hostname varies we'll use some Bash magic here.
mysql -e "DROP USER ''@'$(hostname)'"
# Kill off the demo database
mysql -e "DROP DATABASE test"
# Make our changes take effect
mysql -e "FLUSH PRIVILEGES"
# Any subsequent tries to run queries this way will get access denied because lack of usr/pwd param

sudo apt -y install php libapache2-mod-php php-mysql #installs PHP
#sudo nano /etc/apache2/mods-enabled/dir.conf #change the index.php path for apache (prefer .php files)
sudo systemctl restart apache2 #restart server to save changes
#set up virtual host
sudo mkdir /var/www/$HOSTNAME #make a directory for your domain
sudo chown -R $USER:$USER /var/www/$HOSTNAME #give ownership to currently logged in user
sudo chmod -R 755 /var/www/$HOSTNAME #check that the above command worked

cat default_index.html | sudo tee /var/www/$HOSTNAME/index.html

./setup_default_conf.sh | sudo tee /etc/apache2/sites-available/$HOSTNAME.conf

sudo a2ensite your_domain.conf #enables the new site file
sudo a2dissite 000-default.conf #disable 000-default.conf default website file
sudo apache2ctl configtest #test the config file for errors
sudo systemctl restart apache2 #restart server to save changes

cat default_info.php | sudo tee /var/www/$HOSTNAME/info.php
