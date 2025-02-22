<?php

namespace App\Livewire;

use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public        $selectedId;
    public string $search = "";

    protected $queryString = ['search' => ['except' => '']];

    public ?HojaChequeoArea $activeFilter = null;

    public function toggleFilter(HojaChequeoArea $filter): void {
        $this->activeFilter = ($this->activeFilter === $filter) ? null : $filter;
    }

    public function selectEquipo($id) {
        $this->dispatch('checkSheetSelected', $id);
    }

    public function render() {
        $availableIds = \Auth::user()->perfil->hoja_ids;
        $query = HojaChequeo::with('equipo', 'chequeosDiarios')
                            ->whereActive(true)
                            ->whereIn('id', $availableIds);

        if ($this->activeFilter) {
            $query = $query->where('area', $this->activeFilter->value);
        }

        $query = $query
            ->whereHas('equipo', function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%'])
                             ->orWhereRaw('LOWER(tag) LIKE ?', ['%' . strtolower($this->search) . '%'])
                             ->orWhereRaw('LOWER(area) LIKE ?', ['%' . strtolower($this->search) . '%']);
                });
            })->get();

        return view('livewire.select-hoja-chequeo', [
            'hojas' => $query
        ]);
    }
}
