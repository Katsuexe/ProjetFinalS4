<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Suppression de la contrainte UNIQUE sur email dans users.
 * Les clients Mobile Money n'ont pas d'email — seuls admins/modo en ont un.
 * SQLite ne supporte pas DROP INDEX via Forge::dropKey de façon fiable ;
 * on reconstruit la table via une migration propre.
 */
class RemoveEmailUniqueFromUsers extends Migration
{
    public function up(): void
    {
        // SQLite ne supporte pas DROP UNIQUE KEY directement.
        // On force via raw SQL pour SQLite, et via Forge pour MySQL.
        $driver = $this->db->DBDriver;

        if (strtolower($driver) === 'sqlite3') {
            // Recréation de la table sans l'index unique sur email
            $this->db->query('PRAGMA foreign_keys = OFF');
            $this->db->query('
                CREATE TABLE IF NOT EXISTS users_new (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    username   VARCHAR(100) NOT NULL,
                    phone      VARCHAR(20)  DEFAULT NULL,
                    email      VARCHAR(255) DEFAULT NULL,
                    password   VARCHAR(255) DEFAULT NULL,
                    id_type    INTEGER      DEFAULT NULL,
                    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
                    last_login DATETIME     DEFAULT NULL,
                    created_at DATETIME     DEFAULT NULL,
                    updated_at DATETIME     DEFAULT NULL,
                    deleted_at DATETIME     DEFAULT NULL
                )
            ');
            $this->db->query('INSERT INTO users_new SELECT id, username, phone, email, password, id_type, is_active, last_login, created_at, updated_at, deleted_at FROM users');
            $this->db->query('DROP TABLE users');
            $this->db->query('ALTER TABLE users_new RENAME TO users');
            $this->db->query('PRAGMA foreign_keys = ON');
        } else {
            // MySQL / MariaDB : drop l'index unique sur email
            try {
                $this->db->query('ALTER TABLE users DROP INDEX email');
            } catch (\Exception $e) {
                // Ignore si l'index n'existe pas
            }
        }
    }

    public function down(): void
    {
        // Remettre l'index unique sur email (best effort)
        try {
            $this->forge->addUniqueKey('email');
        } catch (\Exception $e) {
            // Ignore
        }
    }
}
