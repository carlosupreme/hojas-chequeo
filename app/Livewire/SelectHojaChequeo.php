<?php

namespace App\Livewire;

use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public $selectedId;

    public string $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public ?HojaChequeoArea $activeFilter = null;

    public function toggleFilter(HojaChequeoArea $filter): void
    {
        $this->activeFilter = ($this->activeFilter === $filter) ? null : $filter;
    }

    public function selectEquipo($id)
    {
        $this->dispatch('checkSheetSelected', $id);
    }

    public function render()
    {
        $availableIds = auth()->user()->perfil->hoja_ids;

        $hojas = HojaChequeo::with([
            'equipo:id,nombre,tag,area,foto',
            'latestChequeoDiario',
        ])
            ->select(['id', 'equipo_id', 'area', 'active'])
            ->active()
            ->whereIn('id', $availableIds)
            ->when($this->activeFilter, fn ($q) => $q->where('area', $this->activeFilter->value))
            ->when($this->search, function ($q) {
                $term = strtolower($this->search);
                $q->whereHas('equipo', fn ($q2) => $q2->where(function ($sub) use ($term) {
                    $sub->whereRaw('LOWER(nombre) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(tag) LIKE ?', ["%{$term}%"])
                        ->orWhereRaw('LOWER(area) LIKE ?', ["%{$term}%"]);
                })
                );
            })
            ->limit(50)
            ->get();

        return view('livewire.select-hoja-chequeo', ['hojas' => $hojas]);
    }
}
