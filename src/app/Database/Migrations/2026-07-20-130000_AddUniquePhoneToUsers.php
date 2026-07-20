<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Ajout de la contrainte UNIQUE sur la colonne phone dans la table users.
 * 1 numéro de téléphone = 1 utilisateur.
 */
class AddUniquePhoneToUsers extends Migration
{
    public function up(): void
    {
        $driver = $this->db->DBDriver;

        if (strtolower($driver) === 'sqlite3') {
            // SQLite : recréation de la table avec la contrainte UNIQUE sur phone
            $this->db->query('PRAGMA foreign_keys = OFF');
            $this->db->query('
                CREATE TABLE IF NOT EXISTS users_new (
                    id         INTEGER PRIMARY KEY AUTOINCREMENT,
                    username   VARCHAR(100) NOT NULL,
                    phone      VARCHAR(20)  UNIQUE DEFAULT NULL,
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
            // MySQL / MariaDB : ajout direct de l'index unique
            $this->db->query('ALTER TABLE users ADD UNIQUE (phone)');
        }
    }

    public function down(): void
    {
        $driver = $this->db->DBDriver;

        if (strtolower($driver) === 'sqlite3') {
            // SQLite : retrait de la contrainte UNIQUE via recréation
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
            // MySQL / MariaDB : suppression de l'index
            try {
                $this->db->query('ALTER TABLE users DROP INDEX phone');
            } catch (\Exception $e) {}
        }
    }
}
