<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 *
 * Le driver actif est contrôlé UNIQUEMENT par le .env :
 *   database.default.DBDriver = MySQLi | Postgre | SQLite3 | SQLSRV | OCI8
 *
 * Aucun changement de code n'est nécessaire pour changer de base de données.
 */
class Database extends Config
{
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    public string $defaultGroup = 'default';

    /**
     * Connexion par défaut — tous les champs de tous les drivers sont présents.
     * CI4 ignore les clés inconnues du driver actif.
     *
     * Champs utilisés par driver :
     *  MySQLi  → hostname, username, password, database, port, charset, DBCollat,
     *             pConnect, encrypt, compress, strictOn, numberNative, foundRows
     *  Postgre → hostname, username, password, database, port, charset, schema
     *  SQLite3 → database (chemin fichier ou ":memory:"), foreignKeys, busyTimeout, synchronous
     *  SQLSRV  → hostname, username, password, database, port, schema, encrypt
     *  OCI8    → DSN (ex: "localhost:1521/XEPDB1"), username, password, charset
     *
     * @var array<string, mixed>
     */
    public array $default = [
        // ── Commun ───────────────────────────────────────────────────────
        'DSN'      => '',            // OCI8 : connexion string. Autres : laisser vide.
        'DBDriver' => 'MySQLi',      // Surchargé par database.default.DBDriver dans .env
        'DBPrefix' => '',
        'DBDebug'  => true,
        'swapPre'  => '',
        'failover' => [],
        'dateFormat' => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],

        // ── MySQLi / MariaDB ─────────────────────────────────────────────
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',        // MySQLi/Postgre/SQLSRV : nom BDD | SQLite3 : chemin .db
        'charset'      => 'utf8mb4', // Postgre : 'utf8' — ajusté automatiquement
        'DBCollat'     => 'utf8mb4_general_ci',
        'pConnect'     => false,
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'port'         => 3306,      // Postgre : 5432 | SQLSRV : 1433 — ajusté auto.
        'numberNative' => false,
        'foundRows'    => false,

        // ── Postgre / SQLSRV ─────────────────────────────────────────────
        'schema'       => 'public',  // Postgre : 'public' | SQLSRV : 'dbo' — ajusté auto.

        // ── SQLite3 ──────────────────────────────────────────────────────
        'foreignKeys'  => true,
        'busyTimeout'  => 1000,
        'synchronous'  => null,
    ];

    /**
     * Connexion tests PHPUnit — toujours SQLite en mémoire.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',      // DO NOT REMOVE — requis par la suite de tests CI4
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => true,
        'failover'    => [],
        'port'        => 3306,
        'schema'      => 'public',
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
        // parent::__construct() a déjà injecté toutes les variables
        // database.default.* du .env dans $this->default.

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
            return;
        }

        $this->normalizeDriverConfig();
    }

    /**
     * Ajustements automatiques selon le driver lu dans le .env.
     * Aucune modification de code n'est requise lors d'un changement de driver.
     */
    private function normalizeDriverConfig(): void
    {
        switch ($this->default['DBDriver']) {

            case 'SQLite3':
                $this->normalizeSQLitePath();
                break;

            case 'Postgre':
                if ($this->default['charset'] === 'utf8mb4') {
                    $this->default['charset'] = 'utf8';
                }
                if ((int) $this->default['port'] === 3306) {
                    $this->default['port'] = 5432;
                }
                break;

            case 'SQLSRV':
                if ((int) $this->default['port'] === 3306) {
                    $this->default['port'] = 1433;
                }
                if ($this->default['schema'] === 'public') {
                    $this->default['schema'] = 'dbo';
                }
                break;

            case 'OCI8':
                if ((int) $this->default['port'] === 3306) {
                    $this->default['port'] = 1521;
                }
                if ($this->default['charset'] === 'utf8mb4') {
                    $this->default['charset'] = 'AL32UTF8';
                }
                break;

            // MySQLi : valeurs par défaut déjà correctes.
        }
    }

    /**
     * Résolution du chemin SQLite3 :
     *  - relatif → résolu depuis ROOTPATH (racine du projet)
     *  - absolu  → utilisé tel quel
     *  - :memory: → laissé intact
     * Le dossier parent est créé automatiquement si manquant.
     */
    private function normalizeSQLitePath(): void
    {
        $dbPath = $this->default['database'];

        if ($dbPath === '' || $dbPath === ':memory:') {
            return;
        }

        if (! $this->isAbsolutePath($dbPath)) {
            $dbPath = rtrim(ROOTPATH, DIRECTORY_SEPARATOR)
                      . DIRECTORY_SEPARATOR
                      . ltrim($dbPath, '/\\');
            $this->default['database'] = $dbPath;
        }

        $dir = dirname($dbPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Détecte un chemin absolu : Unix (/...) ou Windows (C:\...).
     */
    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || (strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':');
    }
}
