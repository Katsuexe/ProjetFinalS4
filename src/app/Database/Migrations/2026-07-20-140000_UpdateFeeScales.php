<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateFeeScales extends Migration
{
    public function up(): void
    {
        // 1. Vider les anciens barèmes
        $this->db->table('fee_scales')->truncate();

        // 2. Récupérer les IDs des opérations
        $withdrawOp = $this->db->table('operation_types')->where('slug', 'withdraw')->get()->getRow();
        $transferOp = $this->db->table('operation_types')->where('slug', 'transfer')->get()->getRow();

        if (!$withdrawOp || !$transferOp) {
            return;
        }

        $withdrawId = $withdrawOp->id;
        $transferId = $transferOp->id;

        // 3. Insérer les nouveaux barèmes stricts
        $scales = [
            [100.00, 1000.00, 50.00],
            [1001.00, 5000.00, 50.00],
            [5001.00, 10000.00, 100.00],
            [10001.00, 25000.00, 200.00],
            [25001.00, 50000.00, 400.00],
            [50001.00, 100000.00, 800.00],
            [100001.00, 250000.00, 1500.00],
            [250001.00, 500000.00, 1500.00],
            [500001.00, 1000000.00, 2500.00],
            [1000001.00, 2000000.00, 3000.00],
        ];

        $data = [];
        foreach ($scales as $s) {
            $data[] = [
                'operation_type_id' => $withdrawId,
                'min_amount'        => $s[0],
                'max_amount'        => $s[1],
                'fee_amount'        => $s[2],
            ];
            $data[] = [
                'operation_type_id' => $transferId,
                'min_amount'        => $s[0],
                'max_amount'        => $s[1],
                'fee_amount'        => $s[2],
            ];
        }

        $this->db->table('fee_scales')->insertBatch($data);
    }

    public function down(): void
    {
        $this->db->table('fee_scales')->truncate();
    }
}
