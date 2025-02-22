<?php

namespace App\Filament\Pages;

use App\Models\Equipo;
use App\Models\Reporte;
use App\Models\User;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class Reportar extends Page
{
    use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static string $view = 'filament.pages.reportar';

    public string $name;
    public string $tag          = '';
    public string $area         = '';
    public string $department   = '';
    public string $equipment    = '';
    public string $vehicle      = '';
    public string $failure      = '';
    public string $observations = '';
    public string $priority     = '';
    public        $photo;

    protected $rules = [
        'name'     => 'required',
        'failure'  => 'required',
        'priority' => 'required',
        'photo'    => 'nullable|image',
    ];

    public $formData = [
        'areas'       => [
            'Edificio centro',
            'Edificio Universidad',
            'Edificio Violetas',
            'Santa Rosa',
            'Tiendas',
            'Fraccionamiento Elsa',
            'Violetas'
        ],
        'departments' => [
            'Costura',
            'Planchado',
            'Lavado en agua',
            'Lavado en seco',
            'Lavanderia',
            'Empaque',
            'Teñido',
            'Cuarto de maquinas',
            'Tienda Santa Rosa',
            'Tienda Centro',
            'Tienda Universidad',
            'Tienda Xoxo',
            'Tienda Deportivo',
            'Departamento Jade',
            'Departamento Verde Antequera',
            'Departamento Cherry',
            'Bodega',
            'Cuartos',
            'Local',
            'Azotea',
            'Administración'
        ],
        'equipments'  => [
            'Luminarias',
            'Lámpara de emergencia',
            'Ventiladores',
            'Nobreak',
            'Interphone',
            'Cerca eléctrica',
            'Cisternas',
            'Baño',
            'Escritorio',
            'N/A'
        ],
        'vehicles'    => ['Transit 2010', 'Transit 2016', 'Chasis NP 300', 'Partner', 'N/A'],
        'tags'        => []
    ];

    public function mount() {
        $this->name = Auth::user()->name;
        $this->formData['tags'] = Equipo::pluck('tag')->toArray();
        $this->priority = "Baja";
    }

    public function submit(): void {
        $this->validate();

        $equipo = Equipo::where('tag', $this->tag)->first();

        $reporte = Reporte::create([
            'equipo_id'    => $equipo ? $equipo->id : null,
            'tag'          => $this->tag,
            'area'         => $this->area,
            'department'   => $this->department,
            'equipment'    => $this->equipment,
            'vehicle'      => $this->vehicle,
            'failure'      => $this->failure,
            'observations' => $this->observations,
            'priority'     => $this->priority,
            'photo'        => $this->photo ? $this->photo->store() : null,
            'user_id'      => Auth::id(),
            'fecha'        => Carbon::now()
        ]);

        Notification::make()
                    ->success()
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->iconColor('success')
                    ->title('Reporte creado correctamente')
                    ->send();

        Notification::make()
                    ->title('Nuevo reporte de falla')
                    ->danger()
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->iconColor('danger')
                    ->body(auth()->user()->name . ' ha reportado una falla de ' . $this->tag ? $this->tag : ($this->equipment ?: $this->vehicle))
                    ->actions([
                        Actions\Action::make('Ver')
                                      ->button()
                                      ->url("/admin/reportes/")
                    ])
                    ->sendToDatabase(User::role('Administrador')->get(), isEventDispatched: true);
    }

}
