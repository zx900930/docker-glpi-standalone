#On choisit une debian
FROM debian:12.5

LABEL org.opencontainers.image.authors="github@diouxx.be"


#Ne pas poser de question à l'installation
ENV DEBIAN_FRONTEND=noninteractive

#GLPI Verison
ENV VERSION_GLPI="10.0.17"

#Installation d'apache et de php8.3 avec extension
RUN apt update \
&& apt install --yes ca-certificates apt-transport-https lsb-release wget curl \
&& curl -sSLo /usr/share/keyrings/deb.sury.org-php.gpg https://packages.sury.org/php/apt.gpg \ 
&& sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list' \
&& apt update \
&& apt install --yes --no-install-recommends \
apache2 \
php8.3 \
php8.3-mysql \
php8.3-ldap \
php8.3-xmlrpc \
php8.3-imap \
php8.3-curl \
php8.3-gd \
php8.3-mbstring \
php8.3-xml \
php-cas \
php8.3-intl \
php8.3-zip \
php8.3-bz2 \
php8.3-redis \
cron \
jq \
libldap-2.5-0 \
libldap-common \
libsasl2-2 \
libsasl2-modules \
libsasl2-modules-db \
&& rm -rf /var/lib/apt/lists/* \
&& wget -P /var/www/html/ https://github.com/glpi-project/glpi/releases/download/${VERSION_GLPI}/glpi-${VERSION_GLPI}.tgz

#Create custom GID for same group owner ID between Host and Container
RUN groupmod --gid 40000 www-data \
&& usermod --uid 40000 --gid 40000 www-data \
&& chown -R www-data:www-data /var/www/html/

#Copy plugins to the Container
COPY plugins/ /plugins/
RUN chown -R www-data:www-data /plugins/
RUN ls -la /plugins/*

#Copy locales to the Container
COPY locales/ /locales/
RUN chown -R www-data:www-data /locales/
RUN ls -la /locales/*

#Copie et execution du script pour l'installation et l'initialisation de GLPI
COPY glpi-start.sh /opt/
RUN chmod +x /opt/glpi-start.sh
#ENTRYPOINT ["tail", "-f", "/dev/null"]
ENTRYPOINT ["/opt/glpi-start.sh"]

#Exposition des ports
EXPOSE 80 443
