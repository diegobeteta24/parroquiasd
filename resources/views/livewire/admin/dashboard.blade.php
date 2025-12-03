<div>
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de administración</h2>
        </div>
    </div>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <a href="{{ route('admin.mass-calendar') }}" class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Intenciones de misa ordinaria</h3>
                            <p class="mt-1 text-sm text-gray-600">Ir al calendario y listado para gestionar intenciones.</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-blue-50 text-blue-600">🕊️</span>
                    </div>
                </a>

                <a href="{{ route('admin.special-masses.index') }}" class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow transition focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Intenciones especiales</h3>
                            <p class="mt-1 text-sm text-gray-600">Apartar y administrar misas especiales.</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">⭐</span>
                    </div>
                </a>

                <a href="{{ route('admin.reports') }}" class="block rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow transition focus:outline-none focus:ring-2 focus:ring-amber-500">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Reportes</h3>
                            <p class="mt-1 text-sm text-gray-600">Libro del día, recaudación y pendientes.</p>
                        </div>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-amber-50 text-amber-600">📄</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
