<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model pour la table user_types.
 *
 * Toutes les requêtes passent par le Query Builder de CI4,
 * compatible avec tous les drivers (MySQL, Postgre, SQLite3, SQLSRV, OCI8).
 */
class UserTypeModel extends Model
{
    protected $table            = 'user_types';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = ['name', 'slug', 'description'];

    // ── Validation ────────────────────────────────────────────────────────────

    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'slug' => 'required|max_length[100]|is_unique[user_types.slug,id,{id}]',
    ];

    // ── Requêtes métier ───────────────────────────────────────────────────────

    /**
     * Retrouve un type par son slug machine (ex: 'admin', 'user').
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Remplace intégralement les permissions d'un type via la table pivot
     * user_type_permissions.
     *
     * PEDAGOGIE : on fait volontairement "tout supprimer puis tout
     * réinsérer" plutôt qu'un diff (ajouter les nouvelles / retirer les
     * absentes). C'est moins optimal en nombre de requêtes, mais
     * beaucoup plus simple à lire et à prouver correct pour un débutant
     * (pas de risque d'oublier un cas de désynchronisation) — cohérent
     * avec le principe KISS de ce projet (voir DESIGN.md §0 et §5).
     *
     * @param int   $idType        id du type (user_types.id)
     * @param int[] $permissionIds ids des permissions à assigner (peut être vide)
     */
    public function syncPermissions(int $idType, array $permissionIds): void
    {
        $this->db->table('user_type_permissions')->where('id_type', $idType)->delete();

        if (empty($permissionIds)) {
            return;
        }

        $rows = [];
        foreach (array_unique(array_map('intval', $permissionIds)) as $idPermission) {
            $rows[] = ['id_type' => $idType, 'id_permission' => $idPermission];
        }

        $this->db->table('user_type_permissions')->insertBatch($rows);
    }
}
