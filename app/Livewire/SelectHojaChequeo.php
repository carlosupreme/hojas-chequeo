<?php

namespace App\Livewire;

use App\HojaChequeoArea;
use App\Models\HojaChequeo;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public $selectedId;

    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    protected $queryString = ['search' => ['except' => '']];

    public ?HojaChequeoArea $activeFilter = null;

    public function toggleFilter(HojaChequeoArea $filter): void
    {
        $this->activeFilter = ($this->activeFilter === $filter) ? null : $filter;
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

    public function selectEquipo($id)
    {
        $this->dispatch('checkSheetSelected', $id);
    }

    public function render()
    {
        $userId = auth()->id();
        $availableIds = auth()->user()->perfil->hoja_ids;

        $cacheKey = $this->buildCacheKey($userId, $availableIds);
        $cacheTtl = now()->addMinutes(15);

        $hojas = Cache::tags(['hojas', "user:{$userId}"])->remember(
            $cacheKey,
            $cacheTtl,
            fn () => $this->fetchHojas($availableIds)
        );

        $hasMore = $hojas->count() >= ($this->perPage * $this->page);

        return view('livewire.select-hoja-chequeo', [
            'hojas' => $hojas,
            'hasMore' => $hasMore,
        ]);
    }

    protected function buildCacheKey(int $userId, array $availableIds): string
    {
        $idsHash = md5(implode(',', $availableIds));
        $filter = $this->activeFilter?->value ?? 'all';
        $search = $this->search ? md5(strtolower($this->search)) : 'none';

        return "hojas:list:{$userId}:{$idsHash}:{$filter}:{$search}:page{$this->page}";
    }

    protected function fetchHojas(array $availableIds)
    {
        return HojaChequeo::with([
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
            ->limit($this->perPage * $this->page)
            ->get();
    }
}
