<div>
    @if($open)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-data="assignPriest({ results: @js($this->results), selectedId: @js($this->priestId), name: @entangle('newPriestName'), phone: @entangle('newPriestPhone'), email: @entangle('newPriestEmail'), editSelected: false })"
        x-init="$watch('q', value => search(value))">
        <div class="absolute inset-0 bg-black/40" wire:click="close"></div>
        <div class="relative w-full max-w-lg rounded-lg bg-white p-5 shadow-lg">
            <h3 class="text-lg font-semibold mb-3">Asignar/Editar sacerdote</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Buscar sacerdote</label>
                    <input type="text" x-model.debounce.250ms="q" placeholder="Nombre" class="mt-1 block w-full rounded border-gray-300" />
                    <template x-if="results.length">
                        <ul class="mt-2 max-h-40 overflow-auto rounded border border-gray-200 divide-y">
                            <template x-for="p in results" :key="p.id">
                                <li class="px-3 py-2 hover:bg-gray-50 flex items-center justify-between gap-2">
                                    <span class="truncate" x-text="p.name"></span>
                                    <div class="shrink-0 flex items-center gap-2">
                                        <button type="button" @click="select(p)" class="text-sm px-2 py-1 rounded bg-indigo-600 text-white">Seleccionar</button>
                                        <button type="button" @click="if(confirm('¿Eliminar este sacerdote?')) { $wire.deleteById(p.id); results = results.filter(r => r.id !== p.id) }" class="text-sm px-2 py-1 rounded bg-red-600 text-white">Eliminar</button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </template>
                </div>

                <div class="border-t pt-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Crear nuevo o editar seleccionado</label>
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="checkbox" x-model="editSelected" wire:model.live="editSelected" class="rounded border-gray-300">
                            <span>Editar seleccionado</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1">
                        <input type="text" x-model="name" placeholder="Nombre completo" class="rounded border-gray-300 sm:col-span-2" />
                        <input type="text" x-model="phone" placeholder="Teléfono" class="rounded border-gray-300" />
                        <input type="email" x-model="email" placeholder="Email" class="rounded border-gray-300" />
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Si crea uno nuevo, se ignorará el buscado. Active "Editar seleccionado" para modificar datos del existente.</p>
                </div>
            </div>

            <div class="mt-5 flex items-center justify-between gap-2">
                <div class="text-xs text-gray-500">
                    Al eliminar un sacerdote no se borran misas; las asignaciones quedan en blanco.
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" class="px-3 py-2 rounded bg-red-600/90 text-white hover:bg-red-700" :disabled="!selectedId" @click="if(confirm('¿Eliminar sacerdote seleccionado? Esta acción no borra sus misas.')) { $wire.deleteSelected(); results = results.filter(r => r.id !== selectedId); selectedId = null }">Eliminar</button>
                <button type="button" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300" wire:click="close">Cancelar</button>
                <button type="button" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" wire:click="save">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
    document.addEventListener('alpine:init', () => {
            Alpine.data('assignPriest', (state) => ({
                q: '',
                results: state.results || [],
        selectedId: state.selectedId,
                name: state.name,
                phone: state.phone,
                email: state.email,
        editSelected: state.editSelected || false,
                async search(value){
                    if(!value || value.trim().length < 2){ this.results = []; return; }
                    try {
                        const resp = await fetch(`/admin/priests/search?q=${encodeURIComponent(value)}`);
                        if(!resp.ok) return;
                        this.results = await resp.json();
                    } catch (_) {}
                },
                select(p){
                    // update Livewire and local fields
                    this.selectedId = p.id;
                    this.name = p.name || '';
                    this.phone = p.phone || '';
                    this.email = p.email || '';
                    this.results = [];
                    // call Livewire method to sync selection within this component
                    if (typeof $wire !== 'undefined' && $wire?.assignSelected) { $wire.assignSelected(p.id); }
                }
            }))
        })
    </script>
</div>
