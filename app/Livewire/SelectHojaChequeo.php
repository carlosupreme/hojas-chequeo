<?php

namespace App\Livewire;

use App\Area;
use App\Models\HojaChequeo;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public $selectedId;

    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    protected $queryString = ['search' => ['except' => '']];

    public ?Area $activeFilter = null;

    public User $user;

    public iterable $areas;

    public $chequeosPendientes;

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->areas = Area::cases();
        $this->chequeosPendientes = $this->user
            ->chequeosPendientes()
            ->whereHas('hojaChequeo', fn ($query) => $query->inArea($this->activeFilter)->search($this->search))
            ->with('hojaChequeo')
            ->get();
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
        $this->fetchHojas();
    }

    protected function resetPagination(): void
    {
        $this->page = 1;
    }

    public function selectHojaChequeo($id): void
    {
        $this->dispatch('hojaChequeoSelected', $id);
    }

    public function selectHojaEjecucion($chequeo): void
    {
        $this->dispatch('hojaChequeoSelected', $chequeo['hoja_chequeo_id']);
        $this->dispatch('hojaEjecucionSelected', $chequeo['id']);
    }

    public function render(): View
    {
        $cacheKey = $this->buildCacheKey();
        $cacheTtl = now()->addMinutes(15);

        $hojas = Cache::tags(['hojas', "user:{$this->user->id}"])->remember(
            $cacheKey,
            $cacheTtl,
            fn () => $this->fetchHojas()
        );

        $hasMore = $hojas->count() >= ($this->perPage * $this->page);

        return view('livewire.select-hoja-chequeo', [
            'hojas' => $hojas,
            'hasMore' => $hasMore,
            'turno' => $this->user->turno,
        ]);
    }

    protected function buildCacheKey(): string
    {
        $idsHash = md5(implode(',', $this->user->perfil->hoja_ids));
        $filter = $this->activeFilter?->value ?? 'all';
        $search = $this->search ? md5(strtolower($this->search)) : 'none';

        return "hojas:list:{$this->user->id}:{$idsHash}:{$filter}:{$search}:page{$this->page}";
    }

    protected function fetchHojas()
    {
        return HojaChequeo::with(['equipo', 'latestChequeoDiario'])
            ->select(['id', 'equipo_id', 'encendido'])
            ->availableTo($this->user->perfil)
            ->encendidas()
            ->inArea($this->activeFilter?->value)
            ->search($this->search)
            ->limit($this->perPage * $this->page)
            ->get();
    }
}
