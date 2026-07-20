<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model pour la table permissions.
 *
 * PEDAGOGIE : jusqu'ici le projet n'avait pas de Model dédié pour cette
 * table (les contrôleurs interrogeaient `permissions` via des requêtes
 * Query Builder brutes, ex: Admin\Types::index()). On l'ajoute ici pour
 * rester cohérent avec le reste du projet (une table = un Model), et pour
 * permettre au formulaire de types (admin/types/create.php et edit.php)
 * d'afficher la liste complète des permissions disponibles sous forme de
 * cases à cocher.
 */
class PermissionModel extends Model
{
    protected $table            = 'permissions';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = ['name', 'slug', 'description'];

    protected $validationRules = [
        'name' => 'required|max_length[150]',
        'slug' => 'required|max_length[150]|is_unique[permissions.slug,id,{id}]',
    ];
}
