<?php

namespace App\Models;

use CodeIgniter\Model;

class ExternalOperatorModel extends Model
{
    protected $table            = 'external_operators';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['nom', 'commission_pourcentage', 'created_at'];
    protected $useTimestamps    = false; // We manage created_at manually or set it below
}
