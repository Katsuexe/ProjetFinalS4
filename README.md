# Projet PHP / CodeIgniter 4

Ce dépôt contient un projet PHP basé sur CodeIgniter 4. Il utilise des vues PHP directes et ne dépend pas de Node.js pour l'automatisation ou le build.
## Installation

1. Aller dans le dossier `src` :

   ```bash
   cd src
   ```
2. Installer les dépendances PHP :

   ```bash
   composer install
   ```
3. Copier le fichier d'environnement :

   ```bash
   copy env .env
   ```

   puis adapter les valeurs de base de données et l'URL.
4. Lancer les migrations et, si nécessaire, les seeds :

   ```bash
   php spark migrate
   php spark db:seed DatabaseSeeder
   ```
5. Démarrer le serveur de développement :

   ```bash
   php spark serve
   ```

## Points importants

- Ne jamais committer le fichier `.env`.
- `src/composer.json` est le fichier de dépendances PHP.
- Les vues sont rendues directement en PHP, sans build JavaScript.

## Recommandations Git

- Vérifier que `.gitignore` contient bien :

  - `/vendor/`
  - `/writable/`
  - `.env`
  - `.vscode/`
- Utiliser `.gitattributes` pour normaliser les fins de ligne.
- Utiliser `.editorconfig` pour uniformiser l'indentation.

## Structure utile

- `src/app/` : code applicatif PHP
- `src/public/` : point d'entrée web, CSS, JS statique
- `src/database/` : migrations et seeds
- `src/env` : modèle d'environnement




---