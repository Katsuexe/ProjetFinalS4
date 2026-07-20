<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddExternalOperatorsV2 extends Migration
{
    public function up()
    {
        // Opérateurs externes (décision 6A : commission stockée directement ici)
        $this->forge->addField([
            'id'                     => ['type' => 'INTEGER', 'auto_increment' => true],
            'nom'                    => ['type' => 'VARCHAR', 'constraint' => 100],
            'commission_pourcentage' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('external_operators');

        // Préfixes liés à un opérateur externe (un opérateur peut avoir plusieurs préfixes)
        $this->forge->addField([
            'id'                  => ['type' => 'INTEGER', 'auto_increment' => true],
            'external_operator_id'=> ['type' => 'INTEGER', 'constraint' => 11],
            'prefix'              => ['type' => 'VARCHAR', 'constraint' => 5],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('external_operator_id', 'external_operators', 'id', '', 'CASCADE');
        $this->forge->createTable('external_operator_prefixes');

        // Extension de transactions (décision 1A : pas de compte, juste une trace)
        $fields = [
            'external_operator_id' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true, 'after' => 'recipient_id'],
            'external_phone'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true, 'after' => 'external_operator_id'],
            'commission_amount'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => 0, 'after' => 'fee_amount'],
            // décision 3B : workflow de règlement avec statut
            'envoye'               => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'date_envoi'           => ['type' => 'DATETIME', 'null' => true],
        ];
        $this->forge->addColumn('transactions', $fields);

        // décision 4A : crédits de frais de retrait (transfert "frais inclus")
        $this->forge->addField([
            'id'                    => ['type' => 'INTEGER', 'auto_increment' => true],
            'user_id'               => ['type' => 'INTEGER', 'constraint' => 11],
            'amount_remaining'      => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'source_transaction_id' => ['type' => 'INTEGER', 'constraint' => 11],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('fee_credits');
    }

    public function down()
    {
        $this->forge->dropTable('fee_credits', true);
        
        $this->forge->dropColumn('transactions', ['external_operator_id', 'external_phone', 'commission_amount', 'envoye', 'date_envoi']);
        
        $this->forge->dropTable('external_operator_prefixes', true);
        $this->forge->dropTable('external_operators', true);
    }
}
