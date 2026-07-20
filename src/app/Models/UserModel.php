<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model principal pour la table users.
 *
 * Toutes les requêtes passent par le Query Builder de CI4,
 * compatible avec tous les drivers (MySQL, Postgre, SQLite3, SQLSRV, OCI8).
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'username', 'email', 'phone', 'password', 'photo',
        'id_type', 'is_active', 'last_login',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // ── Requêtes métier ───────────────────────────────────────────────────────

    /**
     * Authentifie un utilisateur par email + mot de passe.
     * Retourne le tableau user enrichi (type + permissions) ou null.
     */
    public function authenticate(string $email, string $password): ?array
    {
        $user = $this->withType()
                     ->where('users.email', $email)
                     ->where('users.is_active', 1)
                     ->first();

        if (! $user || ! password_verify($password, $user['password'])) {
            return null;
        }

        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        $user['permissions'] = $this->getPermissionSlugs((int) $user['id_type']);

        return $user;
    }

    /**
     * Retourne les slugs de permissions pour un type d'utilisateur.
     *
     * Utilise des noms de tables complets (sans alias) pour une compatibilité
     * maximale avec tous les drivers CI4.
     */
    public function getPermissionSlugs(int $idType): array
    {
        $rows = $this->db
            ->table('user_type_permissions')
            ->select('permissions.slug')
            ->join('permissions', 'permissions.id = user_type_permissions.id_permission')
            ->where('user_type_permissions.id_type', $idType)
            ->get()
            ->getResultArray();

        return array_column($rows, 'slug');
    }

    /**
     * Scope : ajoute le type d'utilisateur au SELECT.
     * Permet d'enchaîner d'autres conditions Query Builder après.
     */
    public function withType(): static
    {
        return $this->select('users.*, user_types.name AS type_name, user_types.slug AS type_slug')
                    ->join('user_types', 'user_types.id = users.id_type', 'left');
    }

    /**
     * Statistiques globales pour le dashboard admin.
     */
    public function getStats(): array
    {
        return [
            'total_users'    => $this->countAllResults(false),
            'active_users'   => $this->where('is_active', 1)->countAllResults(false),
            'inactive_users' => $this->where('is_active', 0)->countAllResults(false),
            'total_types'    => $this->db->table('user_types')->countAllResults(),
        ];
    }

    /**
     * Derniers utilisateurs inscrits, avec leur type.
     */
    public function getRecentWithType(int $limit = 10): array
    {
        return $this->withType()
                    ->orderBy('users.created_at', 'DESC')
                    ->findAll($limit);
    }

    /**
     * Hash d'un mot de passe en clair.
     */
    public function hashPassword(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }
}
