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
        $availableIds = \Auth::user()->perfil->hoja_ids;

        $query = HojaChequeo::with(['equipo:id,nombre,tag,area'])
            ->whereActive(true)
            ->whereIn('id', $availableIds);

        if ($this->activeFilter) {
            $query = $query->where('area', $this->activeFilter->value);
        }

        if (! empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $query = $query->whereHas('equipo', function ($query) use ($searchTerm) {
                $query->where(function ($subQuery) use ($searchTerm) {
                    $subQuery->whereRaw('LOWER(nombre) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereRaw('LOWER(tag) LIKE ?', ["%{$searchTerm}%"])
                        ->orWhereRaw('LOWER(area) LIKE ?', ["%{$searchTerm}%"]);
                });
            });
        }

        $hojas = $query->select(['id', 'equipo_id', 'area', 'active'])
            ->limit(50)
            ->get();

        return view('livewire.select-hoja-chequeo', [
            'hojas' => $hojas,
        ]);
    }
}
