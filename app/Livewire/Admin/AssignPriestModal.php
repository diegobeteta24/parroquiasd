<?php

namespace App\Livewire\Admin;

use App\Models\MassInstance;
use App\Models\Priest;
use Livewire\Attributes\On;
use Livewire\Component;

class AssignPriestModal extends Component
{
    public MassInstance $mass;
    public bool $open = false;
    public string $search = '';

    // Selection + edit/create fields
    public ?int $priestId = null;
    public bool $editSelected = false;
    public string $newPriestName = '';
    public ?string $newPriestPhone = null;
    public ?string $newPriestEmail = null;

    public function mount(MassInstance $mass): void
    {
        $this->mass = $mass;
        $this->priestId = $mass->priest_id;
    }

    #[On('open-assign-priest')]
    public function open(): void
    {
        // Pre-fill current assigned priest info for quick edits
        if ($this->mass->priest_id && ($p = Priest::find($this->mass->priest_id))) {
            $this->priestId = $p->id;
            $this->newPriestName = $p->name ?? '';
            $this->newPriestPhone = $p->phone;
            $this->newPriestEmail = $p->email;
        }
        $this->open = true;
    }
    public function close(): void { $this->open = false; }

    public function getResultsProperty()
    {
        if (trim($this->search) === '') return collect();
        return Priest::query()->where('name','like','%'.trim($this->search).'%')->orderBy('name')->limit(10)->get();
    }

    public function assignSelected(int $id): void
    {
        $this->priestId = $id;
        if ($priest = Priest::find($id)) {
            $this->newPriestName = $priest->name ?? '';
            $this->newPriestPhone = $priest->phone;
            $this->newPriestEmail = $priest->email;
        }
    }

    public function save(): void
    {
        $priestName = null;

        // Update existing if selected and edit toggled
        if ($this->priestId && $this->editSelected) {
            $this->validate([
                'newPriestName' => 'required|string|min:3',
            ]);
            $priest = Priest::find($this->priestId);
            if ($priest) {
                $priest->fill([
                    'name' => trim($this->newPriestName),
                    'phone' => $this->newPriestPhone,
                    'email' => $this->newPriestEmail,
                ])->save();
                $priestName = $priest->name;
            }
        }

        // Or create/reuse by name if none selected and name provided
        if (!$this->priestId && trim($this->newPriestName) !== '') {
            $name = trim($this->newPriestName);
            $normalized = mb_strtolower($name);
            $existing = Priest::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
                ->first();
            if ($existing) {
                $this->priestId = $existing->id;
                $priestName = $existing->name;
                // If user marked editSelected, allow updating existing found by name
                if ($this->editSelected) {
                    $existing->fill([
                        'name' => $name, // keep exact case user entered
                        'phone' => $this->newPriestPhone,
                        'email' => $this->newPriestEmail,
                    ])->save();
                    $priestName = $existing->name;
                }
            } else {
                $priest = Priest::create([
                    'name' => $name,
                    'phone' => $this->newPriestPhone,
                    'email' => $this->newPriestEmail,
                ]);
                $this->priestId = $priest->id;
                $priestName = $priest->name;
            }
        }

        $this->validate([
            'priestId' => 'nullable|exists:priests,id',
        ]);

        $this->mass->priest_id = $this->priestId;
        $this->mass->save();

        $priestName = $priestName ?? $this->mass->priest?->name;
        $this->dispatch('notify', 'Sacerdote asignado');
        $this->close();
        $this->dispatch('priest-updated', name: (string)($priestName ?? ''));
        $this->dispatch('$refresh');
    }

    public function deleteSelected(): void
    {
        if (!$this->priestId) return;
        $priest = Priest::find($this->priestId);
        if (!$priest) return;

    $id = $priest->id;
    $name = $priest->name;
    $priest->delete(); // FK on mass_instances is nullOnDelete -> no masses are deleted

        // Refresh mass and clear local selection/fields
        $this->mass->refresh();
        $this->priestId = null;
        $this->newPriestName = '';
        $this->newPriestPhone = null;
        $this->newPriestEmail = null;

        $this->dispatch('notify', "Sacerdote eliminado");
        $this->dispatch('priest-deleted', id: $id);
        $this->dispatch('priest-updated', name: '');
        $this->dispatch('$refresh');
    }

    public function deleteById(int $id): void
    {
        $priest = Priest::find($id);
        if (!$priest) return;
    $id = $priest->id;
    $priest->delete();
        if ($this->priestId === $id) {
            $this->priestId = null;
            $this->newPriestName = '';
            $this->newPriestPhone = null;
            $this->newPriestEmail = null;
            $this->mass->refresh();
            $this->dispatch('priest-updated', name: '');
        }
        $this->dispatch('notify', 'Sacerdote eliminado');
        $this->dispatch('priest-deleted', id: $id);
        $this->dispatch('$refresh');
    }

    public function render()
    {
        // Reuse the admin partial which contains the Alpine-powered modal
        return view('livewire.admin.assign-priest-modal');
    }
}
