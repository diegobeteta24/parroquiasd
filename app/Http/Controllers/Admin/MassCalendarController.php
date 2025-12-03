<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MassInstance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MassCalendarController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['start'=>'required|date','end'=>'required|date']);
        $tz = 'America/Guatemala';
        // Interpretar el rango como hora local de la app
        $start = Carbon::parse($request->get('start'), $tz);
        $end = Carbon::parse($request->get('end'), $tz);

        $instances = MassInstance::whereBetween('starts_at', [$start,$end])->orderBy('starts_at')->get();

        // Secretario/superadmin ven la vista de detalle; Padre va directo a PDF
        $user = $request->user();
        $isSecretary = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['Secretaria','superadmin']);

        $events = [];
        foreach ($instances as $m) {
            $color = '#22c55e';
            // Special handling for rosary: show in distinct color (rose-500) and label
            $isRosary = $m->is_special && $m->special_category === 'rosario';
            if ($isRosary) {
                $color = '#ef4444';
            } elseif ($m->status==='cancelled') { $color='#6b7280'; }
            elseif ($m->status==='celebrated') { $color='#334155'; }
            $startLocal = $m->starts_at->clone();
            $eventUrl = $isSecretary
                ? route('admin.masses.show', $m)
                : route('admin.masses.pdf', $m);
            $events[] = [
                'id'=>$m->id,
                'url'=>$eventUrl,
                'title'=> $isRosary ? ('Rosario ' . $startLocal->format('H:i')) : $startLocal->format('H:i'),
                // Enviar sin zona para que FullCalendar use exactamente timeZone configurado
                'start'=>$startLocal->format('Y-m-d\TH:i:s'),
                'end'=>$startLocal->clone()->addHour()->format('Y-m-d\TH:i:s'),
                'backgroundColor'=>$color,
                'borderColor'=>$color,
                'textColor'=>'#ffffff',
                'extendedProps' => [
                    'is_special' => (bool)($m->is_special ?? false),
                    'special_category' => $m->special_category ?? null,
                    'status' => $m->status ?? null,
                ],
            ];
        }
        return response()->json($events);
    }
}
