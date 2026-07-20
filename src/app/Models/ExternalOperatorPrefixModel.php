<?php

namespace App\Models;

use CodeIgniter\Model;

class ExternalOperatorPrefixModel extends Model
{
    protected $table            = 'external_operator_prefixes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['external_operator_id', 'prefix'];
    protected $useTimestamps    = false;
}
