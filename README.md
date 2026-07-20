# ProjetFinalS4 — CodeIgniter 4 Starter

Ce dépôt contient le projet basé sur CodeIgniter 4. L'application intègre un système d'authentification, une gestion des rôles (Admin, User, Moderator), et une base de données avec des migrations/seeders prêts à l'emploi.

## 🚀 Installation & Setup

1. **Cloner et préparer le dossier `src`** :
   Le code applicatif se trouve dans le sous-dossier `src/`.

   ```bash
   cd src
   ```
2. **Installer les dépendances PHP** :
   (!!! important: il faut veriifer votre php.ini dans votre configuration pour qu'il suive les dépendances demandées avant de lancer `composer install`)

   ```bash
   composer install
   ```
3. **Configurer l'environnement** :
   Copier le fichier template vers `.env` :

   ```bash
   cp env .env
   ```

   **Important** : Ouvrez `.env` et configurez vos accès à la base de données (`database.default.hostname`, `database.default.database`, `database.default.username`, `database.default.password`) ainsi que l'URL (`app.baseURL = 'http://localhost:8080'`).
4. **Migrations et Seeders** :
   Générez les tables et insérez les données par défaut (incluant les permissions, types d'utilisateurs et comptes de test).

   ```bash
   php spark migrate
   php spark db:seed MainSeeder
   ```
5. **Démarrer le serveur local** :

   ```bash
   php spark serve
   ```

   L'application sera accessible sur `http://localhost:8080`.

---

## 🔐 Comptes de test (après Seed)

Le seeder `MainSeeder` crée 3 comptes de test, tous avec le mot de passe **`password123`** :

- **Admin** : `admin@example.com`
- **Modérateur** : `mod@example.com`
- **Utilisateur** : `user@example.com`

---

## 📦 Dépendances optionnelles (Import / Export)

Certaines fonctionnalités d'export nécessitent des bibliothèques externes. Si vous devez travailler sur l'export Excel ou PDF, exécutez ces commandes depuis le dossier `src` :

```bash
# Pour l'export Excel (ExcelService)
composer require phpoffice/phpspreadsheet

# Pour l'export PDF (PdfService)
composer require dompdf/dompdf
```

---

## 🛠️ Organisation du travail

- **Fichier TODO** : Consultez le fichier [`TODO.md`](../TODO.md) à la racine pour voir l'état d'avancement, les tâches prioritaires et les bugs connus.
- Ne commitez **jamais** le fichier `.env` ou le dossier `src/writable/`.
- Les vues utilisent du HTML/CSS pur. Le fichier CSS principal est dans `src/public/assets/css/app.css`.
