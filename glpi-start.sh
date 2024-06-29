#!/bin/bash

#Controle du choix de version ou prise de la latest
VERSION_GLPI="${VERSION_GLPI:=10.0.15}"

#Version of PHP
VERSION_PHP="${VERSION_PHP:=8.3}"

#Install plugins
INSTALL_PLUGINS="${INSTALL_PLUGINS:=false}"

if [[ -z "${TIMEZONE}" ]]; then echo "TIMEZONE is unset"; 
else 
echo "date.timezone = \"$TIMEZONE\"" > /etc/php/$VERSION_PHP/apache2/conf.d/timezone.ini;
echo "date.timezone = \"$TIMEZONE\"" > /etc/php/$VERSION_PHP/cli/conf.d/timezone.ini;
fi

#Enable session.cookie_httponly
sed -i 's,session.cookie_httponly = *\(on\|off\|true\|false\|0\|1\)\?,session.cookie_httponly = on,gi' /etc/php/$VERSION_PHP/apache2/php.ini

FOLDER_GLPI=glpi/
FOLDER_WEB=/var/www/html/

#check if TLS_REQCERT is present
if !(grep -q "TLS_REQCERT" /etc/ldap/ldap.conf)
then
	echo "TLS_REQCERT isn't present"
    echo -e "TLS_REQCERT\tnever" >> /etc/ldap/ldap.conf
fi

#Téléchargement et extraction des sources de GLPI
if [ "$(ls ${FOLDER_WEB}${FOLDER_GLPI})" ];
then
	echo "GLPI is already installed"
 	TAR_GLPI=glpi-${VERSION_GLPI}.tgz
  	rm -Rf ${FOLDER_WEB}${TAR_GLPI}
else
	TAR_GLPI=glpi-${VERSION_GLPI}.tgz
	tar -xzf ${FOLDER_WEB}${TAR_GLPI} -C ${FOLDER_WEB}
	rm -Rf ${FOLDER_WEB}${TAR_GLPI}
	chown -R www-data:www-data ${FOLDER_WEB}${FOLDER_GLPI}
fi

#Copy pulgins to the GLPI folder
if [ "$INSTALL_PLUGINS" = true ];
then
  GLPI_PLUGINS=$(ls -1 /plugins)
  echo -e "Install plugins:\n$GLPI_PLUGINS"
  cp -a /plugins/. ${FOLDER_WEB}${FOLDER_GLPI}/plugins/
fi

#Adapt the Apache server according to the version of GLPI installed
## Extract local version installed
LOCAL_GLPI_VERSION=$(ls ${FOLDER_WEB}/${FOLDER_GLPI}/version)
## Extract major version number
LOCAL_GLPI_MAJOR_VERSION=$(echo $LOCAL_GLPI_VERSION | cut -d. -f1)
## Remove dots from version string
LOCAL_GLPI_VERSION_NUM=${LOCAL_GLPI_VERSION//./}

## Target value is GLPI 1.0.7
TARGET_GLPI_VERSION="10.0.7"
TARGET_GLPI_VERSION_NUM=${TARGET_GLPI_VERSION//./}
TARGET_GLPI_MAJOR_VERSION=$(echo $TARGET_GLPI_VERSION | cut -d. -f1)

# Check if need to deply GPLI in a subdir using env GLPI_ALIAS
if [[ -z "$GLPI_ALIAS" ]]; then
   # Deploy in root folder， access url: http://$YourDomain
   # Compare the numeric value of the version number to the target number
   if [[ $LOCAL_GLPI_VERSION_NUM -lt $TARGET_GLPI_VERSION_NUM || $LOCAL_GLPI_MAJOR_VERSION -lt $TARGET_GLPI_MAJOR_VERSION ]]; then
     echo -e "<VirtualHost *:80>\n\tDocumentRoot /var/www/html/glpi\n\n\t<Directory /var/www/html/glpi>\n\t\tAllowOverride All\n\t\tOrder Allow,Deny\n\t\tAllow from all\n\t</Directory>\n\n\tErrorLog /var/log/apache2/error-glpi.log\n\tLogLevel warn\n\tCustomLog /var/log/apache2/access-glpi.log combined\n</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
   else
     set +H
     echo -e "<VirtualHost *:80>\n\tDocumentRoot /var/www/html/glpi/public\n\n\t<Directory /var/www/html/glpi/public>\n\t\tRequire all granted\n\t\tRewriteEngine On\n\t\tRewriteCond %{REQUEST_FILENAME} !-f\n\t\n\t\tRewriteRule ^(.*)$ index.php [QSA,L]\n\t</Directory>\n\n\tErrorLog /var/log/apache2/error-glpi.log\n\tLogLevel warn\n\tCustomLog /var/log/apache2/access-glpi.log combined\n</VirtualHost>" > /etc/apache2/sites-available/000-default.conf
   fi
else
   # Deploy in sub folder， access url： http://$YourDomain/$GLPI_ALIAS
   # Compare the numeric value of the version number to the target number
   if [[ $LOCAL_GLPI_VERSION_NUM -lt $TARGET_GLPI_VERSION_NUM || $LOCAL_GLPI_MAJOR_VERSION -lt $TARGET_GLPI_MAJOR_VERSION ]]; then
     echo -e "\nAlias \"/$GLPI_ALIAS\" \"/var/www/html/glpi\"\n\n<Directory /var/www/html/glpi>\n\tRequire all granted\n\tRewriteEngine On\n\tRewriteCond %{REQUEST_FILENAME} !-f\n\n\tRewriteRule ^(.*)$ index.php [QSA,L]\n</Directory>\n\nErrorLog /var/log/apache2/error-glpi.log\nLogLevel warn\nCustomLog /var/log/apache2/access-glpi.log combined\n" > /etc/apache2/sites-available/000-default.conf
   else
     set +H
     echo -e "\nAlias \"/$GLPI_ALIAS\" \"/var/www/html/glpi/public\"\n\n<Directory /var/www/html/glpi>\n\tRequire all granted\n\tRewriteEngine On\n\tRewriteCond %{REQUEST_FILENAME} !-f\n\n\tRewriteRule ^(.*)$ index.php [QSA,L]\n</Directory>\n\nErrorLog /var/log/apache2/error-glpi.log\nLogLevel warn\nCustomLog /var/log/apache2/access-glpi.log combined\n" > /etc/apache2/sites-available/000-default.conf
   fi
fi

#Add scheduled task by cron and enable
echo "*/2 * * * * www-data /usr/bin/php /var/www/html/glpi/front/cron.php &>/dev/null" > /etc/cron.d/glpi
#Start cron service
service cron start

# output logs to stdout and stderr
ln -sf /dev/stdout /var/log/apache2/access-glpi.log
ln -sf /dev/stderr /var/log/apache2/error-glpi.log

#Activation du module rewrite d'apache
a2enmod rewrite && service apache2 restart && service apache2 stop

#Fix to really stop apache
pkill -9 apache

#Lancement du service apache au premier plan
/usr/sbin/apache2ctl -D FOREGROUND