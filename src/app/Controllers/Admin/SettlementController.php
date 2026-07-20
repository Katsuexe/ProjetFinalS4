<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class SettlementController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();

        $due = $model->select('external_operators.id, external_operators.nom, SUM(transactions.amount) as montant_du, COUNT(transactions.id) as nb_transactions')
            ->join('external_operators', 'external_operators.id = transactions.external_operator_id')
            ->where('transactions.envoye', 0)
            ->groupBy('external_operators.id')
            ->findAll();

        return view('admin/settlement/index', ['due' => $due]);
    }

    public function marquerEnvoye($externalOperatorId)
    {
        $model = new TransactionModel();
        $db = \Config\Database::connect();
        $db->transStart();

        $model->where('external_operator_id', $externalOperatorId)
            ->where('envoye', 0)
            ->set(['envoye' => 1, 'date_envoi' => date('Y-m-d H:i:s')])
            ->update();

        $db->transComplete();

        return redirect()->to('/admin/montants-a-envoyer')->with('success', 'Montants marqués comme envoyés.');
    }
}
