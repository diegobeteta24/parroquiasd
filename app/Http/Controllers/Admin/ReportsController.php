<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Intention;
use App\Models\MassInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function income(Request $request)
    {
        $tz = config('app.timezone');
        $from = $request->input('from');
        $to = $request->input('to');
        $defaultFrom = now()->timezone($tz)->startOfMonth()->toDateString();
        $defaultTo = now()->timezone($tz)->toDateString();
        $from = $from ?: $defaultFrom;
        $to = $to ?: $defaultTo;

        // Normalize range (inclusive)
        $fromAt = Carbon::parse($from, $tz)->startOfDay();
        $toAt = Carbon::parse($to, $tz)->endOfDay();

        $includePrepaid = (bool) $request->boolean('include_prepaid');
        $type = $request->input('type'); // optional: rezada, cantada, rosario
        $payment = $request->input('payment_method'); // optional: cash, transfer, card

        // Intentions breakdown by type
        $intentions = Intention::query()
            ->whereBetween('created_at', [$fromAt, $toAt])
            ->when(!$includePrepaid, function ($q) {
                $q->where(function ($inner) {
                    $inner->where('is_prepaid', false)
                        ->orWhere('payment_method', 'recurrente');
                });
            })
            ->when($type, fn($q) => $q->where('type', $type))
            ->when($payment, fn($q) => $q->where('payment_method', $payment))
            ->whereNull('deleted_at');

        $byType = $intentions->clone()
            ->selectRaw('type, COUNT(*) as cnt, SUM(COALESCE(stipend_amount_gtq, amount)) as total')
            ->groupBy('type')
            ->orderBy('type')
            ->get();
        $sumIntentions = (float) ($byType->sum('total') ?? 0);

        // Special masses reservation amounts by category (exclude rosary)
        $specials = MassInstance::query()
            ->where('is_special', true)
            ->where('special_category', '<>', 'rosario')
            ->whereNotNull('reservation_amount')
            ->whereBetween('created_at', [$fromAt, $toAt]);
        $byCategory = $specials->clone()
            ->selectRaw('special_category, COUNT(*) as cnt, SUM(reservation_amount) as total')
            ->groupBy('special_category')
            ->orderBy('special_category')
            ->get();
        $sumSpecials = (float) ($byCategory->sum('total') ?? 0);

        return view('admin.reports.income', [
            'from' => $fromAt->toDateString(),
            'to' => $toAt->toDateString(),
            'includePrepaid' => $includePrepaid,
            'type' => $type,
            'payment' => $payment,
            'byType' => $byType,
            'sumIntentions' => $sumIntentions,
            'byCategory' => $byCategory,
            'sumSpecials' => $sumSpecials,
        ]);
    }

    // Listado detallado de intenciones por mes, con filtro para ordinarias (no especiales)
    public function intentionsList(Request $request)
    {
        $tz = config('app.timezone');
        $year = (int)($request->input('year') ?: now($tz)->year);
        $month = (int)($request->input('month') ?: now($tz)->month);
        $dateField = $request->input('date_field', 'created'); // created | mass
        $onlyOrdinary = $request->boolean('only_ordinary', true);
        $format = $request->input('format'); // csv | json

        $fromAt = Carbon::create($year, $month, 1, 0, 0, 0, $tz)->startOfMonth();
        $toAt = (clone $fromAt)->endOfMonth();

        $query = Intention::query()
            ->with(['mass'])
            ->when($onlyOrdinary, function ($q) {
                $q->whereHas('mass', fn($mq) => $mq->where('is_special', false));
                $q->whereIn('type', ['rezada','cantada']);
            })
            ->when($dateField === 'mass', fn($q) => $q->whereHas('mass', fn($mq) => $mq->whereBetween('starts_at', [$fromAt, $toAt])),
                fn($q) => $q->whereBetween('created_at', [$fromAt, $toAt])
            )
            ->whereNull('deleted_at')
            ->orderBy('created_at');

        $rows = $query->get();

        if ($format === 'json') {
            return response()->json($rows);
        }

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="intenciones-'.$year.'-'.str_pad((string)$month,2,'0',STR_PAD_LEFT).'.csv"',
            ];
            $callback = function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['ID','Creada','Fecha misa','Hora misa','Tipo','Donante','Monto','Metodo','Estado']);
                foreach ($rows as $r) {
                    fputcsv($out, [
                        $r->id,
                        optional($r->created_at)->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                        optional($r->mass?->starts_at)->format('Y-m-d'),
                        optional($r->mass?->starts_at)->format('H:i'),
                        $r->type,
                        $r->donor_name,
                        number_format((float)($r->stipend_amount_gtq ?? $r->amount), 2, '.', ''),
                        $r->payment_method,
                        $r->status,
                    ]);
                }
                fclose($out);
            };
            return response()->stream($callback, 200, $headers);
        }

        // Totales auxiliares
        $count = $rows->count();
        $sum = (float) $rows->sum(fn($r) => (float)($r->stipend_amount_gtq ?? $r->amount));

        return view('admin.reports.intentions-list', [
            'rows' => $rows,
            'year' => $year,
            'month' => $month,
            'dateField' => $dateField,
            'onlyOrdinary' => $onlyOrdinary,
            'count' => $count,
            'sum' => $sum,
            'from' => $fromAt,
            'to' => $toAt,
        ]);
    }
}
