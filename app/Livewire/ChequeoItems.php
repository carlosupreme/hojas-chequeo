<?php

namespace App\Livewire;

use App\Events\HojaPresenceUpdated;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\On;
use Livewire\Component;

class ChequeoItems extends Component
{
    public $items = [];

    public $columnas = [];

    public $form = [];

    public $hojaId;

    public function mount(HojaChequeo $hoja, ?HojaEjecucion $ejecucion = null): void
    {
        $this->hojaId = $hoja->id;
        $hoja->load([
            'columnas',
            'filas.answerType.answerOptions',
            'filas.valores.hojaColumna',
        ]);

        // Presence Logic: Add user to this Hoja's active list
        $user = Auth::user();
        $cacheKey = "hoja_presence_{$hoja->id}";

        $users = Cache::get($cacheKey, []);
        $users[$user->id] = [
            'id' => $user->id,
            'name' => $user->name,
            'avatar' => $user->profile_photo_url ?? null, // Assuming standard Laravel User
            'joined_at' => now()->timestamp,
        ];
        Cache::put($cacheKey, $users, now()->addMinutes(30)); // Expire if no activity

        broadcast(new HojaPresenceUpdated(
            userId: $user->id,
            userName: $user->name,
            userAvatar: $user->profile_photo_url ?? null,
            hojaId: $hoja->id,
            action: 'joined'
        ));

        $existingResponses = $ejecucion
            ? $ejecucion->respuestas()->get()->keyBy('hoja_fila_id')
            : collect();

        $this->columnas = $hoja->columnas->map(fn ($col) => [
            'key' => $col->key,
            'label' => $col->label,
        ]);

        $this->items = $hoja->filas->map(function ($fila) use ($existingResponses) {
            $cells = [];

            foreach ($fila->valores as $valor) {
                $cells[$valor->hojaColumna->key] = $valor->valor;
            }

            // 2. Extract the value based on the AnswerType
            $resp = $existingResponses->get($fila->id);
            $type = $fila->answerType?->key;

            $initialValue = match ($type) {
                'icon_set' => $resp?->answer_option_id,
                'number' => $resp?->numeric_value,
                'text' => $resp?->text_value,
                'boolean' => (bool) $resp?->boolean_value,
                default => null
            };

            // Initialize the form state
            $this->form[$fila->id] = $initialValue;

            $initialValue = match ($type) {
                'icon_seat' => $resp?->answer_option_id,
                'number' => $resp?->numeric_value,
                'text' => $resp?->text_value,
                'icon_set' => (bool) $resp?->boolean_value,
                default => null
            };

            return [
                'id' => $fila->id,
                'type_key' => $fila->answerType?->key,
                'options' => $fila->answerType?->answerOptions->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'icon' => $o->icon,
                    'color' => $o->color,
                ]) ?? [],
                'cells' => $cells,
            ];
        })->toArray();
    }

    #[On('markAsLeft')]
    public function markAsLeft()
    {
        $user = Auth::user();
        $cacheKey = "hoja_presence_{$this->hojaId}";
        $users = Cache::get($cacheKey, []);

        if (isset($users[$user->id])) {
            unset($users[$user->id]);
            Cache::put($cacheKey, $users, now()->addMinutes(30));

            broadcast(new HojaPresenceUpdated(
                userId: $user->id,
                userName: $user->name,
                userAvatar: $user->profile_photo_url ?? null,
                hojaId: $this->hojaId,
                action: 'left'
            ));
        }
    }

    #[On('dailyCheckCreated')]
    public function save(int $id): void {}

    public function render()
    {
        return view('livewire.chequeo-items');
    }
}
