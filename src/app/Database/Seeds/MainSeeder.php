<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MainSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        // ── 1. Types d'utilisateurs ─────────────────────────────────
        $this->db->table('user_types')->insertBatch([
            ['name' => 'Administrateur', 'slug' => 'admin',     'description' => 'Accès total à l\'application', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Utilisateur',    'slug' => 'user',      'description' => 'Compte standard avec wallet',   'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Modérateur',     'slug' => 'moderator', 'description' => 'Peut gérer le contenu',         'created_at' => $now, 'updated_at' => $now],
        ]);

        $types = [];
        foreach ($this->db->table('user_types')->get()->getResultArray() as $t) {
            $types[$t['slug']] = $t['id'];
        }

        // ── 2. Permissions ──────────────────────────────────────────
        $permissions = [
            ['slug' => 'admin.panel',    'name' => 'Accès panneau admin',           'description' => 'Voir et utiliser l\'interface admin'],
            ['slug' => 'users.manage',   'name' => 'Gérer les utilisateurs',        'description' => 'Créer, modifier, désactiver des users'],
            ['slug' => 'users.create',   'name' => 'Créer des utilisateurs',        'description' => 'Permission étroite : créer un compte "user" sans pouvoir le modifier/désactiver/supprimer (typiquement accordée au modérateur)'],
            ['slug' => 'users.delete',   'name' => 'Supprimer des utilisateurs',    'description' => 'Suppression définitive'],
            ['slug' => 'types.manage',   'name' => 'Gérer les types',               'description' => 'Créer et modifier les user_types'],
            ['slug' => 'wallet.view',    'name' => 'Voir son solde',                'description' => 'Accès à la page wallet'],
            ['slug' => 'wallet.manage',  'name' => 'Gérer les soldes',              'description' => 'Créditer / débiter des users'],
            ['slug' => 'content.read',   'name' => 'Lire le contenu',               'description' => 'Accès aux ressources publiques'],
            ['slug' => 'content.manage', 'name' => 'Modérer le contenu',            'description' => 'Modifier / supprimer du contenu'],
            ['slug' => 'import.csv',     'name' => 'Importer des CSV',              'description' => 'Accès outil import CSV'],
        ];

        foreach ($permissions as &$p) {
            $p['created_at'] = $now;
            $p['updated_at'] = $now;
        }
        $this->db->table('permissions')->insertBatch($permissions);

        $perms = [];
        foreach ($this->db->table('permissions')->get()->getResultArray() as $p) {
            $perms[$p['slug']] = $p['id'];
        }

        // ── 3. Assignation permissions → types (pivot) ──────────────
        $pivot = [
            // Admin : tout
            ['id_type' => $types['admin'], 'id_permission' => $perms['admin.panel']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['users.manage']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['users.create']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['users.delete']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['types.manage']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['wallet.manage']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['content.read']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['content.manage']],
            ['id_type' => $types['admin'], 'id_permission' => $perms['import.csv']],
            // User : wallet + contenu
            ['id_type' => $types['user'], 'id_permission' => $perms['wallet.view']],
            ['id_type' => $types['user'], 'id_permission' => $perms['content.read']],
            // Moderateur : contenu + création d'utilisateurs
            ['id_type' => $types['moderator'], 'id_permission' => $perms['admin.panel']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['users.create']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['content.read']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['content.manage']],
        ];
        $this->db->table('user_type_permissions')->insertBatch($pivot);

        // ── 4. Mobile Money : Préfixes & Opérations ────────────────
        $this->db->table('operator_prefixes')->insertBatch([
            ['prefix' => '033', 'created_at' => $now, 'updated_at' => $now],
            ['prefix' => '037', 'created_at' => $now, 'updated_at' => $now],
            ['prefix' => '034', 'created_at' => $now, 'updated_at' => $now],
            ['prefix' => '038', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('operation_types')->insertBatch([
            ['name' => 'Dépôt', 'slug' => 'deposit'],
            ['name' => 'Retrait', 'slug' => 'withdraw'],
            ['name' => 'Transfert', 'slug' => 'transfer'],
        ]);

        $opTypes = [];
        foreach ($this->db->table('operation_types')->get()->getResultArray() as $op) {
            $opTypes[$op['slug']] = $op['id'];
        }

        // Barème des frais pour Retrait et Transfert
        $feeScales = [
            ['min_amount' => 100, 'max_amount' => 1000, 'fee_amount' => 50],
            ['min_amount' => 1001, 'max_amount' => 5000, 'fee_amount' => 50],
            ['min_amount' => 5001, 'max_amount' => 10000, 'fee_amount' => 100],
            ['min_amount' => 10001, 'max_amount' => 25000, 'fee_amount' => 200],
            ['min_amount' => 25001, 'max_amount' => 50000, 'fee_amount' => 400],
            ['min_amount' => 50001, 'max_amount' => 100000, 'fee_amount' => 800],
            ['min_amount' => 100001, 'max_amount' => 250000, 'fee_amount' => 1500],
            ['min_amount' => 250001, 'max_amount' => 500000, 'fee_amount' => 1500],
            ['min_amount' => 500001, 'max_amount' => 1000000, 'fee_amount' => 2500],
            ['min_amount' => 1000001, 'max_amount' => 2000000, 'fee_amount' => 3000],
        ];

        $feeInserts = [];
        foreach (['withdraw', 'transfer'] as $typeSlug) {
            foreach ($feeScales as $scale) {
                $scale['operation_type_id'] = $opTypes[$typeSlug];
                $feeInserts[] = $scale;
            }
        }
        $this->db->table('fee_scales')->insertBatch($feeInserts);

        // ── 5. Utilisateurs de démonstration ────────────────────────
        $users = [
            [
                'username'   => 'Admin',
                'email'      => 'admin@example.com',
                'phone'      => null,
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'id_type'    => $types['admin'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'Alice',
                'email'      => null, // Les clients utilisent uniquement le phone
                'phone'      => '0331234567',
                'password'   => null,
                'id_type'    => $types['user'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'Modo',
                'email'      => 'mod@example.com',
                'phone'      => null,
                'password'   => password_hash('password123', PASSWORD_DEFAULT),
                'id_type'    => $types['moderator'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'Bob',
                'email'      => null,
                'phone'      => '0341234567',
                'password'   => null,
                'id_type'    => $types['user'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];
        $this->db->table('users')->insertBatch($users);

        // ── 6. Soldes pour les users avec wallet.view ────────────────
        $aliceId = $this->db->table('users')->where('phone', '0331234567')->get()->getRowArray()['id'];
        $bobId = $this->db->table('users')->where('phone', '0341234567')->get()->getRowArray()['id'];
        
        $balances = [
            [
                'id_user'    => $aliceId,
                'balance'    => 150000.00,
                'currency'   => 'Ar',
                'updated_at' => $now,
            ],
            [
                'id_user'    => $bobId,
                'balance'    => 50000.00,
                'currency'   => 'Ar',
                'updated_at' => $now,
            ]
        ];
        $this->db->table('user_balances')->insertBatch($balances);
    }
}
