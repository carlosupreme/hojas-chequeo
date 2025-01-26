<?php

namespace App\Livewire;

use App\Models\HojaChequeo;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public $selectedId;
    public string $search = "";

    protected $queryString = ['search' => ['except' => '']];

    public function selectEquipo($id) {
        $this->dispatch('checkSheetSelected', $id);
    }

    public function render() {
        $hojas = HojaChequeo::with('equipo', 'chequeosDiarios')
                            ->whereActive(true)
                            ->whereHas('equipo', function ($query) {
                                $query->where(function ($subQuery) {
                                    $subQuery->whereRaw('LOWER(nombre) LIKE ?', ['%' . strtolower($this->search) . '%'])
                                             ->orWhereRaw('LOWER(tag) LIKE ?', ['%' . strtolower($this->search) . '%'])
                                             ->orWhereRaw('LOWER(area) LIKE ?', ['%' . strtolower($this->search) . '%']);
                                });
                            })->get();

        return view('livewire.select-hoja-chequeo', compact('hojas'));
    }
}
