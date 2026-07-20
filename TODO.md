# Revue complète et Tâches restantes — ProjetFinalS4

> **Branche** : `dev`

Ce document liste l'état de la codebase, les bugs restants à corriger et les fonctionnalités à finaliser. Utilisez ce document comme point d'entrée pour savoir où travailler.

---

## 📋 Actions prioritaires

| Priorité | Action | Fichier(s) |
|----------|--------|-----------|
| 🔴 Haute | **Devise** : passer `'EUR'` → `'Ar'` (Ariary) | `Seeds/MainSeeder.php`, `Controllers/User/Wallet.php` |
| 🔴 Haute | Corriger la clé de session `import_report` → `last_import_report` | `Views/admin/import/index.php` |
| 🔴 Haute | Ajouter permission `import.csv` dans le seeder | `Seeds/MainSeeder.php` |
| 🟡 Moyenne | `composer install` + tester Export Excel et PDF en local | `ExcelService`, `PdfService` |
| 🟢 Basse | Câbler `AvatarService` sur les formulaires de profil | `AvatarService`, vues profil |
| 🟢 Basse | Câbler wallet admin (crédit/débit) en Ariary | Nouveau contrôleur + vue |
| 🟢 Basse | Tests PHPUnit pour `CsvImportService` | `tests/` |

---

## 💱 1. Unité monétaire : Ariary (Ar / MGA)

> L'application utilise l'**Ariary malgache** comme devise, pas l'Euro.

### Fichiers à corriger :

| Fichier | Ligne | Valeur actuelle | Valeur attendue |
|---------|-------|----------------|-----------------|
| `Seeds/MainSeeder.php` | ~118 | `'currency' => 'EUR'` | `'currency' => 'Ar'` |
| `Controllers/User/Wallet.php` | ~46 | `$row['currency'] ?? 'EUR'` | `$row['currency'] ?? 'Ar'` |

> **Note** : la colonne `currency` est déjà dans la table `user_balances` — pas besoin de migration.
> Il suffit de mettre à jour le seeder et le fallback du contrôleur, puis de re-seeder.

```bash
# Après correction, re-seeder :
php spark db:seed MainSeeder
```

---

## 🐛 2. Bugs confirmés à corriger

### ❌ Clé de session incohérente — import.php vs ImportController

Dans [`Views/admin/import/index.php`](file:///run/media/katsu/SD/repo/ProjetFinalS4/src/app/Views/admin/import/index.php) ligne 64 :
```php
if ($report = session('import_report'))
```
Dans [`Controllers/Admin/ImportController.php`](file:///run/media/katsu/SD/repo/ProjetFinalS4/src/app/Controllers/Admin/ImportController.php) ligne 92 :
```php
session()->set('last_import_report', $report);
```
**→ La clé `'import_report'` ≠ `'last_import_report'`** : le rapport ne s'affichera jamais.

**Fix** : dans la vue, changer en `session('last_import_report')`.

---

### ❌ Permission `import.csv` absente du seeder

Le lien "Import CSV" dans la sidebar admin est conditionné à `has_permission('import.csv')` mais cette permission **n'est pas dans `MainSeeder.php`**.  
L'admin ne verra donc jamais ce lien sans re-seeder.

**Fix** : ajouter dans `MainSeeder::$permissions` :
```php
['slug' => 'import.csv', 'name' => 'Importer des CSV', 'description' => 'Accès outil import CSV'],
```
Et dans `$pivot` :
```php
['id_type' => $types['admin'], 'id_permission' => $perms['import.csv']],
```

---

## 🏗️ 3. Services en réserve (à câbler)

| Fichier | Statut | Note |
|---------|--------|------|
| `Services/AvatarService.php` | 🚧 | Prêt — store(), delete(), urlFor(). Non câblé aux formulaires de profil |
| `Services/ExcelService.php` | 🚧 | Prêt — nécessite `composer require phpoffice/phpspreadsheet` |
| `Libraries/PdfService.php` | 🚧 | Prêt — nécessite `composer require dompdf/dompdf` |

---

## ✅ État du projet (Ne pas toucher)

Ces parties ont été validées et nettoyées, elles fonctionnent correctement :

- **Profil et Mot de passe** : Workflow nettoyé, changement de MDP sécurisé.
- **Vues Orphelines** : Les vues inutilisées (`request_edit.php`) ont été supprimées.
- **Layouts et CSS** : Responsive mobile (`app.css`) opérationnel, menus burgers ajoutés.
- **Authentification** : `Auth.php` et les filtres associés.
- **Base de données** : Migrations stables, `UserModel`, `UserTypeModel`, `PermissionModel`.
- **Infrastructure** : Fichiers `app/Config/` (Database, Routes, Filters, Security).
