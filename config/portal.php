<?php

return [
    'login_enabled' => env('PORTAL_LOGIN_ENABLED', true),
    'intentions_enabled' => env('INTENTIONS_ENABLED', true),
    'intentions_max_repetitions' => (int) env('INTENTIONS_MAX_REPETITIONS', 1000),
    'intentions_admin_ignore_capacity' => env('INTENTIONS_ADMIN_IGNORE_CAPACITY', true),

    'parish' => [
        'name' => 'Parroquia Santo Domingo',
        'full_name' => 'Basílica de Nuestra Señora del Rosario — Parroquia Santo Domingo',
        'short_name' => 'Basílica del Rosario',
        'address' => [
            'street' => '12 Avenida 10-09',
            'zone' => 'Zona 1',
            'city' => 'Ciudad de Guatemala',
            'region' => 'Guatemala',
            'postal_code' => '01001',
            'country_code' => 'GT',
            'country' => 'Guatemala',
            'geo' => [
                'lat' => 14.63764097616705,
                'lng' => -90.50953182409528,
            ],
        ],
        'contact' => [
            'phone' => '+502 2502-2727',
            'phone_display' => '+502 2502-2727',
            'phone_link' => 'tel:+50225022727',
            'whatsapp' => '+502 5628-0420',
            'whatsapp_display' => '+502 5628-0420',
            'whatsapp_link' => 'https://wa.me/50256280420',
            'email' => 'parroquiasantodomingo.op@gmail.com',
            'maps_url' => 'https://goo.gl/maps/1YzdUNk9bWpY3qFr5',
            'maps_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.3222331864486!2d-90.50953182409528!3d14.63764097616705!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8589a26ad56b93cb%3A0xb4a1e4eff74bcc34!2sBas%C3%ADlica%20de%20Nuestra%20Se%C3%B1ora%20del%20Rosario!5e0!3m2!1ses-419!2sgt!4v1757519597424!5m2!1ses-419!2sgt',
        ],
        'office_hours' => [
            'weekdays' => 'Lun–Vie 9:00 a 13:00 y 14:00 a 18:00',
            'saturday' => 'Sáb 9:00 a 13:00',
            'notes' => 'Trámites sacramentales e intenciones se atienden en despacho parroquial.',
        ],
        'schedules' => [
            'weekday_masses' => '7:00 · 12:00 · 18:30',
            'saturday_mass' => '18:30',
            'sunday_masses' => '6:30 · 8:00 · 12:00 · 16:30 · 18:30',
            'rosary' => 'Diario 18:00',
            'holy_hour' => 'Jueves 19:30 – 20:30',
            'confessions_week' => 'Mar–Vie 15:00 – 17:00',
            'confessions_sunday' => 'Dom 9:30 – 11:30',
        ],
        'social' => [
            'facebook' => null,
            'instagram' => null,
            'youtube' => null,
        ],
    ],

    'hero' => [
        'fallback_remote' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?q=80&w=1600&auto=format&fit=crop',
        'primary' => ['images/hero.webp', 'images/hero.jpg'],
        'secondary' => ['images/banner.webp', 'images/banner.jpg'],
        'seasonal' => [
            'rosary_month' => [
                'months' => [10],
                'candidates' => ['images/octubre-hero.webp', 'images/octubre-hero.jpg', 'images/octubre.webp', 'images/octubre.jpg'],
                'gallery_glob' => 'images/octubre*.*',
            ],
        ],
    ],
];
