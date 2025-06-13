<?php

namespace App\Filament\Resources\TarjetonResource\Pages;

use App\Models\Tarjeton;
use App\Models\Equipo;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;


class BitacoraReporte extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = \App\Filament\Resources\TarjetonResource::class;
    protected static string $view = 'filament.resources.tarjeton-resource.pages.bitacora-reporte';
    protected static ?string $title = 'Generar Bitácora';
    protected static ?string $navigationLabel = 'Bitácora';
    
    public ?array $data = [];
    public $registros = [];
    public $equipo = null;
    public $mostrarReporte = false;

    public function mount(): void
    {
        $this->form->fill([
            'fecha_inicio' => now()->subDays(7)->format('Y-m-d'),
            'fecha_fin' => now()->format('Y-m-d'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
             Select::make('equipo_id')
                ->label('Tag del Equipo')
                ->options(Equipo::orderBy('tag')->pluck('tag', 'id'))
                ->searchable()
                ->preload()
                ->required(),
                    
                DatePicker::make('fecha_inicio')
                    ->label('Fecha Inicio')
                    ->required()
                    ->default(now()->subDays(7)),
                    
                DatePicker::make('fecha_fin')
                    ->label('Fecha Fin')
                    ->required()
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
            ->whereDate('fecha', '>=', $data['fecha_inicio'])
            ->whereDate('fecha', '<=', $data['fecha_fin'])
            ->orderBy('fecha', 'asc')
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

    public function exportarPdf():? StreamedResponse
    {
        if (!$this->mostrarReporte || $this->registros->isEmpty()) {
            Notification::make()
                ->error()
                ->title('Error')
                ->body('Genera el reporte primero.')
                ->send();
            return response()->noContent();
        }

        $data = $this->form->getState();
        
        $pdf = PDF::loadView('bitacora-reporte-pdf', [
            'registros' => $this->registros,
            'equipo' => $this->equipo,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
        ]);

        $nombreArchivo = "Bitacora-{$this->equipo->tag}-" . 
                        Carbon::parse($data['fecha_inicio'])->format('d-m-Y') . 
                        "-al-" . 
                        Carbon::parse($data['fecha_fin'])->format('d-m-Y') . ".pdf";

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, $nombreArchivo);
    }

    public function limpiarReporte(): void
    {
        $this->mostrarReporte = false;
        $this->registros = [];
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