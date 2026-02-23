<?php

namespace App\Livewire;

use App\Area;
use App\Models\CentroCosto;
use App\Models\HojaChequeo;
use App\Models\User;
use Filament\Forms\Components\Select;
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

    public ?Area $activeFilter = null;

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
                ->hiddenLabel(),
        ])->statePath('data');
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
                $q->inArea($this->activeFilter?->value)
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
            'areas' => Area::cases(),
            'user' => $user,
            'turno' => $user->turno,
        ]);
    }

    protected function buildCacheKey(int $userId): string
    {
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
