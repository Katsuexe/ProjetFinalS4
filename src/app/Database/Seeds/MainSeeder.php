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
            // User : wallet + contenu
            ['id_type' => $types['user'], 'id_permission' => $perms['wallet.view']],
            ['id_type' => $types['user'], 'id_permission' => $perms['content.read']],
            // Moderateur : contenu + création d'utilisateurs
            // PEDAGOGIE : 'admin.panel' est nécessaire ici car c'est le
            // filtre appliqué à TOUT le groupe de routes /admin/* (voir
            // AdminFilter + Routes.php). Sans lui, le modérateur ne pourrait
            // jamais atteindre /admin/users/create, même avec 'users.create'.
            // 'users.create' reste volontairement PLUS ÉTROITE que
            // 'users.manage' : le modérateur peut créer des comptes "user"
            // par défaut, mais ne peut ni les modifier, ni les désactiver,
            // ni les supprimer (voir Admin\Dashboard::storeUser(), qui
            // ignore le champ id_type envoyé si 'users.manage' est absent).
            ['id_type' => $types['moderator'], 'id_permission' => $perms['admin.panel']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['users.create']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['content.read']],
            ['id_type' => $types['moderator'], 'id_permission' => $perms['content.manage']],
        ];
        $this->db->table('user_type_permissions')->insertBatch($pivot);

        // ── 4. Utilisateurs de démonstration ────────────────────────
        $users = [
            [
                'username'   => 'Admin',
                'email'      => 'admin@example.com',
                'password'   => password_hash('Admin@1234', PASSWORD_DEFAULT),
                'id_type'    => $types['admin'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'Alice',
                'email'      => 'alice@example.com',
                'password'   => password_hash('User@1234', PASSWORD_DEFAULT),
                'id_type'    => $types['user'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'username'   => 'Bob',
                'email'      => 'bob@example.com',
                'password'   => password_hash('Modo@1234', PASSWORD_DEFAULT),
                'id_type'    => $types['moderator'],
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];
        $this->db->table('users')->insertBatch($users);

        // ── 5. Soldes pour les users avec wallet.view ────────────────
        $aliceId = $this->db->table('users')->where('email', 'alice@example.com')->get()->getRowArray()['id'];
        $this->db->table('user_balances')->insert([
            'id_user'    => $aliceId,
            'balance'    => 150.00,
            'currency'   => 'EUR',
            'updated_at' => $now,
        ]);
    }
}
