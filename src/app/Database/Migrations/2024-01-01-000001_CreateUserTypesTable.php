<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Table user_types
 * Définit les types d'utilisateurs (admin, user, moderator, etc.)
 * Chaque type peut avoir des permissions différentes via la table pivot.
 */
class CreateUserTypesTable extends Migration
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
                'constraint' => 100,
                'comment'    => 'Nom lisible (ex: Administrateur)',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
                'comment'    => 'Identifiant machine (ex: admin)',
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
        $this->forge->createTable('user_types');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_types');
    }
}
