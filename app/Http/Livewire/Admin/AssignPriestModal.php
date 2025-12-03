<?php

namespace App\Http\Livewire\Admin;

use App\Models\MassInstance;
use App\Models\Priest;
use Livewire\Attributes\On;
use Livewire\Component;

class AssignPriestModal extends Component
{
    public MassInstance $mass;
    public string $search = '';
    public ?int $priestId = null;
    public string $newPriestName = '';
    public ?string $newPriestPhone = null;
    public ?string $newPriestEmail = null;
    public bool $open = false;
    public bool $editSelected = false; // allow editing existing priest data

    #[On('open-assign-priest')]
    public function open(): void {
        // Prefill fields with current assigned priest if any
        if ($this->mass->priest_id && ($p = Priest::find($this->mass->priest_id))) {
            $this->priestId = $p->id;
            $this->newPriestName = $p->name ?? '';
            $this->newPriestPhone = $p->phone;
            $this->newPriestEmail = $p->email;
        }
        $this->open = true;
    }
    public function close(): void { $this->open = false; }

    public function mount(MassInstance $mass): void
    {
        $this->mass = $mass;
        $this->priestId = $mass->priest_id;
    }

    public function getResultsProperty()
    {
        if (trim($this->search) === '') return collect();
        return Priest::query()->where('name','like','%'.trim($this->search).'%')->orderBy('name')->limit(10)->get();
    }

    public function assignSelected(int $id): void
    {
        $this->priestId = $id;
        // Prefill edit fields from selected priest so user can correct data if needed
        if ($priest = Priest::find($id)) {
            $this->newPriestName = $priest->name ?? '';
            $this->newPriestPhone = $priest->phone;
            $this->newPriestEmail = $priest->email;
        }
    }

    public function save(): void
    {
        $priestName = null;

        // If an existing priest is selected and user opted to edit, update it
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

        // Or create new if none selected and name provided
        if (!$this->priestId && trim($this->newPriestName) !== '') {
            $priest = Priest::create([
                'name' => trim($this->newPriestName),
                'phone' => $this->newPriestPhone,
                'email' => $this->newPriestEmail,
            ]);
            $this->priestId = $priest->id;
            $priestName = $priest->name;
        }

        $this->validate([
            'priestId' => 'nullable|exists:priests,id',
        ]);

        $this->mass->priest_id = $this->priestId;
        $this->mass->save();
        // Prefer the selected/created/edit name; fallback to relation
        $priestName = $priestName ?? $this->mass->priest?->name;
        $this->dispatch('notify', 'Sacerdote asignado');
        $this->close();
        // Inform outer page (Alpine) to update the displayed priest name instantly
        $this->dispatch('priest-updated', name: (string)($priestName ?? ''));
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('admin.masses.assign-priest-modal');
    }
}
