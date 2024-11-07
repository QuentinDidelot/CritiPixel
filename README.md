<p align="center">
  <img src="assets/images/logo.png" alt="CritiPixel" width="200" />
</p>

# CritiPixel

CritiPixel est une plateforme de critiques de jeux vidéo, offrant des avis détaillés et des fonctionnalités de notation et de filtrage basées sur des tags. 
Ce projet vise à enrichir les expériences de la communauté avec un système de notes, des métadonnées de tags, et des options de filtrage avancées.


## 🛠️ Technologies Utilisées
##### PHP 8.3
##### Symfony
##### Visual Studio Code (VSC)
##### PHPUnit pour les tests
##### PHPStan pour l'analyse statique

## Installation

### Composer
Dans un premier temps, installer les dépendances :
```bash
composer install
```

### Docker (optionnel)
Si vous souhaitez utiliser Docker Compose, il vous suffit de lancer la commande suivante :
```bash
docker compose up -d
```

## Configuration

### Base de données
Actuellement, le fichier `.env` est configuré pour la base de données PostgreSQL mise en place dans `docker-compose.yml`.
Cependant, vous pouvez créer un fichier `.env.local` si nécessaire pour configurer l'accès à la base de données.
Exemple :
```dotenv
DATABASE_URL=mysql://root:Password123!@host:3306/criti-pixel
```

### PHP (optionnel)
Vous pouvez surcharger la configuration PHP en créant un fichier `php.local.ini`.

De même pour la version de PHP que vous pouvez spécifier dans un fichier `.php-version`.

## Usage

### Base de données

#### Supprimer la base de données
```bash
symfony console doctrine:database:drop --force --if-exists
```

#### Créer la base de données
```bash
symfony console doctrine:database:create
```

#### Exécuter les migrations
```bash
symfony console doctrine:migrations:migrate -n
```

#### Charger les fixtures
```bash
symfony console doctrine:fixtures:load -n --purge-with-truncate
```

*Note : Vous pouvez exécuter ces commandes avec l'option `--env=test` pour les exécuter dans l'environnement de test.*

### SASS

#### Compiler les fichiers SASS
```bash
symfony console sass:build
```
*Note : le fichier `.symfony.local.yaml` est configuré pour surveiller les fichiers SASS et les compiler automatiquement quand vous lancez le serveur web de Symfony.*

### ✅ Tests
Exécution des Tests
Avant de lancer les tests, assure-toi que les fixtures sont bien chargées dans l'environnement de test :

Préparer la base de données de test :

```bash
symfony console doctrine:database:drop --force --if-exists --env=test
symfony console doctrine:database:create --env=test
symfony console doctrine:schema:update --force --env=test
symfony console doctrine:fixtures:load --env=test
```

Exécuter les tests avec PHPUnit :

```bash
symfony php bin/phpunit
```

Analyse du code avec PHPStan (vérification des types, etc.) :

```bash
vendor/bin/phpstan analyse src --memory-limit=256M
```

### Serveur web
```bash
symfony serve
```
