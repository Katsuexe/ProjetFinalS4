# TODO — Import CSV généralisé (CsvImportService)

---

## Arbre des fichiers à créer / modifier

```
src/app/
│
├── Enums/                              [créer le dossier]
│   └── ErrorTolerance.php              [nouveau]
│
├── Exceptions/                         [créer le dossier]
│   ├── FatalCsvException.php           [nouveau]
│   └── NonFatalCsvException.php        [nouveau]
│
├── Services/                           [créer le dossier]
│   └── CsvImportService.php            [nouveau]
│
├── Controllers/
│   └── Admin/
│       └── ImportController.php        [nouveau]
│
├── Views/
│   └── admin/
│       └── import/
│           └── index.php               [nouveau]
│
├── Config/
│   └── Routes.php                      [modifier — ajouter 2 lignes]
│
└── Database/
    └── Seeds/
        └── MainSeeder.php              [modifier — ajouter permission + pivot]
```

---

## 1. `app/Enums/ErrorTolerance.php` — nouveau fichier complet

```php
<?php

namespace App\Enums;

enum ErrorTolerance
{
    case NONE;
    case VERBOSE;
    case BLOCK;
}
```

---

## 2. `app/Exceptions/FatalCsvException.php` — nouveau fichier complet

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class FatalCsvException extends RuntimeException
{
    public function __construct(
        public readonly string $case,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

---

## 3. `app/Exceptions/NonFatalCsvException.php` — nouveau fichier complet

```php
<?php

namespace App\Exceptions;

use RuntimeException;

class NonFatalCsvException extends RuntimeException
{
    public function __construct(
        public readonly string $case,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
```

---

## 4. `app/Services/CsvImportService.php` — nouveau fichier complet

```php
<?php

namespace App\Services;

use App\Enums\ErrorTolerance;
use App\Exceptions\FatalCsvException;
use App\Exceptions\NonFatalCsvException;
use CodeIgniter\Model;

class CsvImportService
{
    // Colonnes CI4 à exclure des colonnes "required"
    private const EXCLUDED_COLUMNS = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function __construct(
        private ErrorTolerance $tolerance  = ErrorTolerance::VERBOSE,
        private mixed          $detector   = null,
        private mixed          $normalizer = null,
    ) {
        // Détecteur par défaut : sniff le séparateur sur la 1ère ligne
        $this->detector ??= static function (string $tmpPath): string {
            $line = fgets(fopen($tmpPath, 'r'));
            $scores = [];
            foreach ([';', ',', '|', "\t"] as $sep) {
                $scores[$sep] = substr_count($line, $sep);
            }
            arsort($scores);
            return array_key_first($scores) ?? ';';
        };

        // Normaliseur par défaut : trim + strtolower + strip BOM UTF-8
        $this->normalizer ??= static function (string $header): string {
            $header = ltrim($header, "\xEF\xBB\xBF"); // strip BOM
            return strtolower(trim($header));
        };
    }

    // ── Moteur de tolérance ──────────────────────────────────────────

    private function handle(string $case, array $cases, string $message): void
    {
        $level = $cases[$case] ?? $this->tolerance;

        match ($level) {
            ErrorTolerance::NONE    => null,
            ErrorTolerance::VERBOSE => throw new NonFatalCsvException($case, $message),
            ErrorTolerance::BLOCK   => throw new FatalCsvException($case, $message),
        };
    }

    // ── parse() ──────────────────────────────────────────────────────

    public function parse(string $tmpPath, ?string $sep = null): array
    {
        $cases = [
            'file_unreadable'  => ErrorTolerance::BLOCK,
            'invalid_encoding' => ErrorTolerance::VERBOSE,
            'malformed_row'    => ErrorTolerance::NONE,
        ];

        // Détection séparateur
        $sep ??= ($this->detector)($tmpPath);

        // Ouverture
        $handle = @fopen($tmpPath, 'r');
        if ($handle === false) {
            $this->handle('file_unreadable', $cases, "Impossible d'ouvrir : $tmpPath");
            return ['headers' => [], 'rows' => [], 'separator' => $sep, 'total' => 0];
        }

        // Headers (ligne 0)
        $rawHeaders = fgetcsv($handle, 0, $sep) ?: [];
        $headers    = array_map($this->normalizer, $rawHeaders);

        // Boucle lignes
        $rows = [];
        $i    = 1;
        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            $i++;

            if (count($row) !== count($headers)) {
                $this->handle('malformed_row', $cases, "Ligne $i : colonnes attendues " . count($headers) . ', reçues ' . count($row));
                continue;
            }

            if (! mb_check_encoding(implode('', $row), 'UTF-8')) {
                try {
                    $this->handle('invalid_encoding', $cases, "Ligne $i : encodage non UTF-8");
                } catch (NonFatalCsvException) {
                    // loggé dans le rapport par l'appelant si besoin
                }
                continue;
            }

            $rows[] = array_combine($headers, $row);
        }

        fclose($handle);

        return [
            'headers'   => $headers,
            'rows'      => $rows,
            'separator' => $sep,
            'total'     => count($rows),
        ];
    }

    // ── align() ──────────────────────────────────────────────────────

    public function align(array $headers, Model $model): array
    {
        $cases = [
            'extra_column'     => ErrorTolerance::NONE,
            'missing_nullable' => ErrorTolerance::VERBOSE,
            'required_missing' => ErrorTolerance::BLOCK,
        ];

        $db         = \Config\Database::connect();
        $table      = $model->getTable();
        $dbColumns  = $db->getFieldNames($table);
        $fieldData  = $db->getFieldData($table);   // stdClass[] avec name, nullable, default, primary_key, auto_increment

        // Indexer fieldData par nom de colonne pour accès rapide
        $fieldMeta = [];
        foreach ($fieldData as $field) {
            $fieldMeta[$field->name] = $field;
        }

        // Exclure colonnes système des colonnes requises
        $checkableColumns = array_diff($dbColumns, self::EXCLUDED_COLUMNS);

        $matched          = array_values(array_intersect($headers, $dbColumns));
        $extra            = array_values(array_diff($headers, $dbColumns));
        $missingNullable  = [];
        $requiredMissing  = [];

        // Colonnes extra : non bloquant
        foreach ($extra as $col) {
            $this->handle('extra_column', $cases, "Colonne '$col' présente dans le CSV mais absente de la table '$table'");
        }

        // Colonnes manquantes : classer selon metadata
        $missing = array_diff($checkableColumns, $headers);
        foreach ($missing as $col) {
            $meta = $fieldMeta[$col] ?? null;
            $isNullableOrHasDefault = $meta && ($meta->nullable || $meta->default !== null);
            $isAutoIncrement        = $meta && ! empty($meta->auto_increment);

            if ($isAutoIncrement || $isNullableOrHasDefault) {
                $missingNullable[] = $col;
                try {
                    $this->handle('missing_nullable', $cases, "Colonne '$col' absente du CSV (nullable/défaut disponible)");
                } catch (NonFatalCsvException) {
                    // non bloquant, accumulé dans le rapport
                }
            } else {
                $requiredMissing[] = $col;
                $this->handle('required_missing', $cases, "Colonne '$col' requise (NOT NULL sans défaut) absente du CSV");
            }
        }

        return [
            'matched'          => $matched,
            'extra'            => $extra,
            'missing_nullable' => $missingNullable,
            'required_missing' => $requiredMissing,
        ];
    }

    // ── importInto() ─────────────────────────────────────────────────

    public function importInto(Model $model, array $rows, array $options = []): array
    {
        $cases = [
            'soft_duplicate'  => ErrorTolerance::NONE,
            'validation_fail' => ErrorTolerance::VERBOSE,
            'db_fatal'        => ErrorTolerance::BLOCK,
        ];

        $beforeRow = $options['beforeRow'] ?? null;
        $afterRow  = $options['afterRow']  ?? null;
        $strict    = $options['strict']    ?? false;

        $db       = \Config\Database::connect();
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];

        $db->transStart();

        foreach ($rows as $i => $row) {
            $lineNum = $i + 2; // +2 car ligne 1 = headers
            $ok      = false;

            // Hook avant insertion
            if ($beforeRow !== null) {
                $row = $beforeRow($row);
            }

            // Validation via les règles du model
            if (! $model->validate($row)) {
                try {
                    $this->handle('validation_fail', $cases, "Ligne $lineNum : " . implode(', ', $model->errors()));
                } catch (NonFatalCsvException $e) {
                    $errors[] = ['line' => $lineNum, 'case' => $e->case, 'message' => $e->getMessage()];
                    $skipped++;
                    if ($afterRow !== null) { $afterRow($row, false); }
                    continue;
                }
            }

            // Insertion
            try {
                $model->insert($row, false);
                $inserted++;
                $ok = true;
            } catch (\Exception $e) {
                $isDuplicate = str_contains($e->getMessage(), '1062');

                if ($isDuplicate) {
                    try {
                        $this->handle('soft_duplicate', $cases, "Ligne $lineNum : doublon ignoré");
                    } catch (NonFatalCsvException $ex) {
                        $errors[] = ['line' => $lineNum, 'case' => $ex->case, 'message' => $ex->getMessage()];
                    }
                    $skipped++;
                } else {
                    try {
                        $this->handle('db_fatal', $cases, "Ligne $lineNum : erreur DB — " . $e->getMessage());
                    } catch (FatalCsvException $ex) {
                        if ($strict) {
                            $db->transRollback();
                        }
                        throw $ex; // remonte au controller
                    }
                    $skipped++;
                }
            }

            // Hook après insertion
            if ($afterRow !== null) {
                $afterRow($row, $ok);
            }
        }

        $db->transComplete();

        return [
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'total'    => count($rows),
        ];
    }
}
```

---

## 5. `app/Controllers/Admin/ImportController.php` — nouveau fichier complet

```php
<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Exceptions\FatalCsvException;
use App\Models\UserModel;
use App\Services\CsvImportService;

class ImportController extends BaseController
{
    // Table importable → classe Model correspondante
    // Ajouter ici chaque nouvelle table à rendre importable
    private const ALLOWED_TABLES = [
        'users' => UserModel::class,
        // 'parcelles' => ParcelleModel::class,
    ];

    public function index(): string
    {
        return view('admin/import/index', [
            'title'         => 'Import CSV',
            'pageTitle'     => 'Import CSV',
            'allowedTables' => array_keys(self::ALLOWED_TABLES),
        ]);
    }

    public function upload()
    {
        // ── 1. Validation du fichier ────────────────────────────────
        if (! $this->validate([
            'csv_file' => 'uploaded[csv_file]|max_size[csv_file,2048]|ext_in[csv_file,csv,txt]',
            'table'    => 'required',
        ])) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => $this->validator->getErrors(),
            ]);
        }

        // ── 2. Résolution du model cible ────────────────────────────
        $tableName = $this->request->getPost('table');

        if (! isset(self::ALLOWED_TABLES[$tableName])) {
            return $this->response->setStatusCode(422)->setJSON([
                'error' => "Table '$tableName' non autorisée pour l'import.",
            ]);
        }

        $modelClass = self::ALLOWED_TABLES[$tableName];
        $model      = new $modelClass();

        // ── 3. Séparateur (optionnel depuis le formulaire) ──────────
        $sep  = $this->request->getPost('separator') ?: null;
        $sep  = ($sep === 'auto') ? null : $sep;

        $file = $this->request->getFile('csv_file');
        $service = new CsvImportService();

        // ── 4. parse() ──────────────────────────────────────────────
        try {
            $parsed = $service->parse($file->getTempName(), $sep);
        } catch (FatalCsvException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'step'    => 'parse',
                'case'    => $e->case,
                'error'   => $e->getMessage(),
            ]);
        }

        // ── 5. align() ──────────────────────────────────────────────
        try {
            $alignment = $service->align($parsed['headers'], $model);
        } catch (FatalCsvException $e) {
            return $this->response->setStatusCode(422)->setJSON([
                'step'      => 'align',
                'case'      => $e->case,
                'error'     => $e->getMessage(),
                'alignment' => $alignment ?? [],
            ]);
        }

        // ── 6. importInto() ─────────────────────────────────────────
        try {
            $result = $service->importInto($model, $parsed['rows']);
        } catch (FatalCsvException $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'step'  => 'import',
                'case'  => $e->case,
                'error' => $e->getMessage(),
            ]);
        }

        // ── 7. Réponse complète ─────────────────────────────────────
        return $this->response->setJSON([
            'alignment' => $alignment,
            'result'    => $result,
        ]);
    }
}
```

---

## 6. `app/Config/Routes.php` — lignes à ajouter

Trouver le groupe `admin` existant et ajouter les 2 routes à l'intérieur :

```php
// ── Admin (filtre admin: connecté + permission admin.panel) ─────
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
    $routes->get('/',                        'Admin\Dashboard::index');
    $routes->get('dashboard',                'Admin\Dashboard::index');
    $routes->get('users',                    'Admin\Dashboard::users');
    $routes->get('users/(:num)',             'Admin\Dashboard::userDetail/$1');
    $routes->get('users/(:num)/toggle',      'Admin\Dashboard::toggleUser/$1');
    $routes->get('users/(:num)/delete',      'Admin\Dashboard::deleteUser/$1');
    $routes->get('profile',                  'Admin\Dashboard::profile');
    $routes->post('profile',                 'Admin\Dashboard::updateProfile');

    // ── Import CSV ──────────────────────────────────────────────
    $routes->get('import',         'Admin\ImportController::index');   // ← ajouter
    $routes->post('import/upload', 'Admin\ImportController::upload');  // ← ajouter
});
```

---

## 7. `app/Database/Seeds/MainSeeder.php` — lignes à ajouter

### Dans le tableau `$permissions` (section 2 du seeder)

```php
$permissions = [
    ['slug' => 'admin.panel',    'name' => 'Accès panneau admin',        'description' => 'Voir et utiliser l\'interface admin'],
    ['slug' => 'users.manage',   'name' => 'Gérer les utilisateurs',     'description' => 'Créer, modifier, désactiver des users'],
    ['slug' => 'users.delete',   'name' => 'Supprimer des utilisateurs', 'description' => 'Suppression définitive'],
    ['slug' => 'types.manage',   'name' => 'Gérer les types',            'description' => 'Créer et modifier les user_types'],
    ['slug' => 'wallet.view',    'name' => 'Voir son solde',             'description' => 'Accès à la page wallet'],
    ['slug' => 'wallet.manage',  'name' => 'Gérer les soldes',           'description' => 'Créditer / débiter des users'],
    ['slug' => 'content.read',   'name' => 'Lire le contenu',            'description' => 'Accès aux ressources publiques'],
    ['slug' => 'content.manage', 'name' => 'Modérer le contenu',         'description' => 'Modifier / supprimer du contenu'],
    ['slug' => 'import.csv',     'name' => 'Importer des CSV',           'description' => 'Accès à l\'outil d\'import CSV'],  // ← ajouter
];
```

### Dans le tableau `$pivot` (section 3 du seeder)

```php
$pivot = [
    // Admin : tout
    ['id_type' => $types['admin'], 'id_permission' => $perms['admin.panel']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['users.manage']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['users.delete']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['types.manage']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['wallet.manage']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['content.read']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['content.manage']],
    ['id_type' => $types['admin'], 'id_permission' => $perms['import.csv']],   // ← ajouter
    // User : wallet + contenu
    ['id_type' => $types['user'], 'id_permission' => $perms['wallet.view']],
    ['id_type' => $types['user'], 'id_permission' => $perms['content.read']],
    // Moderateur : contenu uniquement
    ['id_type' => $types['moderator'], 'id_permission' => $perms['content.read']],
    ['id_type' => $types['moderator'], 'id_permission' => $perms['content.manage']],
];
```

---

## 8. `app/Views/admin/import/index.php` — nouveau fichier complet

```php
<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div style="max-width:640px">
    <h2><?= esc($pageTitle) ?></h2>

    <form id="import-form" action="<?= site_url('admin/import/upload') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div style="margin-bottom:1rem">
            <label>Fichier CSV</label><br>
            <input type="file" name="csv_file" accept=".csv,.txt" required>
        </div>

        <div style="margin-bottom:1rem">
            <label>Table cible</label><br>
            <select name="table" required>
                <?php foreach ($allowedTables as $t): ?>
                    <option value="<?= esc($t) ?>"><?= esc($t) ?></option>
                <?php endforeach ?>
            </select>
        </div>

        <div style="margin-bottom:1.5rem">
            <label>Séparateur</label><br>
            <select name="separator">
                <option value="auto">Auto-détection</option>
                <option value=";">Point-virgule  ;  </option>
                <option value=",">Virgule  ,</option>
                <option value="|">Pipe  |</option>
                <option value="	">Tabulation</option>
            </select>
        </div>

        <button type="submit">Importer</button>
    </form>

    <!-- Zone de résultat — cachée jusqu'à la réponse -->
    <div id="import-result" style="display:none; margin-top:2rem"></div>
</div>

<script>
document.getElementById('import-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const result = document.getElementById('import-result');
    result.style.display = 'none';
    result.innerHTML = '';

    const res  = await fetch(this.action, { method: 'POST', body: new FormData(this) });
    const data = await res.json();

    let html = '';

    // Erreur fatale
    if (data.error) {
        html += `<p style="color:red"><strong>Erreur [${data.step ?? ''}] :</strong> ${data.error}</p>`;
        if (data.alignment?.required_missing?.length) {
            html += '<p>Colonnes bloquantes manquantes : <strong>' + data.alignment.required_missing.join(', ') + '</strong></p>';
        }
        result.innerHTML = html;
        result.style.display = 'block';
        return;
    }

    // Avertissements d'alignement
    if (data.alignment) {
        const { extra, missing_nullable, required_missing } = data.alignment;
        if (extra?.length)           html += `<p style="color:orange">Colonnes ignorées (absentes en BDD) : ${extra.join(', ')}</p>`;
        if (missing_nullable?.length) html += `<p style="color:orange">Colonnes BDD nullable non fournies : ${missing_nullable.join(', ')}</p>`;
        if (required_missing?.length) html += `<p style="color:red">Colonnes requises manquantes : ${required_missing.join(', ')}</p>`;
    }

    // Rapport d'import
    if (data.result) {
        const { inserted, skipped, total, errors } = data.result;
        html += `<p style="color:green"><strong>${inserted} / ${total}</strong> lignes insérées — ${skipped} ignorées.</p>`;
        if (errors?.length) {
            html += '<ul>';
            errors.forEach(e => { html += `<li>Ligne ${e.line} [${e.case}] : ${e.message}</li>`; });
            html += '</ul>';
        }
    }

    result.innerHTML = html;
    result.style.display = 'block';
});
</script>

<?= $this->endSection() ?>
```

---

## 9. Ordre d'implémentation

```
1. app/Enums/ErrorTolerance.php
2. app/Exceptions/FatalCsvException.php
3. app/Exceptions/NonFatalCsvException.php
4. app/Services/CsvImportService.php
5. app/Controllers/Admin/ImportController.php
6. app/Config/Routes.php          ← 2 lignes à ajouter
7. app/Database/Seeds/MainSeeder.php  ← 2 zones à modifier
8. app/Views/admin/import/index.php
```

> Après modification du seeder, relancer :
> ```bash
> php spark db:seed MainSeeder
> ```
