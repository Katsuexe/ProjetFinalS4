<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Settings — Gestion des préfixes opérateur et barèmes de frais
 * Accessible uniquement aux administrateurs (filtre admin + permission types.manage).
 */
class Settings extends BaseController
{
    protected $db;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        helper(['auth', 'url', 'form']);
    }

    // ─── PRÉFIXES ────────────────────────────────────────────────────────────

    public function prefixes(): string
    {
        return view('admin/settings/prefixes', [
            'title'     => 'Préfixes opérateur',
            'pageTitle' => 'Gestion des préfixes',
            'prefixes'  => $this->db->table('operator_prefixes')->orderBy('prefix')->get()->getResultArray(),
        ]);
    }

    public function storePrefix()
    {
        $prefix = trim($this->request->getPost('prefix'));
        if (empty($prefix) || !preg_match('/^\d{3,4}$/', $prefix)) {
            return redirect()->back()->with('error', 'Préfixe invalide (3 ou 4 chiffres requis).');
        }

        $exists = $this->db->table('operator_prefixes')->where('prefix', $prefix)->countAllResults();
        if ($exists) {
            return redirect()->back()->with('error', "Le préfixe {$prefix} existe déjà.");
        }

        $this->db->table('operator_prefixes')->insert([
            'prefix'     => $prefix,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/settings/prefixes')->with('success', "Préfixe {$prefix} ajouté.");
    }

    public function deletePrefix(int $id)
    {
        $row = $this->db->table('operator_prefixes')->where('id', $id)->get()->getRowArray();
        if (!$row) {
            return redirect()->to('/admin/settings/prefixes')->with('error', 'Préfixe introuvable.');
        }
        $this->db->table('operator_prefixes')->where('id', $id)->delete();
        return redirect()->to('/admin/settings/prefixes')->with('success', "Préfixe {$row['prefix']} supprimé.");
    }

    // ─── BARÈMES DE FRAIS ────────────────────────────────────────────────────

    public function fees(): string
    {
        $opTypes = $this->db->table('operation_types')
                            ->whereIn('slug', ['withdraw', 'transfer'])
                            ->get()->getResultArray();

        $feesByType = [];
        foreach ($opTypes as $op) {
            $feesByType[$op['id']] = [
                'name'  => $op['name'],
                'slug'  => $op['slug'],
                'scales' => $this->db->table('fee_scales')
                                     ->where('operation_type_id', $op['id'])
                                     ->orderBy('min_amount')
                                     ->get()->getResultArray(),
            ];
        }

        return view('admin/settings/fees', [
            'title'       => 'Barèmes de frais',
            'pageTitle'   => 'Gestion des barèmes',
            'feesByType'  => $feesByType,
            'opTypes'     => $opTypes,
        ]);
    }

    public function storeFee()
    {
        $data = [
            'operation_type_id' => (int) $this->request->getPost('operation_type_id'),
            'min_amount'        => (float) $this->request->getPost('min_amount'),
            'max_amount'        => (float) $this->request->getPost('max_amount'),
            'fee_amount'        => (float) $this->request->getPost('fee_amount'),
        ];

        if ($data['min_amount'] >= $data['max_amount'] || $data['fee_amount'] < 0) {
            return redirect()->back()->with('error', 'Valeurs de tranche invalides.');
        }

        $this->db->table('fee_scales')->insert($data);
        return redirect()->to('/admin/settings/fees')->with('success', 'Barème ajouté.');
    }

    public function deleteFee(int $id)
    {
        $this->db->table('fee_scales')->where('id', $id)->delete();
        return redirect()->to('/admin/settings/fees')->with('success', 'Barème supprimé.');
    }
}
