@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
        Calendario de misas
    </h2>
@endsection

@section('content')
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-4 overflow-x-auto whitespace-nowrap -mx-2 px-2" aria-label="Tabs">
                            <a href="{{ route('admin.mass-calendar') }}" class="shrink-0 px-3 py-2 text-sm font-medium rounded-t-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">Calendario</a>
                            @role('Secretaria|superadmin')
                                <a href="{{ route('admin.reports') }}" class="shrink-0 px-3 py-2 text-sm font-medium rounded-t-md text-blue-600 hover:text-blue-800 dark:text-blue-400">Reportes</a>
                            @endrole
                        </nav>
                    </div>
                                <style>
                                    /* Asegura buena legibilidad de los encabezados de días */
                                    .fc .fc-col-header-cell-cushion {
                                        text-transform: none; /* no forzar mayúsculas */
                                        white-space: nowrap;  /* evita cortes raros */
                                        font-weight: 600;
                                        color: #111827; /* slate-900 */
                                    }
                                    .fc .fc-col-header, .fc .fc-col-header-cell {
                                        background: #f9fafb; /* bg-gray-50 */
                                    }
                                    .dark .fc .fc-col-header-cell-cushion { color: #f3f4f6; }
                                    .dark .fc .fc-col-header, .dark .fc .fc-col-header-cell { background: #1f2937; }
                                </style>
                                <div id="mass-calendar" class="min-h-[700px]"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.__massCalendar) return;

            const el = document.getElementById('mass-calendar');
            if (!el) return;

            const ensureCss = (href) => {
                if (!document.querySelector(`link[rel="stylesheet"][href="${href}"]`)) {
                    const l = document.createElement('link');
                    l.rel = 'stylesheet';
                    l.href = href;
                    document.head.appendChild(l);
                }
            };

            const loadScript = (src) => new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = src;
                s.async = true;
                s.onload = () => resolve();
                s.onerror = () => reject(new Error('No se pudo cargar ' + src));
                document.head.appendChild(s);
            });

            const load = async () => {
                try {
                    ensureCss('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/main.min.css');
                    if (!window.FullCalendar) {
                        await loadScript('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.19/index.global.min.js');
                    }

                    const Calendar = window.FullCalendar?.Calendar || window.Calendar;
                    if (!Calendar) {
                        console.error('FullCalendar no se cargó correctamente');
                        return;
                    }

                    const isMobile = () => window.matchMedia('(max-width: 640px)').matches; // Tailwind sm

                    const getOptions = () => ({
                        initialView: isMobile() ? 'timeGridDay' : 'timeGridWeek',
                        // Ancla la semana y el "hoy" al servidor (zona horaria de la app)
                        initialDate: '{{ now(config('app.timezone'))->toDateString() }}',
                        height: 'auto',
                        contentHeight: 'auto',
                        expandRows: true,
                        locale: 'es',
                        timeZone: 'America/Guatemala',
                        firstDay: 1,
                        // Oculta Domingo(0) y Sábado(6) explícitamente para evitar inconsistencias
                        hiddenDays: [0,6],
                        nowIndicator: true,
                        headerToolbar: isMobile()
                            ? { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridDay' }
                            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                        // Slots y formato más compactos en móvil
                        slotMinTime: '06:00:00',
                        slotMaxTime: '20:30:00',
                        slotDuration: '00:30:00',
                        // Encabezado usando el formateador interno de FullCalendar (evita desfaces de TZ)
                        dayHeaderFormat: { weekday: 'long' },
                        // No mostrar prefijo de hora, ya va en el título
                        displayEventTime: false,
                        events: {
                            url: '{{ route('admin.mass-events') }}',
                            method: 'GET',
                            failure: () => alert('No se pudieron cargar los eventos'),
                        },
                        eventClick: (info) => {
                            info.jsEvent?.preventDefault();
                            if (info.event.url) {
                                window.location.href = info.event.url;
                            }
                        },
                        eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
                    });

                    const calendar = new Calendar(el, getOptions());

                    const mq = window.matchMedia('(max-width: 640px)');
                    const applyResponsive = () => {
                        calendar.setOption('headerToolbar', isMobile()
                            ? { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridDay' }
                            : { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' }
                        );
                        calendar.changeView(isMobile() ? 'timeGridDay' : 'timeGridWeek');
                        // dayHeaderContent ya maneja adaptabilidad
                        calendar.updateSize();
                    };
                    mq.addEventListener?.('change', applyResponsive);
                    window.addEventListener('orientationchange', () => setTimeout(applyResponsive, 150));

                    calendar.render();
                    window.__massCalendar = calendar;
                    // Ajuste inicial
                    applyResponsive();
                } catch (e) {
                    console.error(e);
                }
            };

            load();
        });
    </script>
    @endpush

    @push('scripts')
    <style>
        :root {
            --fc-border-color: rgba(0,0,0,0.1);
        }
        .fc .fc-toolbar-title{ font-weight:600; }
        .fc-daygrid-event{ padding: 2px 4px; }
        @media (max-width: 640px) { /* móvil */
            #mass-calendar { min-height: 520px; }
            .fc .fc-toolbar.fc-header-toolbar { flex-wrap: wrap; gap: .5rem; }
            .fc .fc-toolbar-title { font-size: 1.125rem; }
            .fc .fc-button { padding: .25rem .5rem; font-size: .75rem; }
            .fc .fc-timegrid-slot-label { font-size: .75rem; }
            .fc .fc-event { font-size: .75rem; }
        }
    </style>
    @endpush

@endsection
