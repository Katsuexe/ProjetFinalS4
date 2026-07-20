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

CREATE TABLE IF NOT EXISTS `external_operators` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `commission_pourcentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `created_at` DATETIME DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS `external_operator_prefixes` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `external_operator_id` INTEGER NOT NULL,
    `prefix` VARCHAR(5) NOT NULL,
    FOREIGN KEY (`external_operator_id`) REFERENCES `external_operators`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `fee_credits` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `amount_remaining` DECIMAL(15,2) NOT NULL,
    `source_transaction_id` INTEGER NOT NULL,
    `created_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL,
    `recipient_id` INTEGER DEFAULT NULL,
    `external_operator_id` INTEGER DEFAULT NULL,
    `external_phone` VARCHAR(20) DEFAULT NULL,
    `operation_type_id` INTEGER NOT NULL,
    `amount` DECIMAL(15,2) NOT NULL,
    `fee_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `commission_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `envoye` TINYINT(1) NOT NULL DEFAULT 0,
    `date_envoi` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (`operation_type_id`) REFERENCES `operation_types`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`external_operator_id`) REFERENCES `external_operators`(`id`) ON DELETE SET NULL
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
('034', '2026-07-20 10:00:00', '2026-07-20 10:00:00');

-- Opérateurs externes (V2)
INSERT INTO `external_operators` (`id`, `nom`, `commission_pourcentage`, `created_at`) VALUES
(1, 'Orange Money', 2.00, '2026-07-20 10:00:00'),
(2, 'Airtel Money', 2.50, '2026-07-20 10:00:00');

-- Préfixes opérateurs externes (V2)
INSERT INTO `external_operator_prefixes` (`external_operator_id`, `prefix`) VALUES
(1, '037'),
(2, '038');

-- Types d'opérations
INSERT INTO `operation_types` (`id`, `name`, `slug`) VALUES
(1, 'Dépôt', 'deposit'),
(2, 'Retrait', 'withdraw'),
(3, 'Transfert', 'transfer');

-- Barèmes de frais
INSERT INTO `fee_scales` (`operation_type_id`, `min_amount`, `max_amount`, `fee_amount`) VALUES
-- Frais de Retrait (operation_type_id = 2)
(2, 100.00, 1000.00, 50.00),
(2, 1001.00, 5000.00, 50.00),
(2, 5001.00, 10000.00, 100.00),
(2, 10001.00, 25000.00, 200.00),
(2, 25001.00, 50000.00, 400.00),
(2, 50001.00, 100000.00, 800.00),
(2, 100001.00, 250000.00, 1500.00),
(2, 250001.00, 500000.00, 1500.00),
(2, 500001.00, 1000000.00, 2500.00),
(2, 1000001.00, 2000000.00, 3000.00),
-- Frais de Transfert (operation_type_id = 3)
(3, 100.00, 1000.00, 50.00),
(3, 1001.00, 5000.00, 50.00),
(3, 5001.00, 10000.00, 100.00),
(3, 10001.00, 25000.00, 200.00),
(3, 25001.00, 50000.00, 400.00),
(3, 50001.00, 100000.00, 800.00),
(3, 100001.00, 250000.00, 1500.00),
(3, 250001.00, 500000.00, 1500.00),
(3, 500001.00, 1000000.00, 2500.00),
(3, 1000001.00, 2000000.00, 3000.00);
