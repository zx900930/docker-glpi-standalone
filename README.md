# GLPI Standalone Docker Image (Air-Gapped & Customizable)

[Français](README_FR.md)

[![Docker Pulls](https://img.shields.io/docker/pulls/triatk/glpi-standalone?style=flat-square)](https://hub.docker.com/r/triatk/glpi-standalone)
[![Docker Image Size](https://img.shields.io/docker/image-size/triatk/glpi-standalone/latest?style=flat-square)](https://hub.docker.com/r/triatk/glpi-standalone)
[![Visit on Docker Hub](https://img.shields.io/badge/Docker%20Hub-triatk%2Fglpi--standalone-blue?style=flat-square&logo=docker)](https://hub.docker.com/r/triatk/glpi-standalone)

This project provides a Docker image for GLPI, specifically designed for:

1.  **Air-Gapped Network Deployment:** The official GLPI Docker image attempts to download GLPI sources from within the container, making it unsuitable for networks without internet access. This image bundles GLPI, allowing deployment in isolated environments.
2.  **Host Permission Compatibility:** It resolves common permission issues between the Docker container and the host system by setting the `www-data` user/group within the container to a specific UID/GID (defaulting to `40000`). This ensures that volume mounts for persistent data have correct ownership.
3.  **Subfolder Deployment:** Easily deploy GLPI within a subfolder (e.g., `http://yourdomain.com/glpi`).

This fork is based on previous works and aims to provide a stable and convenient way to run GLPI in Docker.

# Credits

This image builds upon the excellent work of:

- [Original repo by DiouxX](https://github.com/DiouxX/docker-glpi)
- [Air-gapped mod by s0p4L1n3](https://github.com/s0p4L1n3/docker-glpi)
- And contributions from the community.

# Table of Contents

- [Introduction](#introduction)
  - [Default Accounts](#default-accounts)
- [Deploy with CLI](#deploy-with-cli)
  - [Deploy GLPI](#deploy-glpi)
  - [Deploy GLPI with Existing Database](#deploy-glpi-with-existing-database)
  - [Deploy GLPI with Database and Persistence Data](#deploy-glpi-with-database-and-persistence-data)
  - [Deploy a Specific Release of GLPI](#deploy-a-specific-release-of-glpi)
- [Deploy with Docker Compose](#deploy-with-docker-compose)
  - [Quick Test (No Persistence)](#quick-test-no-persistence)
  - [Deploy a Specific Release with Persistence](#deploy-a-specific-release-with-persistence)
    - [mariadb.env example](#mariadbenv-example)
    - [docker-compose.yml example](#docker-composeyml-example)
- [Environment Variables](#environment-variables)
  - [TIMEZONE](#timezone)
  - [GLPI_ALIAS](#glpi_alias)
  - [INSTALL_PLUGINS](#install_plugins)
  - [VERSION_GLPI](#version_glpi)
  - [VERSION_PHP](#version_php)
  - [GLPI_UPGRADE_MIGRATION](#glpi_upgrade_migration)
  - [Other PHP & Opcache Settings](#other-php--opcache-settings)

# Introduction

Install and run a GLPI instance using the `triatk/glpi-standalone` Docker image.

## Default Accounts

After installation, you can log in with the default GLPI accounts. More info in the 📄[Official GLPI Installation Docs](https://glpi-install.readthedocs.io/en/latest/install/wizard.html#end-of-installation).

| Login/Password     | Role              |
| ------------------ | ----------------- |
| glpi/glpi          | Admin account     |
| tech/tech          | Technical account |
| normal/normal      | "Normal" account  |
| post-only/postonly | Post-only account |

# Deploy with CLI

## Deploy GLPI

This example starts GLPI and a new MariaDB database container.

```shell
# Start MariaDB
docker run --name mariadb \
  -e MARIADB_ROOT_PASSWORD=your_strong_root_password \
  -e MARIADB_DATABASE=glpidb \
  -e MARIADB_USER=glpi_user \
  -e MARIADB_PASSWORD=your_strong_glpi_password \
  -d mariadb:10.11 # Or your preferred MariaDB version

# Start GLPI (replace 10.0.18 with the desired/latest stable tag from Docker Hub)
docker run --name glpi \
  --link mariadb:mariadb \
  -p 8080:80 \
  -d triatk/glpi-standalone:null
```

Access GLPI at `http://localhost:8080`.

## Deploy GLPI with Existing Database

If you have an existing MariaDB/MySQL database:

```shell
# Replace 10.0.18 with the desired/latest stable tag
docker run --name glpi \
  --link your_existing_database_container_name:mariadb \
  -p 8080:80 \
  -d triatk/glpi-standalone:null
```

Ensure your GLPI container can connect to `your_existing_database_container_name` on port 3306 and has the necessary credentials.

## Deploy GLPI with Database and Persistence Data

For production or daily usage, use volumes to persist data.

```shell
# Create a Docker volume for MariaDB data (recommended)
docker volume create mariadb_data

# Create Docker volumes for GLPI data (recommended)
docker volume create glpi_data # For GLPI files, marketplace, plugins etc.
docker volume create glpi_config # For GLPI config
docker volume create glpi_logs # For GLPI logs

# Start MariaDB with persistent data
docker run --name mariadb \
  -e MARIADB_ROOT_PASSWORD=your_strong_root_password \
  -e MARIADB_DATABASE=glpidb \
  -e MARIADB_USER=glpi_user \
  -e MARIADB_PASSWORD=your_strong_glpi_password \
  --volume mariadb_data:/var/lib/mysql \
  -d mariadb:10.11

# Start GLPI with persistent data (replace 10.0.18 with desired/latest tag)
# Default UID/GID for www-data is 40000. If your host needs a different one for volume permissions,
# you might need to adjust host folder permissions or chown data within the volume.
docker run --name glpi \
  --link mariadb:mariadb \
  --volume glpi_data:/var/www/html/glpi \
  --volume glpi_config:/var/www/html/glpi/config \
  --volume glpi_logs:/var/www/html/glpi/files/_log \
  -p 8080:80 \
  -d triatk/glpi-standalone:null
```

## Deploy a Specific Release of GLPI

The Docker image tag often corresponds to a GLPI version (e.g., `triatk/glpi-standalone:null`).
You can also use the `VERSION_GLPI` environment variable if the image tag is more generic (like `latest`), though using specific image tags is recommended for production.

```shell
# Example using environment variable (if image tag is generic)
docker run --name glpi \
  --link mariadb:mariadb \
  --volume glpi_data:/var/www/html/glpi \
  -p 8080:80 \
  -e "VERSION_GLPI=10.0.12" \
  -d triatk/glpi-standalone:latest # Or a specific base image tag
```

**Note:** Always check [Docker Hub (`triatk/glpi-standalone`)](https://hub.docker.com/r/triatk/glpi-standalone/tags) for available tags.

# Deploy with Docker Compose

Using `docker-compose` is recommended for managing multi-container applications.

## Quick Test (No Persistence)

This `docker-compose.yml` is for quick testing; data will be lost when containers are removed.

```yaml
version: "3.8"

services:
  mariadb:
    image: mariadb:10.11 # Use a specific, recent, stable version
    container_name: mariadb-glpi
    hostname: mariadb
    environment:
      - MARIADB_ROOT_PASSWORD=mysecretrootpassword
      - MARIADB_DATABASE=glpidb
      - MARIADB_USER=glpi_user
      - MARIADB_PASSWORD=glpiuserpassword
    restart: unless-stopped

  glpi:
    # Check Docker Hub for the latest stable tag: https://hub.docker.com/r/triatk/glpi-standalone/tags
    image: triatk/glpi-standalone:null # Use a specific version tag
    container_name: glpi-app
    hostname: glpi
    depends_on:
      - mariadb
    ports:
      - "8080:80" # Host port 8080 maps to container port 80
    environment:
      - TIMEZONE=Europe/Paris
      # - VERSION_GLPI=null # Often determined by the image tag, but can be set
    restart: unless-stopped
```

## Deploy a Specific Release with Persistence

This is a more complete example for production, using named volumes for data persistence and an `.env` file for database credentials.

**Create a `mariadb.env` file (or use direct environment variables in `docker-compose.yml`):**

### mariadb.env example

```env
MARIADB_ROOT_PASSWORD=your_very_strong_root_password
MARIADB_DATABASE=glpidb
MARIADB_USER=glpi_user
MARIADB_PASSWORD=your_secure_glpi_password
```

### docker-compose.yml example

```yaml
version: "3.8"

services:
  mariadb:
    image: mariadb:10.11 # Use a specific, recent, stable version
    container_name: mariadb-glpi-prod
    hostname: mariadb
    volumes:
      - mariadb_data:/var/lib/mysql
    env_file:
      - ./mariadb.env # Loads variables from mariadb.env
    restart: always

  glpi:
    # Check Docker Hub for the latest stable tag: https://hub.docker.com/r/triatk/glpi-standalone/tags
    image: triatk/glpi-standalone:null # Use a specific version tag
    container_name: glpi-app-prod
    hostname: glpi
    depends_on:
      - mariadb
    ports:
      - "80:80" # Or "8080:80" if port 80 is taken on the host
    volumes:
      # Named volumes for GLPI data persistence
      - glpi_config:/var/www/html/glpi/config
      - glpi_files:/var/www/html/glpi/files # Includes documents, dumps, logs, plugins etc.
      - glpi_marketplace:/var/www/html/glpi/marketplace
      # To use local plugins:
      # - ./my_plugins/:/var/www/html/glpi/plugins/ # Mount your local plugins folder
    environment:
      - TIMEZONE=Europe/Brussels # e.g., Europe/Paris, America/New_York
      # - GLPI_ALIAS=glpi  # Uncomment to deploy GLPI in a subfolder (e.g., /glpi)
      # - VERSION_GLPI=null # Usually set by the image tag, confirm if needed
      # - VERSION_PHP=8.3 # If the image supports multiple PHP versions via env var
      - INSTALL_PLUGINS=false # Set to true if you mount a plugins folder and want them installed
      - OPCACHE_SIZE=128
      - OPCACHE_BUFFER=8
      - OPCACHE_WASTED_PERCENTAGE=5
      - GLPI_UPGRADE_MIGRATION=false # Set to true only during a version upgrade migration
    restart: always

volumes:
  mariadb_data:
  glpi_config:
  glpi_files:
  glpi_marketplace:
```

To deploy, save the files and run in the same directory:

```shell
docker-compose up -d
```

# Environment Variables

The `triatk/glpi-standalone` image supports several environment variables for configuration:

## TIMEZONE

Sets the timezone for PHP and Apache.

- Example: `TIMEZONE=Europe/Paris`
- See [List of Supported Timezones](https://www.php.net/manual/en/timezones.php).

## GLPI_ALIAS

Deploys GLPI in a subfolder (e.g., `http://yourhost/glpi_alias_value`).

- Example: `GLPI_ALIAS=helpdesk`
- If set, GLPI will be accessible at `/helpdesk`.

## INSTALL_PLUGINS

If set to `true`, the container will attempt to install/enable plugins found in the `/var/www/html/glpi/plugins/` directory (which you can mount as a volume).

- Default: `false`
- Example: `INSTALL_PLUGINS=true`

## VERSION_GLPI

Specifies the GLPI version to install/ensure, if the image supports dynamic version fetching (less common with pre-built standalone images). It's often tied to the image tag.

- Example: `VERSION_GLPI=10.0.18`
- **Note:** Prefer using a specific image tag like `triatk/glpi-standalone:null`.

## VERSION_PHP

Allows selecting a specific PHP version if the image is built to support multiple PHP-FPM versions.

- Example: `VERSION_PHP=8.3` (Default often set in image)
- Check image documentation/Dockerfile for supported PHP versions.

## GLPI_UPGRADE_MIGRATION

Set to `true` when you are upgrading GLPI to a new version that requires a database schema migration. The application will attempt to run the migration scripts.

- Default: `false`
- Example: `GLPI_UPGRADE_MIGRATION=true`
- **Important:** Set this back to `false` after the migration is successfully completed.

## Other PHP & Opcache Settings

- `OPCACHE_SIZE`: Opcache memory size in MB (e.g., `128`).
- `OPCACHE_BUFFER`: Opcache interned strings buffer size in MB (e.g., `8`).
- `OPCACHE_WASTED_PERCENTAGE`: Opcache wasted memory percentage to trigger restart (e.g., `5`).
