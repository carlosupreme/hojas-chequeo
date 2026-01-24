<?php

namespace App\Livewire;

use App\Area;
use App\Models\HojaChequeo;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    public ?Area $activeFilter = null;

    protected $queryString = ['search' => ['except' => '']];

    public function mount(): void
    {
        // No heavy lifting here anymore to ensure filters apply dynamically
    }

    public function toggleFilter(?string $filter = null): void
    {
        $area = null;

        if (! is_null($filter) && $filter !== '') {
            $area = Area::tryFrom($filter);
        }

        $this->activeFilter = ($this->activeFilter === $area) ? null : $area;
        $this->resetPagination();
    }

    public function updatedSearch(): void
    {
        $this->resetPagination();
    }

    public function loadMore(): void
    {
        $this->page++;
    }

    protected function resetPagination(): void
    {
        $this->page = 1;
    }

    public function selectHojaChequeo($id): void
    {
        $this->dispatch('hojaChequeoSelected', $id);
    }

    public function selectHojaEjecucion($chequeoId): void
    {
        // Assuming you need the Chequeo ID to continue
        $this->dispatch('hojaEjecucionSelected', $chequeoId);
    }

    public function render(): View
    {
        $user = Auth::user();

        // 1. Shared Filter Logic (Closure)
        // This ensures the exact same logic applies to all 3 lists
        $applyFilters = function (Builder $query) {
            $query->whereHas('hojaChequeo', function ($q) {
                $q->inArea($this->activeFilter?->value)
                    ->search($this->search);
            });
        };

        // 2. Fetch Pending (Filtered)
        $chequeosPendientes = $user->chequeosPendientes()
            ->tap($applyFilters)
            ->with(['hojaChequeo.equipo'])
            ->get();

        // 3. Fetch Completed Today (Filtered)
        // We use the relation query but add our filters before getting results
        $chequeosCompletados = $user->chequeosCompletadosHoy()
            ->tap($applyFilters)
            ->with(['hojaChequeo.equipo'])
            ->get();

        $hojas = $this->fetchHojas($user);

        $hasMore = $hojas->count() >= ($this->perPage * $this->page);

        return view('livewire.select-hoja-chequeo', [
            'hojas' => $hojas,
            'chequeosPendientes' => $chequeosPendientes,
            'chequeosCompletados' => $chequeosCompletados,
            'hasMore' => $hasMore,
            'areas' => Area::cases(),
            'user' => $user,
            'turno' => $user->turno,
        ]);
    }

    protected function buildCacheKey(int $userId): string
    {
        // Added userId to params to ensure purity if method is moved later
        $user = User::find($userId);
        $idsHash = md5(implode(',', $user->perfil->hoja_ids ?? []));
        $filter = $this->activeFilter?->value ?? 'all';
        $search = $this->search ? md5(strtolower($this->search)) : 'none';

        return "hojas:list:{$userId}:{$idsHash}:{$filter}:{$search}:page{$this->page}";
    }

    protected function fetchHojas(User $user)
    {
        return HojaChequeo::with(['equipo', 'latestChequeoDiario'])
            ->select(['id', 'equipo_id', 'encendido', 'version'])
            ->availableTo($user->perfil)
            ->encendidas()
            ->inArea($this->activeFilter?->value)
            ->search($this->search)
            ->limit($this->perPage * $this->page)
            ->get();
    }
}
