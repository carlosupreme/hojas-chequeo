<?php

namespace App\Filament\Resources\Tarjetons\Pages;

use App\Filament\Resources\Tarjetons\TarjetonResource;
use App\Models\Equipo;
use App\Models\Tarjeton;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BitacoraReporte extends Page
{
    protected static string $resource = TarjetonResource::class;

    protected string $view = 'filament.resources.tarjetons.pages.bitacora-reporte';

    protected static ?string $title = 'Generar Bitácora';

    protected static ?string $navigationLabel = 'Bitácora';

    public ?array $data = [];

    public $registros;

    public $equipo = null;

    public $mostrarReporte = false;

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->subDays(7),
            'fecha_fin' => now(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('equipo_id')
                    ->label('Tag del Equipo')
                    ->options(Equipo::calderas()->pluck('tag', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),

                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->required()
                    ->displayFormat('D d/m/Y')
                    ->native(false)
                    ->locale('es')
                    ->closeOnDateSelection()
                    ->default(now()->subDays(7)),

                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required()
                    ->displayFormat('D d/m/Y')
                    ->native(false)
                    ->locale('es')
                    ->closeOnDateSelection()
                    ->default(now())
                    ->afterOrEqual('fecha_inicio'),
            ])
            ->statePath('data')
            ->columns(3);
    }

    public function generarReporte(): void
    {
        $data = $this->form->getState();

        $this->equipo = Equipo::find($data['equipo_id']);

        $this->registros = Tarjeton::with('equipo')
            ->where('equipo_id', $data['equipo_id'])
            ->whereDate('hora_encendido', '>=', $data['fecha_inicio'])
            ->whereDate('hora_encendido', '<=', $data['fecha_fin'])
            ->orderBy('hora_encendido', 'asc')
            ->get();

        $this->mostrarReporte = true;

        if ($this->registros->isEmpty()) {
            Notification::make()
                ->warning()
                ->title('Sin registros')
                ->body('No se encontraron registros para el rango de fechas seleccionado.')
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Reporte generado')
                ->body("Se encontraron {$this->registros->count()} registros.")
                ->send();
        }
    }

    public function exportarPdf(): ?StreamedResponse
    {
        if (! $this->mostrarReporte) {
            Notification::make()
                ->title('Error')
                ->body('Genera el reporte primero.')
                ->send();
        }

        $data = $this->form->getState();

        $registrosPorDia = $this->registros->groupBy(
            fn (Tarjeton $t) => $t->hora_encendido?->format('Y-m-d') ?? 'sin-fecha'
        );

        $pdf = PDF::loadView('bitacora-reporte-pdf', [
            'registros' => $this->registros,
            'registrosPorDia' => $registrosPorDia,
            'equipo' => $this->equipo,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);

        $nombreArchivo = "Bitacora-{$this->equipo->tag}-".
            Carbon::parse($data['fecha_inicio'])->format('d-m-Y').
            '-al-'.
            Carbon::parse($data['fecha_fin'])->format('d-m-Y').'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $nombreArchivo);
    }

    public function limpiarReporte(): void
    {
        $this->mostrarReporte = false;
        $this->registros = collect();
        $this->equipo = null;

        Notification::make()
            ->info()
            ->title('Reporte limpiado')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generar')
                ->label('Generar Reporte')
                ->color('primary')
                ->action('generarReporte'),

            Action::make('exportar')
                ->label('Exportar PDF')
                ->color('success')
                ->icon('heroicon-o-document-arrow-down')
                ->visible($this->mostrarReporte)
                ->action('exportarPdf'),

            Action::make('limpiar')
                ->label('Limpiar')
                ->color('gray')
                ->icon('heroicon-o-trash')
                ->visible($this->mostrarReporte)
                ->action('limpiarReporte'),
        ];
    }
}
