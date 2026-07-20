<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PermissionModel;
use App\Models\UserTypeModel;

/**
 * ============================================================================
 *  Admin\Types — gestion des "types d'utilisateurs" (rôles)
 * ============================================================================
 *
 * PEDAGOGIE — ce contrôleur illustre une AUTORISATION À DEUX COUCHES :
 *
 *   1) Le groupe de routes 'admin/*' impose déjà le filtre 'admin'
 *      (= connecté + permission 'admin.panel').
 *   2) Les routes de CE contrôleur ajoutent EN PLUS le filtre
 *      'permission:types.manage' (voir app/Config/Routes.php). CI4 exécute
 *      les DEUX filtres l'un après l'autre : il faut donc À LA FOIS être
 *      admin ET avoir la permission précise 'types.manage'.
 *
 *   Résultat concret avec les données du MainSeeder : le compte Admin (qui a
 *   TOUTES les permissions) peut y accéder ; un hypothétique compte "admin
 *   junior" qui aurait 'admin.panel' mais pas 'types.manage' serait bloqué
 *   ICI, alors qu'il pourrait quand même voir /admin/dashboard.
 *
 *   C'est la bonne façon de faire du RBAC (Role-Based Access Control) fin :
 *   un filtre "large" pour la zone, un filtre "précis" par ressource.
 * ============================================================================
 */
class Types extends BaseController
{
    protected UserTypeModel $typeModel;
    protected PermissionModel $permissionModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->typeModel       = new UserTypeModel();
        $this->permissionModel = new PermissionModel();
        helper(['form', 'url']);
    }

    // ─── GET /admin/types — liste des types + leurs permissions ────────────
    public function index(): string
    {
        $types = $this->typeModel->findAll();

        // Pour chaque type, on va chercher la liste de ses permissions.
        // PEDAGOGIE : ceci est une "requête N+1" volontairement simple pour
        // la lisibilité pédagogique (une requête par type, dans une boucle).
        // En production sur un gros volume, on préférerait UNE seule requête
        // avec un JOIN groupé (voir UserModel::getPermissionSlugs() pour le
        // modèle de requête équivalent).
        foreach ($types as &$type) {
            $type['permissions'] = $this->typeModel->db
                ->table('user_type_permissions')
                ->select('permissions.slug, permissions.name')
                ->join('permissions', 'permissions.id = user_type_permissions.id_permission')
                ->where('user_type_permissions.id_type', $type['id'])
                ->get()
                ->getResultArray();
        }
        unset($type); // bonne pratique : on détruit la référence après une boucle "foreach (&...)"

        return view('admin/types/index', [
            'title'     => 'Types & Rôles',
            'pageTitle' => "Gestion des types d'utilisateurs",
            'types'     => $types,
        ]);
    }

    // ─── GET /admin/types/create — formulaire de création ───────────────────
    public function create(): string
    {
        return view('admin/types/create', [
            'title'       => 'Nouveau type',
            'pageTitle'   => 'Créer un type',
            'permissions' => $this->permissionModel->orderBy('slug')->findAll(),
            // Aucune permission cochée par défaut à la création.
            'checkedIds'  => [],
        ]);
    }

    // ─── POST /admin/types — traitement du formulaire ───────────────────────
    public function store()
    {
        // PEDAGOGIE : $this->typeModel->insert() lit automatiquement les
        // règles définies dans UserTypeModel::$validationRules (is_unique,
        // etc.) car CI4 délègue la validation au modèle.
        $data = [
            'name'        => $this->request->getPost('name'),
            'slug'        => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
        ];

        $newId = $this->typeModel->insert($data, true);

        if ($newId === false) {
            // Model::errors() renvoie les erreurs de validation du modèle
            // (déclenchées par $validationRules dans UserTypeModel).
            return redirect()->back()->withInput()->with('error', implode(' ', $this->typeModel->errors()));
        }

        // PEDAGOGIE : c'est ICI que le formulaire de la capture d'écran
        // manquait une suite — le type était créé, mais aucune permission
        // ne lui était jamais assignée (voir l'ancienne note dans la vue).
        // On lit les cases cochées ('permissions[]' dans le <form>) et on
        // les synchronise via UserTypeModel::syncPermissions().
        $permissionIds = array_map('intval', $this->request->getPost('permissions') ?? []);
        $this->typeModel->syncPermissions((int) $newId, $permissionIds);

        return redirect()->to('/admin/types')->with('success', 'Type créé avec succès.');
    }

    // ─── GET /admin/types/{id}/edit — formulaire de modification ────────────
    public function edit(int $id): string
    {
        $type = $this->typeModel->find($id);
        if ($type === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Type #{$id} introuvable.");
        }

        // Ids des permissions déjà assignées à ce type, pour pré-cocher les
        // cases correspondantes dans la vue (voir admin/types/edit.php).
        $checkedIds = array_column(
            $this->typeModel->db->table('user_type_permissions')
                ->select('id_permission')
                ->where('id_type', $id)
                ->get()
                ->getResultArray(),
            'id_permission'
        );

        return view('admin/types/edit', [
            'title'       => 'Modifier un type',
            'pageTitle'   => "Modifier : {$type['name']}",
            'type'        => $type,
            'permissions' => $this->permissionModel->orderBy('slug')->findAll(),
            'checkedIds'  => array_map('intval', $checkedIds),
        ]);
    }

    // ─── POST /admin/types/{id} — traitement de la modification ─────────────
    public function update(int $id)
    {
        $type = $this->typeModel->find($id);
        if ($type === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Type #{$id} introuvable.");
        }

        $data = [
            'name'        => $this->request->getPost('name'),
            'slug'        => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
        ];

        if (! $this->typeModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(' ', $this->typeModel->errors()));
        }

        $permissionIds = array_map('intval', $this->request->getPost('permissions') ?? []);
        $this->typeModel->syncPermissions($id, $permissionIds);

        return redirect()->to('/admin/types')->with('success', 'Type mis à jour avec succès.');
    }

    // ─── GET /admin/types/{id}/delete ────────────────────────────────────────
    public function delete(int $id)
    {
        // Petite protection métier : on empêche la suppression des 3 types
        // de base créés par le seeder, pour ne pas casser les comptes de
        // démonstration (admin/user/moderator) référencés par id_type.
        $type = $this->typeModel->find($id);
        if ($type && in_array($type['slug'], ['admin', 'user', 'moderator'], true)) {
            return redirect()->to('/admin/types')->with('error', 'Impossible de supprimer un type système.');
        }

        $this->typeModel->delete($id);
        return redirect()->to('/admin/types')->with('success', 'Type supprimé.');
    }
}
