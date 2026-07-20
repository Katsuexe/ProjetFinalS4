<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Table d'extension optionnelle — uniquement pour les users qui ont la permission wallet.view.
 * Les admins n'ont PAS d'entrée ici par défaut.
 *
 * La clé étrangère est créée via Forge (addForeignKey) plutôt qu'en SQL brut,
 * pour une compatibilité avec tous les drivers CI4.
 * Note : SQLite3 ignore silencieusement les FK (limitation du driver CI4).
 */
class CreateUserBalancesTable extends Migration
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
            'id_user' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'balance' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => 0.00,
            ],
            'currency' => [
                'type'       => 'CHAR',
                'constraint' => 3,
                'default'    => 'EUR',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('id_user');

        // Clé étrangère via Forge — SQL généré automatiquement selon le driver
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('user_balances');
    }

    public function down(): void
    {
        $this->forge->dropTable('user_balances');
    }
}
