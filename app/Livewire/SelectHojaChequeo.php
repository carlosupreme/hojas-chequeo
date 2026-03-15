<?php

namespace App\Livewire;

use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SelectHojaChequeo extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public ?array $data = [];

    public string $search = '';

    public int $perPage = 12;

    public int $page = 1;

    public ?string $activeFilter = null;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->form->fill([
            'centro_costo' => Auth::user()->turno?->centro_costo_id,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('centro_costo')
                ->options(CentroCosto::query()->pluck(column: 'nombre', key: 'id'))
                ->native(false)
                ->preload()
                ->live()
                ->required()
                ->hiddenLabel()
                ->afterStateUpdated(function (Set $set, $state) {
                    if (empty($state)) {
                        $set('centro_costo', Auth::user()->turno?->centro_costo_id);
                    }
                }),
        ])->statePath('data');
    }

    public function toggleFilter(?string $filter = null): void
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

    public function selectHojaChequeo($id): void
    {
        $this->dispatch('hojaChequeoSelected', [
            'id' => $id,
            'centro_costo' => $this->data['centro_costo'],
        ]);
    }

    public function selectHojaEjecucion($chequeoId): void
    {
        $this->dispatch('hojaEjecucionSelected', [
            'id' => $chequeoId,
            'centro_costo' => $this->data['centro_costo'],
        ]);
    }

    public function render(): View
    {
        $user = Auth::user();

        $applyFilters = function (Builder $query) {
            $query->whereHas('hojaChequeo', function ($q) {
                $q->inArea($this->activeFilter)
                    ->search($this->search);
            });
        };

        $chequeosPendientes = $user->chequeosPendientes()
            ->tap($applyFilters)
            ->with(['hojaChequeo.equipo'])
            ->get();

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
            'areas' => Equipo::distinct()->orderBy('area')->pluck('area')
                ->filter()
                ->map(fn (string $a) => ucwords(mb_strtolower($a)))
                ->unique()
                ->sort()
                ->values(),
            'user' => $user,
            'turno' => $user->turno,
        ]);
    }

    protected function buildCacheKey(int $userId): string
    {
        $user = User::find($userId);
        $idsHash = md5(implode(',', $user->perfil->hoja_ids ?? []));
        $filter = $this->activeFilter ?? 'all';
        $search = $this->search ? md5(strtolower($this->search)) : 'none';

        return "hojas:list:{$userId}:{$idsHash}:{$filter}:{$search}:page{$this->page}";
    }

    protected function fetchHojas(User $user)
    {
        return HojaChequeo::with(['equipo', 'latestChequeoDiario'])
            ->select(['id', 'equipo_id', 'encendido', 'version'])
            ->availableTo($user->perfil)
            ->encendidas()
            ->inArea($this->activeFilter)
            ->search($this->search)
            ->orderByRaw('(SELECT MAX(finalizado_en) FROM hoja_ejecucions WHERE hoja_chequeo_id = hoja_chequeos.id AND finalizado_en IS NOT NULL) ASC NULLS FIRST')
            ->limit($this->perPage * $this->page)
            ->get();
    }
}
