# ProjetFinalS4 — Application CodeIgniter 4

[![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue?logo=php&logoColor=white)](https://www.php.net/)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.3-orange?logo=codeigniter&logoColor=white)](https://codeigniter.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Dompdf](https://img.shields.io/badge/dompdf-v3.1-blue)](https://github.com/dompdf/dompdf)
[![PhpSpreadsheet](https://img.shields.io/badge/PhpSpreadsheet-v3.5-blue)](https://github.com/PHPOffice/PhpSpreadsheet)

## Description

Application web développée avec CodeIgniter 4, proposant :
- authentification utilisateur
- gestion de rôles (Admin, Moderator, User)
- migration et seeders prêts à l'emploi
- export PDF et Excel
- organisation MVC propre et extensible

## Stack technique

- PHP 8.2+
- CodeIgniter 4
- dompdf
- phpoffice/phpspreadsheet
- PHPUnit
- Composer

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Extensions PHP : `intl`, `mbstring`, `dom`, `fileinfo`, `pdo`, `pdo_sqlite` ou autre pilote DB selon votre configuration

## Installation

1. Cloner le dépôt :

```bash
git clone <votre-repo-url> ProjetFinalS4
cd ProjetFinalS4/src
```

2. Installer les dépendances :

```bash
composer install
```

3. Copier et configurer l'environnement :

```bash
cp env .env
```

Puis éditez `.env` et mettez à jour les paramètres suivants :

- `database.default.hostname`
- `database.default.database`
- `database.default.username`
- `database.default.password`
- `app.baseURL`

4. Générer la base de données et insérer les données initiales :

```bash
php spark migrate
php spark db:seed MainSeeder
```

5. Lancer le serveur local :

```bash
php spark serve
```

L'application est disponible sur `http://localhost:8080`.

## Comptes de test

Après exécution du seeder, utilisez :

- **Admin** : `admin@example.com`
- **Modérateur** : `mod@example.com`
- **Utilisateur** : `user@example.com`

Mot de passe commun : `password123`

## Export PDF / Excel

Les fonctionnalités d'export sont déjà listées dans `composer.json` :
- `dompdf/dompdf` pour le PDF
- `phpoffice/phpspreadsheet` pour Excel

Si nécessaire, ajoutez ou mettez à jour ces packages avec :

```bash
composer require dompdf/dompdf
composer require phpoffice/phpspreadsheet
```

## Tests

Le projet inclut des dépendances PHPUnit et des utilitaires de tests.

Exécution des tests :

```bash
cd src
composer test
```

## Structure du projet

- `src/app/` : application principale
- `src/app/Config/` : configuration de CodeIgniter
- `src/app/Controllers/` : contrôleurs
- `src/app/Models/` : modèles de données
- `src/app/Views/` : vues front-end
- `src/public/` : point d'entrée public et ressources statiques
- `src/system/` : framework CodeIgniter
- `src/writable/` : logs, cache et fichiers temporaires

## Bonnes pratiques

- Ne commentez pas `.env`
- Ne versionnez pas `src/writable/`
- Vérifiez les permissions de la base de données avant de lancer les migrations
- Respectez les conventions de CodeIgniter pour les contrôleurs, modèles et vues

## Licence

Ce projet est distribué sous licence **MIT**.
