# TODO — Revue complète du projet ProjetFinalS4

> **Branche** : `dev`
> Ce document liste tout ce qui manque ou doit être corrigé pour livrer la Version 1 (v1) du sujet. Il ne décrit pas ce qui a été fait (voir `Taches.md` pour ça) — uniquement ce qui reste.

---

## 🔴 1. Fonctionnalités métier — non démarrées

Le sujet demande un système de simulation d'opérateur mobile money. Actuellement, le code livré est une base technique générique (auth, permissions, import CSV, solde en lecture seule) : **aucune des fonctionnalités métier de la v1 n'est implémentée.**

### Côté opérateur
- [ ] Configuration des préfixes opérateur (ex : 033, 037) — pas de table, pas de CRUD, pas de vue
- [ ] Types d'opérations (dépôt, retrait, transfert) avec barème de frais par tranche de montant, modifiable — aucune table `operation_types` / `fee_tiers`
- [ ] Vue "situation des gains" (via les différents frais : retrait et transfert)
- [ ] Vue "situation des comptes clients"

### Côté client
- [ ] Login automatique avec le numéro de téléphone (pas d'inscription préalable) — le système actuel n'a que login/mot de passe classique (`Auth.php`), incompatible avec ce flux
- [x] Voir le solde — existe, mais en lecture seule (`Controllers/User/Wallet.php::index()`)
- [ ] Faire un dépôt (supposé automatique)
- [ ] Faire un retrait (supposé automatique)
- [ ] Faire un transfert
- [ ] Voir les historiques d'opérations

### Base de données
- [ ] `base.sql` à la racine du projet (scripts de création tables/vues/données) — **absent, obligatoire pour la livraison**
- [ ] Migration `operators` (préfixes)
- [ ] Migration `operation_types` (dépôt/retrait/transfert)
- [ ] Migration `fee_tiers` (barèmes par tranche de montant)
- [ ] Migration `transactions` (historique des opérations, lien client/opérateur/type/montant/frais)
- [ ] Seeder pour peupler préfixes + barèmes de frais par défaut (voir barème donné dans le sujet)

### Livraison
- [ ] Tag Git `v1` sur le dépôt public (Github/Gitlab) — dernier commit non taggé actuellement
- [ ] Vérifier que le dépôt est bien public
- [ ] Renseigner les informations de début de projet dans le formulaire fourni (`https://forms.gle/nCv6xJYHVvVJj2FKA`)

---

## 🟠 2. Bugs confirmés dans le code existant

### Devise EUR → Ar (Ariary)
Le sujet utilise l'Ariary malgache, le code utilise encore `'EUR'` par défaut.
| Fichier | Ligne | Valeur actuelle | Valeur attendue |
|---|---|---|---|
| `Seeds/MainSeeder.php` | ~118 | `'currency' => 'EUR'` | `'currency' => 'Ar'` |
| `Controllers/User/Wallet.php` | ~46 | `$row['currency'] ?? 'EUR'` | `$row['currency'] ?? 'Ar'` |

La colonne `currency` existe déjà dans `user_balances`, pas de migration nécessaire — corriger le seeder et le fallback puis re-seeder :
```bash
php spark db:seed MainSeeder
```

### Clé de session incohérente — import CSV
`Views/admin/import/index.php` ligne 64 :
```php
if ($report = session('import_report'))
```
`Controllers/Admin/ImportController.php` ligne 92 :
```php
session()->set('last_import_report', $report);
```
Les deux clés diffèrent (`import_report` vs `last_import_report`) → le rapport d'import ne s'affiche jamais.
**Fix** : dans la vue, utiliser `session('last_import_report')`.

### Permission `import.csv` absente du seeder
Le lien "Import CSV" de la sidebar admin est conditionné à `has_permission('import.csv')`, mais cette permission n'existe pas dans `MainSeeder::$permissions`. L'admin ne voit donc jamais ce lien.
**Fix** — ajouter dans `$permissions` :
```php
['slug' => 'import.csv', 'name' => 'Importer des CSV', 'description' => 'Accès outil import CSV'],
```
et dans `$pivot` :
```php
['id_type' => $types['admin'], 'id_permission' => $perms['import.csv']],
```

---

## 🟡 3. Services prêts mais non câblés / non testés

| Fichier | Statut | Action requise |
|---|---|---|
| `Services/ExcelService.php` | 🚧 Prêt | `composer require phpoffice/phpspreadsheet` puis tester l'export en local |
| `Libraries/PdfService.php` | 🚧 Prêt | `composer require dompdf/dompdf` puis tester l'export en local |
| `Services/AvatarService.php` | 🚧 Prêt (store/delete/urlFor) | Non branché aux formulaires de profil — à intégrer |
| Wallet admin (crédit/débit) | ❌ Non fait | Aucun contrôleur/vue permettant à l'admin de créditer/débiter un compte en Ariary — nécessaire avant de simuler dépôts/retraits |
| `CsvImportService` | ❌ Non testé | Aucun test PHPUnit dans `tests/` |

---

## 🟢 4. Points à vérifier / non urgents

- [ ] `composer install` complet + test de bout en bout en local (Excel + PDF inclus)
- [ ] Vérifier la responsivité mobile sur les futures vues opérateur/client (dépôt, retrait, transfert, historique)
- [ ] Revalider les permissions (`wallet.manage`, futur `operations.manage`, etc.) une fois les nouvelles fonctionnalités ajoutées

---

## ✅ Ce qui est stable et validé (ne pas toucher)

- **Authentification** générique : `Auth.php` et filtres associés (à noter : incompatible avec le login par téléphone demandé pour les clients — à traiter séparément, pas à modifier ici)
- **Base de données infrastructure** : migrations `user_types`, `permissions`, `users`, `user_type_permissions`, `user_balances` ; modèles associés
- **Profil et mot de passe** : workflow nettoyé, changement de mot de passe sécurisé
- **Layouts et CSS** : responsive mobile (`app.css`), menus burger
- **Vues orphelines** : supprimées (`request_edit.php`)
- **Config** : `app/Config/` (Database, Routes, Filters, Security)