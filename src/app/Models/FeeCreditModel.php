<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeCreditModel extends Model
{
    protected $table            = 'fee_credits';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['user_id', 'amount_remaining', 'source_transaction_id', 'created_at'];
    protected $useTimestamps    = false;
}
