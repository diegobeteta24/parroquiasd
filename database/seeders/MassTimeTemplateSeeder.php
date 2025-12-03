<?php

namespace Database\Seeders;

use App\Models\MassTimeTemplate;
use Illuminate\Database\Seeder;

class MassTimeTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $records = [];
        foreach (range(1,5) as $dow) {
            foreach (['07:00','12:00','18:30'] as $time) { $records[] = compact('dow','time'); }
        }
        $records[] = ['dow'=>6,'time'=>'18:30'];
        foreach (['06:30','08:00','12:00','16:30','18:30'] as $time) { $records[] = ['dow'=>7,'time'=>$time]; }
        foreach ($records as $r) {
            MassTimeTemplate::firstOrCreate(['dow'=>$r['dow'],'time'=>$r['time']], [
                'capacity'=>10,'active'=>true
            ]);
        }
    }
}
