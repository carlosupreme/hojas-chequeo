<?php

namespace App\Filament\Pages;

use App\Models\FormularioRecorrido;
use App\Models\LogRecorrido;
use App\Models\Turno;
use App\Models\User;
use App\Models\ValorRecorrido;
use App\WithImageService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class CreateRecorrido extends Page
{
    use WithImageService;

    protected string $view = 'filament.resources.recorridos.pages.create-recorrido';

    protected static string|null|BackedEnum $navigationIcon = Heroicon::OutlinedPencil;

    protected static ?string $title = 'Empezar recorrido';

    public static function getNavigationLabel(): string
    {
        return 'Empezar recorrido';
    }

    #[Url(as: 'f', except: null)]
    public ?int $formularioId = null;

    #[Url(as: 'e', except: null)]
    public null|string|int $logRecorridoId = null;

    #[Url(as: 'b', except: null)]
    public ?string $backUrl = null;

    public ?int $turno_id = null;

    public $fecha;

    public array $respuestas = [];

    public ?FormularioRecorrido $formulario = null;

    public $formularios;

    public User $user;

    public ?LogRecorrido $recorrido = null;

    public array $entregaData = [];

    private const SESSION_KEY = 'recorrido_progress';

    public function mount(): void
    {
        $this->user = Auth::user();
        $this->formularios = FormularioRecorrido::withCount('categorias')->get();

        if ($this->logRecorridoId) {
            $this->loadRecorrido();

            return;
        }

        $this->fecha = now();
        $this->turno_id = $this->user->turno_id;

        if ($this->formularioId) {
            $this->formulario = FormularioRecorrido::with('categorias.items')->findOrFail($this->formularioId);
        }

        $this->restoreFromSession();
    }

    public function loadRecorrido(): void
    {
        $this->recorrido = LogRecorrido::with('valores')->findOrFail($this->logRecorridoId);

        if (! $this->formulario) {
            $this->formularioId = $this->recorrido->formulario_recorrido_id;
            $this->formulario = FormularioRecorrido::with('categorias.items')->findOrFail($this->formularioId);
        }

        $this->fecha = $this->recorrido->fecha;
        $this->turno_id = $this->recorrido->turno_id;
        $this->loadRespuestas();

        $this->entregaData = [
            'equipos_funcionando'     => $this->recorrido->equipos_funcionando,
            'observaciones_equipos'   => $this->recorrido->observaciones_equipos,
            'servicios_funcionando'   => $this->recorrido->servicios_funcionando,
            'observaciones_servicios' => $this->recorrido->observaciones_servicios,
            'firma_operador'          => $this->recorrido->firma_operador
                ? $this->imageService()->getAsBase64($this->recorrido->firma_operador)
                : null,
            'firma_supervisor'        => $this->recorrido->firma_supervisor
                ? $this->imageService()->getAsBase64($this->recorrido->firma_supervisor)
                : null,
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()->components([
                DatePicker::make('fecha')
                    ->disabled(fn () => ! $this->user->canModifyDate())
                    ->hiddenLabel()
                    ->displayFormat('D d/m/Y')
                    ->native(false)
                    ->locale('es')
                    ->closeOnDateSelection()
                    ->required()
                    ->maxDate(now()),
                Select::make('turno_id')
                    ->hiddenLabel()
                    ->options(Turno::query()->pluck('nombre', 'id'))
                    ->default($this->turno_id)
                    ->native(false)
                    ->required(),
            ]),
        ]);
    }

    public function entregaForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('entregaData')
            ->components([
                Grid::make()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Textarea::make('equipos_funcionando')
                            ->label('Equipos funcionando')
                            ->rows(3),
                        Textarea::make('observaciones_equipos')
                            ->label('Observaciones equipos')
                            ->rows(3),
                        Textarea::make('servicios_funcionando')
                            ->label('Servicios funcionando')
                            ->rows(3),
                        Textarea::make('observaciones_servicios')
                            ->label('Observaciones servicios')
                            ->rows(3),
                    ]),
                Grid::make()
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        SignaturePad::make('firma_operador')
                            ->label('Firma Operador')
                            ->penColor('blue')
                            ->penColorOnDark('blue')
                            ->live(),
                        SignaturePad::make('firma_supervisor')
                            ->label('Firma Supervisor')
                            ->penColor('blue')
                            ->penColorOnDark('blue')
                            ->live(),
                    ]),
            ]);
    }

    public function selectFormulario(int $id): void
    {
        $this->formularioId = $id;
        $this->formulario = FormularioRecorrido::with('categorias.items')->findOrFail($id);
        $this->initializeRespuestas();
    }

    public function resetState(): void
    {
        $this->formulario = null;
        $this->formularioId = null;
        $this->respuestas = [];
        $this->entregaData = [];

        $this->clearSession();
        if (! is_null($this->backUrl)) {
            redirect()->to($this->backUrl);
        }
    }

    public function guardar(): void
    {
        $validated = $this->form->getState();
        $entregaValidated = $this->entregaForm->getState();

        try {
            DB::beginTransaction();

            if (! empty($entregaValidated['firma_operador'])) {
                $entregaValidated['firma_operador'] = $this->imageService()
                    ->storeBase64('firmas', $entregaValidated['firma_operador']);
            } else {
                unset($entregaValidated['firma_operador']);
            }

            if (! empty($entregaValidated['firma_supervisor'])) {
                $entregaValidated['firma_supervisor'] = $this->imageService()
                    ->storeBase64('firmas', $entregaValidated['firma_supervisor']);
            } else {
                unset($entregaValidated['firma_supervisor']);
            }

            $log = $this->recorrido
                ? tap($this->recorrido)->update([
                    ...$validated,
                    ...$entregaValidated,
                    'fecha' => $this->fecha,
                ])
                : LogRecorrido::create([
                    ...$validated,
                    ...$entregaValidated,
                    'formulario_recorrido_id' => $this->formularioId,
                    'user_id' => $this->user->id,
                    'fecha' => $this->fecha,
                ]);

            foreach ($this->respuestas as $itemId => $datos) {
                ValorRecorrido::updateOrCreate(
                    [
                        'log_recorrido_id' => $log->id,
                        'item_recorrido_id' => $itemId,
                    ],
                    [
                        'estado' => $datos['estado'] ?? null,
                        'valor_numerico' => $datos['valor_numerico'] ?? null,
                        'valor_texto' => $datos['valor_texto'] ?? null,
                    ]
                );
            }

            DB::commit();

            Notification::make()
                ->success()
                ->icon('heroicon-o-document-text')
                ->iconColor('success')
                ->title('Recorrido guardado exitosamente')
                ->send();

            $this->resetState();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->icon('heroicon-o-exclamation-circle')
                ->title('Error al guardar')
                ->body('Ocurrió un error al guardar el recorrido. Por favor, intente nuevamente.')
                ->send();

            Log::error('Error', [
                'E' => $e->getMessage(),
            ]);
        }
    }

    public function updatedRespuestas(): void
    {
        $this->saveToSession();
    }

    private function initializeRespuestas(): void
    {
        if (! $this->formulario) {
            return;
        }

        foreach ($this->formulario->categorias as $categoria) {
            foreach ($categoria->items as $item) {
                if (! isset($this->respuestas[$item->id])) {
                    $this->respuestas[$item->id] = [
                        'estado' => null,
                        'valor_numerico' => null,
                        'valor_texto' => null,
                    ];
                }
            }
        }
    }

    private function loadRespuestas(): void
    {
        if (! $this->recorrido) {
            return;
        }

        $respuestas = $this->recorrido->valores;

        foreach ($this->formulario->categorias as $categoria) {
            foreach ($categoria->items as $item) {
                if (! isset($this->respuestas[$item->id])) {

                    $valor = $respuestas->where('item_recorrido_id', $item->id)->first();

                    $this->respuestas[$item->id] = [
                        'estado' => $valor->estado ?? null,
                        'valor_numerico' => $valor->valor_numerico ?? null,
                        'valor_texto' => $valor->valor_texto ?? null,
                    ];
                }
            }
        }
    }

    private function saveToSession(): void
    {
        session([
            self::SESSION_KEY => [
                'formularioId' => $this->formularioId,
                'turno_id' => $this->turno_id,
                'fecha' => $this->fecha,
                'respuestas' => $this->respuestas,
                'timestamp' => now()->timestamp,
            ],
        ]);
    }

    private function restoreFromSession(): void
    {
        $sessionData = session(self::SESSION_KEY);

        if (! $sessionData) {
            return;
        }

        $sessionTimestamp = $sessionData['timestamp'] ?? null;

        if ($sessionTimestamp && Carbon::createFromTimestamp($sessionTimestamp)->isYesterday()) {
            $this->clearSession();

            return;
        }

        $this->formularioId = $sessionData['formularioId'] ?? null;
        $this->turno_id = $sessionData['turno_id'] ?? $this->turno_id;
        $this->fecha = $sessionData['fecha'] ?? $this->fecha;
        $this->respuestas = $sessionData['respuestas'] ?? [];

        if ($this->formularioId) {
            $this->formulario = FormularioRecorrido::with('categorias.items')->find($this->formularioId);

            Notification::make()
                ->info()
                ->icon('heroicon-o-arrow-path')
                ->title('Progreso restaurado')
                ->body('Se ha restaurado tu progreso anterior.')
                ->send();
        }
    }

    private function clearSession(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function clearProgress(): void
    {
        $this->clearSession();
        $this->resetState();

        Notification::make()
            ->warning()
            ->title('Progreso eliminado')
            ->body('El progreso guardado ha sido eliminado.')
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Mantenimiento';
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public function getExtraBodyAttributes(): array
    {
        return [
            'class' => 'create-recorrido-page',
        ];
    }
}
