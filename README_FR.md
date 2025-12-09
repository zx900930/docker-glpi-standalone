# Image Docker GLPI Autonome (Réseau Isolé & Personnalisable)

[English](README.md)

[![Docker Pulls](https://img.shields.io/docker/pulls/triatk/glpi-standalone?style=flat-square)](https://hub.docker.com/r/triatk/glpi-standalone)
[![Docker Image Size](https://img.shields.io/docker/image-size/triatk/glpi-standalone/latest?style=flat-square)](https://hub.docker.com/r/triatk/glpi-standalone)
[![Visiter sur Docker Hub](https://img.shields.io/badge/Docker%20Hub-triatk%2Fglpi--standalone-blue?style=flat-square&logo=docker)](https://hub.docker.com/r/triatk/glpi-standalone)

Ce projet fournit une image Docker pour GLPI, spécialement conçue pour :

1.  **Déploiement en Réseau Isolé (Air-Gap) :** L'image Docker officielle de GLPI tente de télécharger les sources de GLPI depuis le conteneur, ce qui la rend inadaptée aux réseaux sans accès Internet. Cette image intègre GLPI, permettant un déploiement dans des environnements isolés.
2.  **Compatibilité des Permissions avec l'Hôte :** Elle résout les problèmes courants de permissions entre le conteneur Docker et le système hôte en définissant l'utilisateur/groupe `www-data` à l'intérieur du conteneur avec un UID/GID spécifique (par défaut `40000`). Cela garantit que les volumes montés pour les données persistantes ont les bons droits de propriété.
3.  **Déploiement en Sous-dossier :** Déployez facilement GLPI dans un sous-dossier (par exemple, `http://votredomaine.com/glpi`).

Ce fork est basé sur des travaux antérieurs et vise à fournir un moyen stable et pratique d'exécuter GLPI avec Docker.

# Crédits

Cette image s'appuie sur l'excellent travail de :

- [Dépôt original par DiouxX](https://github.com/DiouxX/docker-glpi)
- [Modification pour réseau isolé par s0p4L1n3](https://github.com/s0p4L1n3/docker-glpi)
- Et les contributions de la communauté.

# Table des Matières

- [Introduction](#introduction)
  - [Comptes par Défaut](#comptes-par-défaut)
- [Déployer avec la CLI](#déployer-avec-la-cli)
  - [Déployer GLPI](#déployer-glpi)
  - [Déployer GLPI avec une Base de Données Existante](#déployer-glpi-avec-une-base-de-données-existante)
  - [Déployer GLPI avec Base de Données et Données Persistantes](#déployer-glpi-avec-base-de-données-et-données-persistantes)
  - [Déployer une Version Spécifique de GLPI](#déployer-une-version-spécifique-de-glpi)
- [Déployer avec Docker Compose](#déployer-avec-docker-compose)
  - [Test Rapide (Sans Persistance)](#test-rapide-sans-persistance)
  - [Déployer une Version Spécifique avec Persistance](#déployer-une-version-spécifique-avec-persistance)
    - [Exemple de mariadb.env](#exemple-de-mariadbenv)
    - [Exemple de docker-compose.yml](#exemple-de-docker-composeyml)
- [Variables d'Environnement](#variables-denvironnement)
  - [TIMEZONE](#timezone)
  - [GLPI_ALIAS](#glpi_alias)
  - [INSTALL_PLUGINS](#install_plugins)
  - [VERSION_GLPI](#version_glpi)
  - [VERSION_PHP](#version_php)
  - [GLPI_UPGRADE_MIGRATION](#glpi_upgrade_migration)
  - [Autres Paramètres PHP & Opcache](#autres-paramètres-php--opcache)

# Introduction

Installez et exécutez une instance GLPI en utilisant l'image Docker `triatk/glpi-standalone`.

## Comptes par Défaut

Après l'installation, vous pouvez vous connecter avec les comptes GLPI par défaut. Plus d'informations dans la 📄[Documentation Officielle d'Installation de GLPI](https://glpi-install.readthedocs.io/fr/latest/install/wizard.html#fin-de-l-installation).

| Identifiant/Mot de passe | Rôle                  |
| ------------------------ | --------------------- |
| glpi/glpi                | Compte administrateur |
| tech/tech                | Compte technicien     |
| normal/normal            | Compte "normal"       |
| post-only/postonly       | Compte post-only      |

# Déployer avec la CLI

## Déployer GLPI

Cet exemple démarre GLPI et un nouveau conteneur de base de données MariaDB.

```shell
# Démarrer MariaDB
docker run --name mariadb \
  -e MARIADB_ROOT_PASSWORD=votre_mot_de_passe_root_solide \
  -e MARIADB_DATABASE=glpidb \
  -e MARIADB_USER=glpi_user \
  -e MARIADB_PASSWORD=votre_mot_de_passe_glpi_solide \
  -d mariadb:10.11 # Ou votre version préférée de MariaDB

# Démarrer GLPI (remplacez 10.0.18 par le tag désiré/dernier stable de Docker Hub)
docker run --name glpi \
  --link mariadb:mariadb \
  -p 8080:80 \
  -d triatk/glpi-standalone:11.0.4
```

Accédez à GLPI sur `http://localhost:8080`.

## Déployer GLPI avec une Base de Données Existante

Si vous avez une base de données MariaDB/MySQL existante :

```shell
# Remplacez 10.0.18 par le tag désiré/dernier stable
docker run --name glpi \
  --link nom_conteneur_bdd_existant:mariadb \
  -p 8080:80 \
  -d triatk/glpi-standalone:11.0.4
```

Assurez-vous que votre conteneur GLPI peut se connecter à `nom_conteneur_bdd_existant` sur le port 3306 et dispose des informations d'identification nécessaires.

## Déployer GLPI avec Base de Données et Données Persistantes

Pour une utilisation en production ou quotidienne, utilisez des volumes pour rendre les données persistantes.

```shell
# Créer un volume Docker pour les données MariaDB (recommandé)
docker volume create mariadb_data

# Créer des volumes Docker pour les données GLPI (recommandé)
docker volume create glpi_data # Pour les fichiers GLPI, marketplace, plugins etc.
docker volume create glpi_config # Pour la configuration GLPI
docker volume create glpi_logs # Pour les logs GLPI

# Démarrer MariaDB avec données persistantes
docker run --name mariadb \
  -e MARIADB_ROOT_PASSWORD=votre_mot_de_passe_root_solide \
  -e MARIADB_DATABASE=glpidb \
  -e MARIADB_USER=glpi_user \
  -e MARIADB_PASSWORD=votre_mot_de_passe_glpi_solide \
  --volume mariadb_data:/var/lib/mysql \
  -d mariadb:10.11

# Démarrer GLPI avec données persistantes (remplacez 10.0.18 par le tag désiré/dernier)
# L'UID/GID par défaut pour www-data est 40000. Si votre hôte nécessite un UID/GID différent
# pour les permissions de volume, vous devrez peut-être ajuster les permissions du dossier hôte
# ou faire un chown des données dans le volume.
docker run --name glpi \
  --link mariadb:mariadb \
  --volume glpi_data:/var/www/html/glpi \
  --volume glpi_config:/var/www/html/glpi/config \
  --volume glpi_logs:/var/www/html/glpi/files/_log \
  -p 8080:80 \
  -d triatk/glpi-standalone:11.0.4
```

## Déployer une Version Spécifique de GLPI

Le tag de l'image Docker correspond souvent à une version de GLPI (ex: `triatk/glpi-standalone:11.0.4`).
Vous pouvez également utiliser la variable d'environnement `VERSION_GLPI` si le tag de l'image est plus générique (comme `latest`), bien que l'utilisation de tags d'image spécifiques soit recommandée en production.

```shell
# Exemple utilisant une variable d'environnement (si le tag de l'image est générique)
docker run --name glpi \
  --link mariadb:mariadb \
  --volume glpi_data:/var/www/html/glpi \
  -p 8080:80 \
  -e "VERSION_GLPI=10.0.12" \
  -d triatk/glpi-standalone:latest # Ou un tag d'image de base spécifique
```

**Note :** Vérifiez toujours [Docker Hub (`triatk/glpi-standalone`)](https://hub.docker.com/r/triatk/glpi-standalone/tags) pour les tags disponibles.

# Déployer avec Docker Compose

L'utilisation de `docker-compose` est recommandée pour gérer les applications multi-conteneurs.

## Test Rapide (Sans Persistance)

Ce `docker-compose.yml` est pour un test rapide ; les données seront perdues lors de la suppression des conteneurs.

```yaml
version: "3.8"

services:
  mariadb:
    image: mariadb:10.11 # Utilisez une version spécifique, récente et stable
    container_name: mariadb-glpi
    hostname: mariadb
    environment:
      - MARIADB_ROOT_PASSWORD=monmotdepasserootsecret
      - MARIADB_DATABASE=glpidb
      - MARIADB_USER=glpi_user
      - MARIADB_PASSWORD=motdepasseutilisateurglpi
    restart: unless-stopped

  glpi:
    # Vérifiez Docker Hub pour le dernier tag stable : https://hub.docker.com/r/triatk/glpi-standalone/tags
    image: triatk/glpi-standalone:11.0.4 # Utilisez un tag de version spécifique
    container_name: glpi-app
    hostname: glpi
    depends_on:
      - mariadb
    ports:
      - "8080:80" # Le port hôte 8080 est mappé au port conteneur 80
    environment:
      - TIMEZONE=Europe/Paris
      # - VERSION_GLPI=11.0.4 # Souvent déterminé par le tag de l'image, mais peut être défini
    restart: unless-stopped
```

## Déployer une Version Spécifique avec Persistance

Ceci est un exemple plus complet pour la production, utilisant des volumes nommés pour la persistance des données et un fichier `.env` pour les informations d'identification de la base de données.

**Créez un fichier `mariadb.env` (ou utilisez des variables d'environnement directes dans `docker-compose.yml`) :**

### Exemple de mariadb.env

```env
MARIADB_ROOT_PASSWORD=votre_mot_de_passe_root_tres_solide
MARIADB_DATABASE=glpidb
MARIADB_USER=glpi_user
MARIADB_PASSWORD=votre_mot_de_passe_glpi_securise
```

### Exemple de docker-compose.yml

```yaml
version: "3.8"

services:
  mariadb:
    image: mariadb:10.11 # Utilisez une version spécifique, récente et stable
    container_name: mariadb-glpi-prod
    hostname: mariadb
    volumes:
      - mariadb_data:/var/lib/mysql
    env_file:
      - ./mariadb.env # Charge les variables depuis mariadb.env
    restart: always

  glpi:
    # Vérifiez Docker Hub pour le dernier tag stable : https://hub.docker.com/r/triatk/glpi-standalone/tags
    image: triatk/glpi-standalone:11.0.4 # Utilisez un tag de version spécifique
    container_name: glpi-app-prod
    hostname: glpi
    depends_on:
      - mariadb
    ports:
      - "80:80" # Ou "8080:80" si le port 80 est pris sur l'hôte
    volumes:
      # Volumes nommés pour la persistance des données GLPI
      - glpi_config:/var/www/html/glpi/config
      - glpi_files:/var/www/html/glpi/files # Inclut documents, sauvegardes, logs, plugins etc.
      - glpi_marketplace:/var/www/html/glpi/marketplace
      # Pour utiliser des plugins locaux :
      # - ./mes_plugins/:/var/www/html/glpi/plugins/ # Montez votre dossier de plugins local
    environment:
      - TIMEZONE=Europe/Brussels # ex: Europe/Paris, America/New_York
      # - GLPI_ALIAS=glpi  # Décommentez pour déployer GLPI dans un sous-dossier (ex: /glpi)
      # - VERSION_GLPI=11.0.4 # Généralement défini par le tag de l'image, confirmez si besoin
      # - VERSION_PHP=8.3 # Si l'image supporte plusieurs versions PHP via var d'env
      - INSTALL_PLUGINS=false # Mettre à true si vous montez un dossier de plugins et voulez les installer
      - OPCACHE_SIZE=128
      - OPCACHE_BUFFER=8
      - OPCACHE_WASTED_PERCENTAGE=5
      - GLPI_UPGRADE_MIGRATION=false # Mettre à true uniquement lors d'une migration de version
    restart: always

volumes:
  mariadb_data:
  glpi_config:
  glpi_files:
  glpi_marketplace:
```

Pour déployer, sauvegardez les fichiers et exécutez dans le même répertoire :

```shell
docker-compose up -d
```

# Variables d'Environnement

L'image `triatk/glpi-standalone` prend en charge plusieurs variables d'environnement pour la configuration :

## TIMEZONE

Définit le fuseau horaire pour PHP et Apache.

- Exemple : `TIMEZONE=Europe/Paris`
- Voir la [Liste des Fuseaux Horaires Supportés](https://www.php.net/manual/fr/timezones.php).

## GLPI_ALIAS

Déploie GLPI dans un sous-dossier (par exemple, `http://votrehote/valeur_glpi_alias`).

- Exemple : `GLPI_ALIAS=helpdesk`
- Si défini, GLPI sera accessible à `/helpdesk`.

## INSTALL_PLUGINS

Si défini à `true`, le conteneur tentera d'installer/activer les plugins trouvés dans le répertoire `/var/www/html/glpi/plugins/` (que vous pouvez monter en tant que volume).

- Défaut : `false`
- Exemple : `INSTALL_PLUGINS=true`

## VERSION_GLPI

Spécifie la version de GLPI à installer/assurer, si l'image prend en charge la récupération dynamique de version (moins courant avec les images autonomes pré-construites). C'est souvent lié au tag de l'image.

- Exemple : `VERSION_GLPI=10.0.18`
- **Note :** Préférez utiliser un tag d'image spécifique comme `triatk/glpi-standalone:11.0.4`.

## VERSION_PHP

Permet de sélectionner une version PHP spécifique si l'image est conçue pour prendre en charge plusieurs versions de PHP-FPM.

- Exemple : `VERSION_PHP=8.3` (La valeur par défaut est souvent définie dans l'image)
- Vérifiez la documentation de l'image/Dockerfile pour les versions PHP supportées.

## GLPI_UPGRADE_MIGRATION

Mettre à `true` lorsque vous mettez à niveau GLPI vers une nouvelle version qui nécessite une migration du schéma de la base de données. L'application tentera d'exécuter les scripts de migration.

- Défaut : `false`
- Exemple : `GLPI_UPGRADE_MIGRATION=true`
- **Important :** Remettez cette variable à `false` une fois la migration terminée avec succès.

## Autres Paramètres PHP & Opcache

- `OPCACHE_SIZE` : Taille de la mémoire Opcache en Mo (ex : `128`).
- `OPCACHE_BUFFER` : Taille du tampon des chaînes internalisées Opcache en Mo (ex : `8`).
- `OPCACHE_WASTED_PERCENTAGE` : Pourcentage de mémoire gaspillée Opcache pour déclencher un redémarrage (ex : `5`).
