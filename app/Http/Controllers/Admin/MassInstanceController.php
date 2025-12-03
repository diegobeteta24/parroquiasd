<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MassInstance;
use Barryvdh\DomPDF\Facade\Pdf;

class MassInstanceController extends Controller
{
    public function show(MassInstance $mass)
    {
        $mass = $this->loadMassWithIntentions($mass);
        return view('admin.masses.show', compact('mass'));
    }

    public function print(MassInstance $mass)
    {
        $mass = $this->loadMassWithIntentions($mass);
        return view('admin.masses.intentions-print', compact('mass'));
    }

    public function pdf(MassInstance $mass)
    {
        $mass = $this->loadMassWithIntentions($mass);
        $pdf = Pdf::loadView('admin.masses.intentions-pdf', compact('mass'))
            ->setPaper('a4', 'portrait');
        $filename = 'intenciones-misa-'.$mass->starts_at->format('Ymd_His').'.pdf';
        return $pdf->stream($filename);
    }

    protected function loadMassWithIntentions(MassInstance $mass): MassInstance
    {
        $orderSql = "CASE "
            . "WHEN category = 'acciones_de_gracia' THEN 1 "
            . "WHEN category = 'peticiones' THEN 2 "
            . "WHEN category = 'difuntos' THEN 3 "
            . "ELSE 99 END";

        $mass->load([
            'priest',
            'intentions' => function ($query) use ($orderSql) {
                $query->with(['dedicatees', 'dedicatee', 'receipt'])
                    ->orderByRaw($orderSql)
                    ->orderBy('created_at');
            },
        ]);

        return $mass;
    }
}
