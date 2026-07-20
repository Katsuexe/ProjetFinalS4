<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MobileMoneyTables extends Migration
{
    public function up()
    {
        // 1. Modifier la table `users` pour supporter le numéro de téléphone
        // On ajoute la colonne `phone` et on rend `email` et `password` optionnels
        $fields = [
            'phone' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'null'       => true,
                'after'      => 'username'
            ],
        ];
        $this->forge->addColumn('users', $fields);

        // SQLite & MySQL ne permettent pas toujours de modifier facilement des colonnes pour les rendre NULL (via Forge modifyColumn).
        // Cependant dans SQLite on peut bidouiller ou on peut juste considérer qu'elles sont déjà là et on insérera des valeurs NULL ou vides
        // car le seeder d'origine ne force pas toujours NOT NULL au niveau de la DB.
        // Si besoin de modifier email/password pour autoriser NULL (ce qui est préférable) :
        // Note : Forge->modifyColumn() est instable sous SQLite. Nous allons gérer la "nullabilité" au niveau du Model (ignorer si vide).

        // 2. Table `operator_prefixes`
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'prefix' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'unique'     => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('operator_prefixes');

        // 3. Table `operation_types` (dépôt, retrait, transfert)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('operation_types');

        // 4. Table `fee_scales` (barèmes de frais)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'operation_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'min_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'max_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'fee_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('operation_type_id', 'operation_types', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('fee_scales');

        // 5. Table `transactions`
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'recipient_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true, // NULL pour les dépôts ou retraits simples
            ],
            'operation_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
            ],
            'fee_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('recipient_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('operation_type_id', 'operation_types', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions', true);
        $this->forge->dropTable('fee_scales', true);
        $this->forge->dropTable('operation_types', true);
        $this->forge->dropTable('operator_prefixes', true);
        $this->forge->dropColumn('users', 'phone');
    }
}
