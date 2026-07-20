-- ============================================================================
-- PROJET FINAL S4 : BlueMoney
-- Définition complète de la base de données (SQLite/MySQL compatible)
-- Inclut le schéma (DDL) et les données de test (DML).
-- ============================================================================

-- ----------------------------------------------------------------------------
-- 1. SCHÉMA DE LA BASE DE DONNÉES (DDL)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `user_types` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `user_type_permissions` (
    `id_type` INTEGER NOT NULL,
    `id_permission` INTEGER NOT NULL,
    PRIMARY KEY (`id_type`, `id_permission`),
    FOREIGN KEY (`id_type`) REFERENCES `user_types`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`id_permission`) REFERENCES `permissions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `users` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `username` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) UNIQUE DEFAULT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `password` VARCHAR(255) DEFAULT NULL,
    `id_type` INTEGER DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    `deleted_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`id_type`) REFERENCES `user_types`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `user_balances` (
    `id_user` INTEGER NOT NULL PRIMARY KEY,
    `balance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'Ar',
    `updated_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`id_user`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `operator_prefixes` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `prefix` VARCHAR(10) NOT NULL UNIQUE,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `operation_types` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS `fee_scales` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `operation_type_id` INTEGER NOT NULL,
    `min_amount` DECIMAL(15,2) NOT NULL,
    `max_amount` DECIMAL(15,2) NOT NULL,
    `fee_amount` DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `recipient_id` INTEGER DEFAULT NULL,
    `operation_type_id` INTEGER NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `fee_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
);


-- ----------------------------------------------------------------------------
-- 2. DONNÉES DE TEST (DML)
-- ----------------------------------------------------------------------------

-- Types d'utilisateurs
INSERT INTO `user_types` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Administrateur', 'admin', 'Accès complet au système'),
(2, 'Modérateur', 'moderator', 'Accès limité au panel d\'administration'),
(3, 'Utilisateur', 'user', 'Client Mobile Money normal');

-- Permissions
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Accès Panel Admin', 'admin.panel', 'Peut se connecter au tableau de bord administrateur'),
(2, 'Gérer Utilisateurs', 'users.manage', 'Peut lister et modifier les utilisateurs'),
(3, 'Créer Utilisateurs', 'users.create', 'Peut créer de nouveaux utilisateurs'),
(4, 'Supprimer Utilisateurs', 'users.delete', 'Peut supprimer des utilisateurs'),
(5, 'Voir Solde', 'wallet.view', 'Peut voir son solde Mobile Money');

-- Association Types / Permissions
INSERT INTO `user_type_permissions` (`id_type`, `id_permission`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), -- Admin
(2, 1), (2, 2), (2, 5),                 -- Modo
(3, 5);                                 -- User

-- Utilisateurs (mot de passe pour admin/modo : "password123")
INSERT INTO `users` (`id`, `username`, `phone`, `email`, `password`, `id_type`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin', NULL, 'admin@example.com', '$2y$10$Q7eYlTfVv.oXNqY9F31L/OTlX4M8b1N3Fm/5q3.0fJk9/Y.E9G2eG', 1, 1, '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
(2, 'Alice', '0331234567', NULL, NULL, 3, 1, '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
(3, 'Bob', '0341234567', NULL, NULL, 3, 1, '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
(4, 'Modo', NULL, 'mod@example.com', '$2y$10$Q7eYlTfVv.oXNqY9F31L/OTlX4M8b1N3Fm/5q3.0fJk9/Y.E9G2eG', 2, 1, '2026-07-20 10:00:00', '2026-07-20 10:00:00');

-- Soldes initiaux
INSERT INTO `user_balances` (`id_user`, `balance`, `currency`, `updated_at`) VALUES
(2, 150000.00, 'Ar', '2026-07-20 10:00:00'), -- Solde Alice
(3, 50000.00, 'Ar', '2026-07-20 10:00:00');  -- Solde Bob

-- Préfixes opérateurs acceptés
INSERT INTO `operator_prefixes` (`prefix`, `created_at`, `updated_at`) VALUES
('032', '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
('033', '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
('034', '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
('037', '2026-07-20 10:00:00', '2026-07-20 10:00:00'),
('038', '2026-07-20 10:00:00', '2026-07-20 10:00:00');

-- Types d'opérations
INSERT INTO `operation_types` (`id`, `name`, `slug`) VALUES
(1, 'Dépôt', 'deposit'),
(2, 'Retrait', 'withdraw'),
(3, 'Transfert', 'transfer');

-- Barèmes de frais
INSERT INTO `fee_scales` (`operation_type_id`, `min_amount`, `max_amount`, `fee_amount`) VALUES
(2, 0.00, 10000.00, 100.00),         -- Retrait [0-10000] -> 100 Ar
(2, 10000.01, 50000.00, 500.00),     -- Retrait [10001-50000] -> 500 Ar
(2, 50000.01, 100000.00, 1000.00),   -- Retrait [50001-100000] -> 1000 Ar
(2, 100000.01, 9999999.00, 2500.00), -- Retrait [100001+] -> 2500 Ar
(3, 0.00, 50000.00, 100.00),         -- Transfert [0-50000] -> 100 Ar
(3, 50000.01, 9999999.00, 200.00);   -- Transfert [50001+] -> 200 Ar
