<?php

namespace Database\Seeders;

use App\Models\Intention;
use App\Models\MassInstance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class December2025ReportSeeder extends Seeder
{
    public function run(): void
    {
        $tz = config('app.timezone', 'America/Guatemala');
        $year = 2025;

        $period = CarbonPeriod::create(
            Carbon::create($year, 12, 1, 0, 0, 0, $tz)->startOfDay(),
            Carbon::create($year, 12, 31, 23, 59, 59, $tz)->endOfDay()
        );

        DB::transaction(function () use ($period, $tz) {
            $sequence = 1;
            foreach ($period as $date) {
                $masses = [
                    'morning' => $this->ensureMass($date, '07:00', false, null, 18),
                    'evening' => $this->ensureMass($date, '18:30', false, null, 20),
                    'rosary' => $this->ensureMass($date, '05:30', true, 'rosario', 0),
                ];

                $intentionSpecs = [
                    [
                        'type' => 'rezada',
                        'category' => 'acciones_de_gracia',
                        'amount' => 50.00,
                        'payment_method' => $date->day % 2 === 0 ? 'cash' : 'transfer',
                        'channel' => 'counter',
                        'mass_key' => 'morning',
                    ],
                    [
                        'type' => 'cantada',
                        'category' => 'peticiones',
                        'amount' => 150.00,
                        'payment_method' => 'card',
                        'channel' => 'counter',
                        'mass_key' => 'evening',
                    ],
                    [
                        'type' => 'rosario',
                        'category' => 'difuntos',
                        'amount' => 30.00,
                        'payment_method' => 'recurrente',
                        'channel' => 'web',
                        'mass_key' => 'rosary',
                    ],
                ];

                foreach ($intentionSpecs as $index => $spec) {
                    /** @var MassInstance $mass */
                    $mass = $masses[$spec['mass_key']];
                    $createdAt = $date->copy()->setTime(8 + ($index * 4), 15, 0);

                    Intention::create([
                        'mass_instance_id' => $mass->id,
                        'type' => $spec['type'],
                        'category' => $spec['category'],
                        'public_text' => sprintf(
                            'Intención %s del %s',
                            $spec['type'],
                            $date->isoFormat('D [de] MMMM')
                        ),
                        'donor_name' => sprintf('Familia %02d %s', $date->day, ucfirst($spec['type'])),
                        'phone' => '+5025555' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                        'email' => sprintf('donante%03d@example.com', $sequence),
                        'amount' => $spec['amount'],
                        'stipend_amount_gtq' => $spec['amount'],
                        'payment_method' => $spec['payment_method'],
                        'payment_ref' => $spec['payment_method'] === 'cash' ? null : 'REF-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
                        'amount_in_cents' => (int) round($spec['amount'] * 100),
                        'currency' => 'GTQ',
                        'metadata' => [
                            'source' => 'december-2025-report',
                            'day' => $date->toDateString(),
                            'sequence' => $sequence,
                        ],
                        'status' => 'paid',
                        'paid_at' => $createdAt,
                        'channel' => $spec['channel'],
                        'is_prepaid' => false,
                        'hold_expires_at' => null,
                        'code' => 'DEC' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                        'group_code' => null,
                        'payment_intent_id' => $spec['payment_method'] === 'recurrente'
                            ? 'pi_december_' . $date->format('Ymd') . '_' . ($index + 1)
                            : null,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    $sequence++;
                }

                foreach ($masses as $mass) {
                    $mass->occupied = $mass->intentions()->count();
                    $mass->save();
                }
            }
        });
    }

    private function ensureMass(Carbon $date, string $time, bool $isSpecial, ?string $specialCategory, int $capacity): MassInstance
    {
        $startsAt = Carbon::parse($date->toDateString() . ' ' . $time, $date->timezone ?? config('app.timezone'));

        return MassInstance::firstOrCreate(
            [
                'starts_at' => $startsAt,
                'is_special' => $isSpecial,
                'special_category' => $specialCategory,
            ],
            [
                'capacity' => $capacity,
                'occupied' => 0,
                'status' => 'scheduled',
                'source' => 'override',
                'notes' => 'Generado para el reporte de diciembre 2025',
            ]
        );
    }
}
