<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationTypeModel extends Model
{
    protected $table            = 'operation_types';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $allowedFields    = ['name', 'slug'];

    /**
     * Retourne l'ID d'une opération à partir de son slug.
     */
    public function getIdBySlug(string $slug): ?int
    {
        $op = $this->where('slug', $slug)->first();
        return $op ? (int) $op['id'] : null;
    }
}
