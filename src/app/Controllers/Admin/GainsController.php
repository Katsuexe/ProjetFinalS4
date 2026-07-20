<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TransactionModel;

class GainsController extends BaseController
{
    public function index()
    {
        $model = new TransactionModel();
        $stats = $model->getGainsStatsV2();

        return view('admin/gains/index', ['stats' => $stats]);
    }
}
