@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800">Nueva misa especial</h2>
@endsection

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('admin.special-masses.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fecha y hora</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="mt-1 block w-full rounded border-gray-300" required />
                        <p class="mt-1 text-xs text-gray-500">Solo sábados a las 08:30, 10:00, 12:00, 15:00 o 16:30.</p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Categoría</label>
                            <select name="special_category" class="mt-1 block w-full rounded border-gray-300" required @change="window.dispatchEvent(new CustomEvent('category-changed', { detail: $event.target.value }))">
                                @foreach(['bautismo','confirmacion','primera_comunion','matrimonio','otra'] as $cat)
                                    <option value="{{ $cat }}" @selected(old('special_category')===$cat)>{{ str_replace('_',' ', ucfirst($cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cupo</label>
                            <input type="number" min="1" max="200" name="capacity" value="{{ old('capacity', 20) }}" class="mt-1 block w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Monto de reserva (Q)</label>
                            <input type="number" step="0.01" min="0" name="reservation_amount" value="{{ old('reservation_amount') }}" class="mt-1 block w-full rounded border-gray-300" placeholder="Ej. 200.00" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div x-data="priestPicker('{{ route('admin.priests.search') }}')">
                            <label class="block text-sm font-medium text-gray-700">Padre asignado (opcional)</label>
                            <input type="hidden" name="priest_id" :value="selected?.id || ''">
                            <input type="text" x-model.debounce.250ms="q" placeholder="Buscar sacerdote" class="mt-1 block w-full rounded border-gray-300" />
                            <template x-if="results.length">
                                <ul class="mt-1 max-h-36 overflow-auto rounded border border-gray-200 divide-y">
                                    <template x-for="p in results" :key="p.id">
                                        <li class="px-3 py-2 hover:bg-gray-50 flex items-center justify-between gap-2">
                                            <span class="truncate" x-text="p.name"></span>
                                            <button type="button" @click="select(p)" class="text-xs px-2 py-1 rounded bg-indigo-600 text-white">Elegir</button>
                                        </li>
                                    </template>
                                </ul>
                            </template>
                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="text" name="new_priest_name" x-model="name" placeholder="Nombre nuevo/edición" class="rounded border-gray-300" />
                                <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="edit_selected" class="rounded border-gray-300"> Editar seleccionado</label>
                                <input type="text" name="new_priest_phone" x-model="phone" placeholder="Teléfono" class="rounded border-gray-300" />
                                <input type="email" name="new_priest_email" x-model="email" placeholder="Email" class="rounded border-gray-300" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Comentarios</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="mt-1 block w-full rounded border-gray-300" placeholder="Coro, procesión, requisitos, etc." />
                        </div>
                    </div>

                    <div x-data="specialDetails('{{ old('special_category', 'bautismo') }}')" x-init="init()">
                        <label class="block text-sm font-medium text-gray-700">Detalles del sacramento</label>
                        <div class="text-xs text-gray-500 mb-1">Se muestran campos según la categoría seleccionada.</div>
                        <div class="space-y-2">
                            <template x-if="category==='bautismo'">
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <input type="text" class="rounded border-gray-300" name="details[child_name]" placeholder="Nombre del niño/a" value="{{ old('details.child_name') }}" />
                                    <input type="date" class="rounded border-gray-300" name="details[birth_date]" value="{{ old('details.birth_date') }}" />
                                    <input type="text" class="rounded border-gray-300" name="details[father_name]" placeholder="Nombre del padre" value="{{ old('details.father_name') }}" />
                                    <input type="text" class="rounded border-gray-300" name="details[mother_name]" placeholder="Nombre de la madre" value="{{ old('details.mother_name') }}" />
                                    <input type="text" class="rounded border-gray-300 sm:col-span-2" name="details[godparents]" placeholder="Padrinos" value="{{ old('details.godparents') }}" />
                                </div>
                            </template>
                            <template x-if="category==='confirmacion'">
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <input type="text" class="rounded border-gray-300" name="details[candidate_name]" placeholder="Nombre del candidato" value="{{ old('details.candidate_name') }}" />
                                    <input type="text" class="rounded border-gray-300" name="details[sponsor]" placeholder="Padrino/Madrina" value="{{ old('details.sponsor') }}" />
                                    <input type="text" class="rounded border-gray-300 sm:col-span-2" name="details[parish]" placeholder="Parroquia" value="{{ old('details.parish') }}" />
                                </div>
                            </template>
                            <template x-if="category==='primera_comunion'">
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <input type="text" class="rounded border-gray-300" name="details[group]" placeholder="Grupo" value="{{ old('details.group') }}" />
                                    <input type="text" class="rounded border-gray-300" name="details[catechist]" placeholder="Catequista" value="{{ old('details.catechist') }}" />
                                    <input type="text" class="rounded border-gray-300 sm:col-span-2" name="details[school]" placeholder="Colegio/Escuela" value="{{ old('details.school') }}" />
                                </div>
                            </template>
                            <template x-if="category==='matrimonio'">
                                <div class="grid sm:grid-cols-2 gap-2">
                                    <input type="text" class="rounded border-gray-300" name="details[bride_name]" placeholder="Nombre de la novia" value="{{ old('details.bride_name') }}" />
                                    <input type="text" class="rounded border-gray-300" name="details[groom_name]" placeholder="Nombre del novio" value="{{ old('details.groom_name') }}" />
                                    <input type="text" class="rounded border-gray-300 sm:col-span-2" name="details[witnesses]" placeholder="Testigos" value="{{ old('details.witnesses') }}" />
                                    <label class="text-sm text-gray-700">Fecha matrimonio civil
                                        <input type="date" class="ml-2 rounded border-gray-300" name="details[civil_marriage_date]" value="{{ old('details.civil_marriage_date') }}" />
                                    </label>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('admin.special-masses.index') }}" class="px-4 py-2 rounded bg-gray-200">Cancelar</a>
                        <button type="submit" class="px-4 py-2 rounded bg-emerald-600 text-white">Crear</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function priestPicker(searchUrl, initial = null){
    return {
        q: '', results: [], selected: initial, name: initial?.name || '', phone: '', email: '',
        async search(){
            if(!this.q || this.q.trim().length < 2){ this.results = []; return; }
            try{ const resp = await fetch(`${searchUrl}?q=${encodeURIComponent(this.q)}`); if(resp.ok){ this.results = await resp.json(); } } catch(_){}
        },
        select(p){ this.selected = p; this.name = p.name || ''; this.q = p.name || ''; this.results = []; },
        init(){ this.$watch('q', () => this.search()); }
    }
}
function specialDetails(initialCategory){
    return {
        category: initialCategory || 'bautismo',
        init(){ window.addEventListener('category-changed', (e) => { this.category = e.detail || 'bautismo'; }); }
    }
}
</script>
@endpush
