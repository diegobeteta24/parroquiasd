<?php

namespace App\Http\Controllers;

use App\Services\MonthlyIntentionStats;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function monthly(Request $request)
    {
        // Accept either ?year=&month= or ?data[year]=&data[month]= from Filament forms
        $year = (int) ($request->input('year') ?? $request->input('data.year'));
        $month = (int) ($request->input('month') ?? $request->input('data.month'));

        $validated = validator(
            ['year' => $year, 'month' => $month],
            ['year' => 'required|integer|min:2000|max:2100', 'month' => 'required|integer|min:1|max:12']
        )->validate();

        $stats = MonthlyIntentionStats::forMonth((int) $validated['year'], (int) $validated['month']);

        $pdf = Pdf::loadView('reports.monthly', [
            'stats' => $stats,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('reporte-intenciones-%04d-%02d.pdf', $stats['period']['year'], $stats['period']['month']);
        return $pdf->download($filename);
    }
}
