<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Table permissions
 * Chaque permission est identifiée par un slug (ex: 'admin.panel', 'users.manage').
 * Les permissions sont assignées aux user_types, pas aux users directement.
 */
class CreatePermissionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'comment'    => 'Nom lisible (ex: Accès panneau admin)',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
                'comment'    => 'Identifiant machine (ex: admin.panel)',
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('permissions');
    }

    public function down(): void
    {
        $this->forge->dropTable('permissions');
    }
}
