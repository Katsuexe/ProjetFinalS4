<?php

namespace App\Models;

use CodeIgniter\Model;

class OperatorPrefixModel extends Model
{
    protected $table            = 'operator_prefixes';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['prefix'];
    protected $useTimestamps    = true;
}
