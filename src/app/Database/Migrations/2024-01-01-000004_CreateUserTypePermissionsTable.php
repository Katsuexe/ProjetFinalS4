<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTypePermissionsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id_type'       => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'id_permission' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
        ]);

        $this->forge->addPrimaryKey(['id_type', 'id_permission']);

        // Clés étrangères via Forge — portable (contrairement au ALTER TABLE
        // brut ci-dessous qui n'est pas valide en SQLite).
        $this->forge->addForeignKey('id_type', 'user_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_permission', 'permissions', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_type_permissions');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_type_permissions');
    }
}
