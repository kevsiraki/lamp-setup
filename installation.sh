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
sudo apt install mysql-server #install SQL 
sudo apt -y install php libapache2-mod-php php-mysql #installs PHP
#sudo nano /etc/apache2/mods-enabled/dir.conf #change the index.php path for apache (prefer .php files)
sudo systemctl restart apache2 #restart server to save changes
#set up virtual host
sudo mkdir /var/www/$HOSTNAME #make a directory for your domain
sudo chown -R $USER:$USER /var/www/$HOSTNAME #give ownership to currently logged in user
sudo chmod -R 755 /var/www/$HOSTNAME #check that the above command worked
cat default_index.html | sudo tee /var/www/$HOSTNAME/index.html
./setup_default_conf.sh $HOSTNAME | sudo tee /etc/apache2/sites-available/$HOSTNAME.conf
sudo a2ensite $HOSTNAME.conf #enables the new site file
sudo a2dissite 000-default.conf #disable 000-default.conf default website file
sudo apache2ctl configtest #test the config file for errors
sudo systemctl restart apache2 #restart server to save changes
cat default_info.php | sudo tee /var/www/$HOSTNAME/info.php
#security implementation
# Install Goaccess for analytics
sudo apt install goaccess
# Install certbot via snap
sudo snap refresh
sudo snap install certbot
# Install nix
curl -L https://nixos.org/nix/install | sh
# Install Snort via Nix
nix-env -iA nixpkgs.snort
