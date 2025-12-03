@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
        Reportes
    </h2>
@endsection

@section('content')
    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-4 overflow-x-auto whitespace-nowrap -mx-2 px-2" aria-label="Tabs">
                            <a href="{{ route('admin.mass-calendar') }}" class="shrink-0 px-3 py-2 text-sm font-medium rounded-t-md text-blue-600 hover:text-blue-800 dark:text-blue-400">Calendario</a>
                            <a href="{{ route('admin.reports') }}" class="shrink-0 px-3 py-2 text-sm font-medium rounded-t-md bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100">Reportes</a>
                        </nav>
                    </div>
                    <h1 class="text-xl font-semibold mb-4">Reporte mensual de intenciones</h1>

                    <form method="GET" action="{{ route('reports.monthly') }}" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Año</label>
                                <select name="year" class="mt-1 block w-full border rounded p-2 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                    @foreach(range(now()->year - 3, now()->year + 1) as $y)
                                        <option value="{{ $y }}" @selected($y === now()->year)>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Mes</label>
                                <select name="month" class="mt-1 block w-full border rounded p-2 bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                                    @foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $m => $label)
                                        <option value="{{ $m }}" @selected($m === now()->month)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Descargar PDF</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
