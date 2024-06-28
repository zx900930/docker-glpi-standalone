# docker-glpi-standalone

This fork is intended only to provide method to deploy glpi on air gap network. The official method does not permit to run the docker on air gap network as it try to download within the container the GLPI source.

It also help to fix the permissions issue between the container and the host by defining new UID/GID to www-data user/group so that it match a defined UID/GID on your host.

I decided to use 40000 as UID/GID as it is not common.

In addition, it add support to deploy GLPI in a subfolder.

# Credits

* [Oringinal repo by DiouxX](https://github.com/DiouxX/docker-glpi)
* [The air-gapped mod by s0p4L1n3](https://github.com/s0p4L1n3/docker-glpi)

# Project to deploy GLPI with docker

[](https://github.com/zx900930/docker-glpi-standalone#project-to-deploy-glpi-with-docker)

[![Docker Pulls](https://camo.githubusercontent.com/42ddb30ff3603b49f4d40bb24eddcb09528ea0eca482f13da8d34ede6c647c80/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f70756c6c732f64696f7578782f676c7069)](https://camo.githubusercontent.com/42ddb30ff3603b49f4d40bb24eddcb09528ea0eca482f13da8d34ede6c647c80/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f70756c6c732f64696f7578782f676c7069) [![Docker Stars](https://camo.githubusercontent.com/8dfef08a98c3a0b6f46d03ad8ca01bb38cf63010c8fa33e292facffe5541f54c/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f73746172732f64696f7578782f676c7069)](https://camo.githubusercontent.com/8dfef08a98c3a0b6f46d03ad8ca01bb38cf63010c8fa33e292facffe5541f54c/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f73746172732f64696f7578782f676c7069) [![](https://camo.githubusercontent.com/2f2b98c937dda982c8ce150e5754e6e0d105ea99ac4cf8983a377c81d561e545/68747470733a2f2f696d616765732e6d6963726f6261646765722e636f6d2f6261646765732f696d6167652f64696f7578782f676c70692e737667)](http://microbadger.com/images/zx900930/glpi-standalone "Get your own image badge on microbadger.com") [![Docker Cloud Automated build](https://camo.githubusercontent.com/626722f18660937d134f54b0b8734072e7402cf977f0981645464f74fb0dafd8/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f636c6f75642f6175746f6d617465642f64696f7578782f676c7069)](https://camo.githubusercontent.com/626722f18660937d134f54b0b8734072e7402cf977f0981645464f74fb0dafd8/68747470733a2f2f696d672e736869656c64732e696f2f646f636b65722f636c6f75642f6175746f6d617465642f64696f7578782f676c7069)

# Table of Contents

[](https://github.com/zx900930/docker-glpi-standalone#table-of-contents)

* [Project to deploy GLPI with docker](https://github.com/zx900930/docker-glpi-standalone#project-to-deploy-glpi-with-docker)
* [Table of Contents](https://github.com/zx900930/docker-glpi-standalone#table-of-contents)
* [Introduction](https://github.com/zx900930/docker-glpi-standalone#introduction)
  * [Default accounts](https://github.com/zx900930/docker-glpi-standalone#default-accounts)
* [Deploy with CLI](https://github.com/zx900930/docker-glpi-standalone#deploy-with-cli)
  * [Deploy GLPI](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi)
  * [Deploy GLPI with existing database](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi-with-existing-database)
  * [Deploy GLPI with database and persistence data](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi-with-database-and-persistence-data)
  * [Deploy a specific release of GLPI](https://github.com/zx900930/docker-glpi-standalone#deploy-a-specific-release-of-glpi)
* [Deploy with docker-compose](https://github.com/zx900930/docker-glpi-standalone#deploy-with-docker-compose)
  * [Deploy without persistence data ( for quickly test )](https://github.com/zx900930/docker-glpi-standalone#deploy-without-persistence-data--for-quickly-test-)
  * [Deploy a specific release](https://github.com/zx900930/docker-glpi-standalone#deploy-a-specific-release)
  * [Deploy with persistence data](https://github.com/zx900930/docker-glpi-standalone#deploy-with-persistence-data)
    * [mariadb.env](https://github.com/zx900930/docker-glpi-standalone#mariadbenv)
    * [docker-compose .yml](https://github.com/zx900930/docker-glpi-standalone#docker-compose-yml)
* [Environnment variables](https://github.com/zx900930/docker-glpi-standalone#environnment-variables)
  * [TIMEZONE](https://github.com/zx900930/docker-glpi-standalone#timezone)

# Introduction

[](https://github.com/zx900930/docker-glpi-standalone#introduction)

Install and run an GLPI instance with docker

## Default accounts

[](https://github.com/zx900930/docker-glpi-standalone#default-accounts)

More info in the 📄[Docs](https://glpi-install.readthedocs.io/en/latest/install/wizard.html#end-of-installation)

| Login/Password     | Role              |
| ------------------ | ----------------- |
| glpi/glpi          | admin account     |
| tech/tech          | technical account |
| normal/normal      | "normal" account  |
| post-only/postonly | post-only account |

# Deploy with CLI

[](https://github.com/zx900930/docker-glpi-standalone#deploy-with-cli)

## Deploy GLPI

[](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi)

```shell
docker run --name mariadb -e MARIADB_ROOT_PASSWORD=diouxx -e MARIADB_DATABASE=glpidb -e MARIADB_USER=glpi_user -e MARIADB_PASSWORD=glpi -d mariadb:10.7
docker run --name glpi --link mariadb:mariadb -p 80:80 -d zx900930/glpi-standalone
```

## Deploy GLPI with existing database

[](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi-with-existing-database)

```shell
docker run --name glpi --link yourdatabase:mariadb -p 80:80 -d zx900930/glpi-standalone
```

## Deploy GLPI with database and persistence data

[](https://github.com/zx900930/docker-glpi-standalone#deploy-glpi-with-database-and-persistence-data)

For an usage on production environnement or daily usage, it's recommanded to use container with volumes to persistent data.

* First, create MariaDB container with volume

```shell
docker run --name mariadb -e MARIADB_ROOT_PASSWORD=diouxx -e MARIADB_DATABASE=glpidb -e MARIADB_USER=glpi_user -e MARIADB_PASSWORD=glpi --volume /var/lib/mysql:/var/lib/mysql -d mariadb:10.7
```

* Then, create GLPI container with volume and link MariaDB container

```shell
docker run --name glpi --link mariadb:mariadb --volume /var/www/html/glpi:/var/www/html/glpi -p 80:80 -d zx900930/glpi-standalone
```

Enjoy :)

## Deploy a specific release of GLPI

[](https://github.com/zx900930/docker-glpi-standalone#deploy-a-specific-release-of-glpi)

Default, docker run will use the latest release of GLPI. For an usage on production environnement, it's recommanded to set specific release. Here an example for release 9.1.6 :

```shell
docker run --name glpi --hostname glpi --link mariadb:mariadb --volume /var/www/html/glpi:/var/www/html/glpi -p 80:80 --env "VERSION_GLPI=9.1.6" -d zx900930/glpi-standalone
```

# Deploy with docker-compose

[](https://github.com/zx900930/docker-glpi-standalone#deploy-with-docker-compose)

## Deploy without persistence data ( for quickly test )

[](https://github.com/zx900930/docker-glpi-standalone#deploy-without-persistence-data--for-quickly-test-)

```yaml
version: "3.8"

services:
#MariaDB Container
  mariadb:
    image: mariadb:10.7
    container_name: mariadb
    hostname: mariadb
    environment:
      - MARIADB_ROOT_PASSWORD=password
      - MARIADB_DATABASE=glpidb
      - MARIADB_USER=glpi_user
      - MARIADB_PASSWORD=glpi

#GLPI Container
  glpi:
    image: zx900930/glpi-standalone
    container_name : glpi
    hostname: glpi
    ports:
      - "80:80"
```

## Deploy a specific release

[](https://github.com/zx900930/docker-glpi-standalone#deploy-a-specific-release)

```yaml
version: "3.8"

services:
#MariaDB Container
  mariadb:
    image: mariadb:10.7
    container_name: mariadb
    hostname: mariadb
    environment:
      - MARIADB_ROOT_PASSWORD=password
      - MARIADB_DATABASE=glpidb
      - MARIADB_USER=glpi_user
      - MARIADB_PASSWORD=glpi

#GLPI Container
  glpi:
    image: zx900930/glpi-standalone
    container_name : glpi
    hostname: glpi
    environment:
      - VERSION_GLPI=9.5.6
    ports:
      - "80:80"
```

## Deploy with persistence data

[](https://github.com/zx900930/docker-glpi-standalone#deploy-with-persistence-data)

To deploy with docker compose, you use *docker-compose.yml* and *mariadb.env* file. You can modify ***mariadb.env*** to personalize settings like :

* MariaDB root password
* GLPI database
* GLPI user database
* GLPI user password

### mariadb.env

[](https://github.com/zx900930/docker-glpi-standalone#mariadbenv)

```
MARIADB_ROOT_PASSWORD=diouxx
MARIADB_DATABASE=glpidb
MARIADB_USER=glpi_user
MARIADB_PASSWORD=glpi
```

### docker-compose .yml

[](https://github.com/zx900930/docker-glpi-standalone#docker-compose-yml)

```yaml
version: "3.2"

services:
  #MariaDB Container
  mariadb:
    image: mariadb:10.7
    container_name: mariadb
    hostname: mariadb
    volumes:
      - /var/lib/mysql:/var/lib/mysql
    env_file:
      - ./mariadb.env
    restart: always

  #GLPI Container
  glpi:
    image: zx900930/glpi-standalone
    container_name: glpi
    hostname: glpi
    ports:
      - "80:80"
    volumes:
      - /etc/timezone:/etc/timezone:ro
      - /etc/localtime:/etc/localtime:ro
      - /var/www/html/glpi/:/var/www/html/glpi
    environment:
      - TIMEZONE=Europe/Brussels
      #- GLPI_ALIAS=glpi  # Optional, uncomment to deploy GLPI in a subfolder
    restart: always
```

To deploy, just run the following command on the same directory as files

```shell
docker-compose up -d
```

# Environnment variables

[](https://github.com/zx900930/docker-glpi-standalone#environnment-variables)

## TIMEZONE

[](https://github.com/zx900930/docker-glpi-standalone#timezone)

If you need to set timezone for Apache and PHP

From commande line

```shell
docker run --name glpi --hostname glpi --link mariadb:mariadb --volumes-from glpi-data -p 80:80 --env "TIMEZONE=Europe/Brussels" -d zx900930/glpi-standalone
```

From docker-compose

Modify this settings

```yaml
environment:
     TIMEZONE=Europe/Brussels
```
