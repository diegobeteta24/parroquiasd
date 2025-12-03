<?php

namespace App\Services;

use App\Models\Intention;
use Illuminate\Support\Carbon;

class MonthlyIntentionStats
{
    public static function forMonth(int $year, int $month): array
    {
        $from = Carbon::create($year, $month, 1, 0, 0, 0)->startOfMonth();
        $to = (clone $from)->endOfMonth();

        $base = Intention::query()->whereBetween('created_at', [$from, $to]);

        // Totals
        $totalIntentions = (clone $base)->count();

        // By status
        $byStatus = (clone $base)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        // Paid/celebrated sums (income).
        // Allow Recurrente to be contado aunque marque is_prepaid=true (porque ya vienen liquidados).
        $incomeStatuses = ['paid', 'celebrated'];
        $incomeScope = function ($query) {
            return $query
                ->whereNotNull('payment_method')
                ->where(function ($inner) {
                    $inner->where('is_prepaid', false)
                        ->orWhere('payment_method', 'recurrente');
                });
        };

        $paidAmount = (float) ($incomeScope((clone $base))
            ->selectRaw('SUM(COALESCE(stipend_amount_gtq, amount)) as s')
            ->value('s') ?? 0);

        $paidCount = (clone $base)
            ->whereIn('status', $incomeStatuses)
            ->count();

        // By payment method
        $byPaymentMethod = $incomeScope((clone $base))
            ->selectRaw('payment_method, SUM(COALESCE(stipend_amount_gtq, amount, 0)) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->payment_method => [
                    'count' => (int) $r->count,
                    'total' => (float) $r->total,
                ],
            ])
            ->all();

        $avgAmount = $totalIntentions > 0
            ? (float) (((clone $base)->selectRaw('AVG(COALESCE(stipend_amount_gtq, amount)) as a')->value('a')) ?? 0)
            : 0.0;

        // By type (income recorded via payment method). Separates each intention by its type.
        $byTypeIncome = $incomeScope((clone $base))
            ->selectRaw('type, COUNT(*) as count, SUM(COALESCE(stipend_amount_gtq, amount)) as total')
            ->groupBy('type')
            ->orderBy('type')
            ->get()
            ->mapWithKeys(fn ($r) => [
                $r->type => [
                    'count' => (int) $r->count,
                    'total' => (float) $r->total,
                ],
            ])
            ->all();

        $recurrenteStats = $incomeScope((clone $base))
            ->where('payment_method', 'recurrente')
            ->selectRaw('COUNT(*) as count, SUM(COALESCE(stipend_amount_gtq, amount)) as total')
            ->first();

        return [
            'period' => [
                'year' => $year,
                'month' => $month,
                'from' => $from,
                'to' => $to,
            ],
            'totals' => [
                'intentions' => $totalIntentions,
                'paid_count' => $paidCount,
                'paid_amount' => (float) $paidAmount,
                'average_amount' => $avgAmount,
            ],
            'by_status' => $byStatus,
            'by_payment_method' => $byPaymentMethod,
            'by_type_income' => $byTypeIncome,
            'recurrente' => [
                'count' => (int) ($recurrenteStats->count ?? 0),
                'total' => (float) ($recurrenteStats->total ?? 0),
            ],
        ];
    }
}
