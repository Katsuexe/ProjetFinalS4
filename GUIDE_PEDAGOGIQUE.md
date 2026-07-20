# Guide pédagogique — Découvrir CodeIgniter 4 avec ce projet

Ce projet est un starter CI4 volontairement **commenté en profondeur** pour
qu'un étudiant de 1re année puisse apprendre en explorant le code lui-même,
sans cours magistral. Ce guide te donne un **ordre de lecture progressif**.

## 0. Comprendre le schéma MVC

CodeIgniter suit le patron **Modèle-Vue-Contrôleur** :

- **Routes** (`app/Config/Routes.php`) : associent une URL à une méthode de contrôleur.
- **Contrôleur** (`app/Controllers/...`) : reçoit la requête, appelle la logique métier, choisit la réponse.
- **Modèle** (`app/Models/...`) : parle à la base de données (Query Builder CI4).
- **Vue** (`app/Views/...`) : génère le HTML envoyé au navigateur.
- **Service / Library** (`app/Services`, `app/Libraries`) : logique métier réutilisable, indépendante du HTTP.

Une requête suit toujours ce chemin :
```
URL → Routes.php → Filtre (auth/admin/guest) → Contrôleur → Modèle/Service → Vue → Réponse HTTP
```

## 1. Ordre de lecture recommandé

1. `app/Config/Routes.php` — toutes les URLs du site, très commenté.
2. `app/Controllers/BaseController.php` — la classe mère de tous les contrôleurs.
3. `app/Controllers/Auth.php` — cycle complet login/register/session.
4. `app/Filters/AuthFilter.php`, `AdminFilter.php`, `GuestFilter.php`, `PermissionFilter.php`
   — comment CI4 protège des routes **avant** d'exécuter le contrôleur.
5. `app/Models/UserModel.php` — Query Builder, relations, hachage de mot de passe.
6. `app/Config/Database.php` — comment CI4 change de moteur de BDD (MySQL/Postgre/SQLite...)
   uniquement via le fichier `.env`, sans toucher au code.
7. `app/Views/layouts/admin.php` + `app/Views/admin/dashboard.php` — héritage de vues
   (`$this->extend()` / `$this->section()`), équivalent d'un template Blade/Twig.
8. **Le module Types & Rôles + CRUD utilisateurs** — voir section 2 ci-dessous,
   c'est le meilleur exemple de RBAC (Role-Based Access Control) fin du projet.
9. **Le module Import/Export (CSV, Excel, PDF)** — voir section 3 ci-dessous,
   c'est l'exemple le plus complet du projet côté traitement de fichiers.
10. **Le workflow de confirmation (`pending_actions`)** — voir section 4
    ci-dessous, c'est l'exemple le plus complet du projet côté logique
    métier "à deux acteurs" (demandeur / validateur).

## 2. Le module Types & Rôles + CRUD utilisateurs

Ce module illustre un RBAC (Role-Based Access Control) complet : des
**types** (rôles) portent des **permissions**, et chaque **utilisateur**
appartient à un seul type. Voir DESIGN.md pour la partie mise en forme.

| Fichier | Rôle |
|---|---|
| `app/Models/UserTypeModel.php` | CRUD `user_types` + `syncPermissions()` (remplace intégralement les permissions d'un type) |
| `app/Models/PermissionModel.php` | CRUD `permissions` |
| `app/Controllers/Admin/Types.php` | `index/create/store/edit/update/delete` — matrice de permissions cochables |
| `app/Views/admin/types/_permissions_fieldset.php` | Partial réutilisé par `create.php` et `edit.php` (principe KISS : un seul endroit à maintenir) |
| `app/Controllers/Admin/Dashboard.php` | `createUser/storeUser/editUser/updateUser` en plus de `users/userDetail/toggleUser/deleteUser` déjà présents |

### 2.1 Deux permissions différentes pour créer et gérer des utilisateurs

- `users.manage` : peut créer **et** modifier/désactiver un compte, choisir
  librement son type.
- `users.create` : permission volontairement **plus étroite**, donnée au
  modérateur (voir `MainSeeder`) — il peut créer un compte, mais celui-ci
  est toujours créé avec le type `user` par défaut, quoi qu'il envoie dans
  le formulaire (voir `Admin\Dashboard::storeUser()` : le champ `id_type`
  posté est **ignoré** si l'appelant n'a pas `users.manage`). C'est un
  exemple concret de "ne jamais faire confiance à une valeur envoyée par
  le client pour une action sensible".
- `PermissionFilter` (au niveau route) ne sait vérifier qu'un **ET** entre
  plusieurs permissions. Comme cette action accepte un **OU**
  (`users.manage` OU `users.create`), la vérification est faite dans le
  contrôleur avec `has_any_permission()` plutôt que dans `Routes.php`.

### 2.2 Pourquoi le modérateur a maintenant `admin.panel`

Le filtre `admin` (voir `AdminFilter.php`) protège **tout** le groupe de
routes `/admin/*` et ne vérifie qu'une seule chose : la présence de
`admin.panel`. Sans cette permission, `users.create` serait une permission
"orpheline" — accordée mais inatteignable. `MainSeeder` donne donc
`admin.panel` au modérateur, en plus de `users.create`, `content.read` et
`content.manage`. Ce choix est documenté directement dans le commentaire du
seeder — à adapter si votre projet a besoin d'une séparation plus stricte
(ex: une zone `/modo/*` dédiée plutôt que de partager `/admin/*`).

### 2.3 Exercice suggéré

Créer une zone `/modo/*` séparée (nouveau groupe de routes + nouveau
`ModeratorFilter`) plutôt que de faire entrer le modérateur dans
`/admin/*` via `admin.panel`. C'est l'approche la plus propre à terme, mais
volontairement pas encore faite ici pour garder ce lot de changements
petit et testable (principe KISS, voir DESIGN.md §0).

## 3. Le module Import / Export : CSV, Excel, PDF

C'est le module le plus riche pédagogiquement car il illustre **tout le cycle**
Contrôleur → Service → Librairie externe → Vue → Réponse HTTP binaire.

| Fichier | Rôle |
|---|---|
| `app/Enums/ErrorTolerance.php` | Enum PHP 8.1+ : 3 modes de gestion d'erreurs (`NONE`, `VERBOSE`, `BLOCK`) |
| `app/Exceptions/NonFatalCsvException.php` | Erreur sur **une seule ligne** du CSV |
| `app/Exceptions/FatalCsvException.php` | Erreur qui **annule tout l'import** |
| `app/Services/CsvImportService.php` | Parsing `fgetcsv()`, validation ligne par ligne, transaction SQL |
| `app/Services/ExcelService.php` | Lecture/écriture de `.xlsx` avec **PhpSpreadsheet** |
| `app/Libraries/PdfService.php` | Génération de PDF avec **Dompdf** à partir d'une vue HTML |
| `app/Controllers/Admin/ImportController.php` | Orchestre les 3 fonctionnalités (upload, export Excel, export PDF) |
| `app/Views/admin/import/index.php` | Formulaires d'upload CSV/Excel + boutons d'export |
| `app/Views/admin/pdf/import_report.php` | Vue HTML **spécifique au PDF** (mise en page simplifiée pour Dompdf) |

### 3.1 CSV — comment ça marche

Un CSV est un simple fichier texte : on peut le lire ligne par ligne avec
la fonction native PHP `fgetcsv()`, sans aucune dépendance externe.
Regarde `CsvImportService::import()` : chaque ligne est validée
(`validateRow()`), et selon le mode `ErrorTolerance` choisi dans le
formulaire, une ligne en erreur est soit ignorée, soit journalisée, soit
elle annule tout l'import (`transRollback()`).

### 3.2 Excel (.xlsx) — pourquoi une bibliothèque est nécessaire

Contrairement au CSV, un fichier `.xlsx` est en réalité **une archive ZIP**
contenant plusieurs fichiers XML internes (feuilles, styles, formules...).
On ne peut donc pas le lire avec `fopen()`. On utilise la bibliothèque
**PhpSpreadsheet**, qui sait décoder ce format et exposer son contenu comme
un simple tableau PHP (`ExcelService::readAsAssoc()`), ou au contraire
générer un `.xlsx` à partir de données PHP (`ExcelService::buildXlsx()`).

Installation (à faire une seule fois avec Composer, en local — le réseau
utilisé pour préparer ce zip pédagogique ne peut pas contacter Packagist) :
```bash
composer require phpoffice/phpspreadsheet
```

### 3.3 PDF — comment fonctionne Dompdf

**Dompdf ne génère pas un PDF depuis du "code"** : il transforme du
**HTML + CSS en PDF**, un peu comme si un navigateur imprimait une page.
C'est pourquoi `PdfService::renderView()` charge une VUE CodeIgniter
normale (`view('admin/pdf/import_report', ...)`) et passe le HTML résultant
à Dompdf. Regarde `app/Views/admin/pdf/import_report.php` : c'est du HTML
classique, mais avec un CSS volontairement simple (pas de Flexbox/Grid),
car Dompdf ne supporte qu'un sous-ensemble de CSS proche de CSS 2.1.

Installation :
```bash
composer require dompdf/dompdf
```

### 3.4 Tester le module en local

```bash
composer install                     # installe TOUTES les dépendances (dont dompdf et phpspreadsheet)
php spark serve                      # démarre le serveur de dev CI4
php spark migrate                    # crée les tables
php spark db:seed MainSeeder         # données de démo
```
Puis connecte-toi en admin et va sur `/admin/import` pour :
- télécharger un modèle CSV (`/admin/import/template`),
- importer ce CSV avec les 3 niveaux de tolérance aux erreurs,
- exporter le rapport d'import en PDF,
- exporter/importer la liste des utilisateurs en `.xlsx`.

## 4. Le workflow de confirmation (`pending_actions`)

Ce module répond à un besoin métier courant : **certaines actions sensibles
ne doivent pas s'appliquer immédiatement**, elles doivent d'abord être
validées par quelqu'un d'autre. Ici, deux cas symétriques :

| Qui demande | Quoi | Qui valide | Où |
|---|---|---|---|
| Modérateur | Modifier le profil d'un **autre** user | Admin | `/admin/validations` |
| User | Modifier **son propre** profil (nom d'utilisateur) | Modérateur (ou admin) | `/admin/validations/users` |

| Fichier | Rôle |
|---|---|
| `app/Database/Migrations/..._CreatePendingActionsTable.php` | Table **générique** (voir son commentaire pour pourquoi une seule table plutôt que deux) |
| `app/Models/PendingActionModel.php` | `createRequest()`, `pendingWithNames()`, `pendingForUser()`, `countPending()` |
| `app/Controllers/Admin/Validations.php` | Les DEUX files de validation (`index`/`approve`/`reject` pour l'admin, `userRequests`/`approveUserRequest`/`rejectUserRequest` pour le modérateur) |
| `app/Controllers/Admin/Dashboard.php` | `requestEditUser()`/`storeRequestEditUser()` — le modo soumet sa demande |
| `app/Controllers/User/Dashboard.php` | `updateProfile()` — **changé** : ne modifie plus directement, crée une demande |
| `app/Views/admin/validations/index.php` et `users.php` | Les deux files, listant `payload_decoded` sous forme de badges |
| `app/Views/admin/users/request_edit.php` | Formulaire du modo (pas de champ `id_type`/mot de passe — moindre privilège) |

### 4.1 Pourquoi une seule table `pending_actions` plutôt que deux

Voir le commentaire complet dans la migration. En résumé : les deux
workflows ont exactement la même forme (quelqu'un demande un changement,
quelqu'un d'autre approuve ou rejette), seul le sens change. Une colonne
`action_type` suffit à les distinguer dans la même table — c'est le pattern
"table de demandes génériques", réutilisable pour n'importe quel futur
workflow d'approbation sans nouvelle migration.

### 4.2 Le payload ne contient QUE ce qui change

`PendingActionModel::createRequest()` reçoit un tableau qui ne contient que
les champs réellement modifiés (voir le `if` de comparaison dans
`storeRequestEditUser()` et `updateProfile()`). Deux raisons :
1. La vue de validation affiche un diff lisible (`username: Bob → Bobby`)
   plutôt qu'un mur de champs identiques à l'existant.
2. Au moment d'appliquer (`Admin\Validations::resolve()`), on ne touche
   QUE ces champs — jamais tout l'enregistrement `users`.

### 4.3 Défense en profondeur à l'approbation

`Admin\Validations::resolve()` fait DEUX vérifications avant d'appliquer un
changement, en plus du filtre de route :
- `action_type` de la demande chargée correspond bien au type attendu par
  la route appelée (empêche qu'un id d'une file soit approuvé via l'autre) ;
- `array_intersect_key()` sur le payload ne garde que `username`/`email`,
  même si la table contenait un jour autre chose — jamais `id_type` ou
  `is_active` ne peuvent être modifiés par ce chemin.

### 4.4 Exercice suggéré

Ajouter un 3e type de demande : un modérateur qui veut **désactiver** un
compte (au lieu de le faire directement) passerait aussi par
`pending_actions` avec `action_type = 'moderator.user_deactivate'` — sans
créer de nouvelle table, juste une nouvelle valeur d'enum + une nouvelle
route + un nouveau bouton.

## 5. Pour aller plus loin (exercices suggérés côté Import/Export)

1. Ajouter une colonne `phone` au CSV et à `UserModel::$allowedFields`, avec
   une validation (regex) dans `validateRow()`.
2. Ajouter une mise en forme conditionnelle dans `ExcelService::buildXlsx()`
   (ex: ligne rouge si `is_active` = 0).
3. Ajouter un logo dans l'en-tête du PDF (`import_report.php`) via une image
   encodée en base64 (Dompdf sait afficher des images `data:` inline).
4. Créer une commande CLI `php spark import:csv chemin.csv` qui réutilise
   directement `CsvImportService` sans passer par HTTP (illustre pourquoi
   la logique métier ne doit jamais vivre dans le contrôleur).
