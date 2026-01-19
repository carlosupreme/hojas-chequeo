<?php

namespace App\Livewire;

use App\Area;
use App\Models\HojaChequeo;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class SelectHojaChequeo extends Component
{
    public $selectedId;

    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    public $activeUsers = [];

    protected $queryString = ['search' => ['except' => '']];

    public ?Area $activeFilter = null;

    public function mount(): void
    {
        // Initial load of active users for relevant sheets
        $availableIds = auth()->user()->perfil->hoja_ids ?? [];

        $idsToLoad = in_array('*', $availableIds)
            ? HojaChequeo::encendidas()->pluck('id')->toArray()
            : $availableIds;

        foreach ($idsToLoad as $id) {
            $users = Cache::get("hoja_presence_{$id}", []);
            if (! empty($users)) {
                $this->activeUsers[$id] = $users;
            }
        }
    }

    #[On('echo:hojas.global,HojaPresenceUpdated')]
    public function updatePresence($event)
    {
        Log::info($event);
        // $event is an array in Livewire listener
        $hojaId = $event['hojaId'];
        $userId = $event['userId'];
        $action = $event['action'];

        if (! isset($this->activeUsers[$hojaId])) {
            $this->activeUsers[$hojaId] = [];
        }

        if ($action === 'joined') {
            $this->activeUsers[$hojaId][$userId] = [
                'id' => $userId,
                'name' => $event['userName'],
                'avatar' => $event['userAvatar'],
            ];
        } elseif ($action === 'left') {
            unset($this->activeUsers[$hojaId][$userId]);
        }
    }

    public function toggleFilter(Area $filter): void
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

        // Refresh cache for newly loaded items (optional, but good for consistency)
        $hojas = $this->fetchHojas(auth()->user()->perfil->hoja_ids);
        foreach ($hojas as $hoja) {
            $users = Cache::get("hoja_presence_{$hoja->id}", []);
            if (! empty($users)) {
                $this->activeUsers[$hoja->id] = $users;
            }
        }
    }

    protected function resetPagination(): void
    {
        $this->page = 1;
    }

    public function selectEquipo($id)
    {
        $this->dispatch('checkSheetSelected', $id);
    }

    public function render(): View
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
            'turno' => Auth::user()->turno,
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
        return HojaChequeo::with(['equipo', 'latestChequeoDiario'])
            ->select(['id', 'equipo_id', 'encendido'])
            ->encendidas()
            ->availableTo($availableIds)
            ->inArea($this->activeFilter?->value)
            ->search($this->search)
            ->limit($this->perPage * $this->page)
            ->get();
    }
}
