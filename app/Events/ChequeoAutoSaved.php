<?php

namespace App\Events;

use App\Models\HojaEjecucion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ChequeoAutoSaved implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public readonly HojaEjecucion $ejecucion
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('chequeos');
    }

    public function broadcastAs(): string
    {
        return 'saved';
    }

    public function broadcastWith(): array
    {
        return [
            'ejecucion_id' => $this->ejecucion->id,
            'hoja_chequeo_id' => $this->ejecucion->hoja_chequeo_id,
            'finalizado_en' => $this->ejecucion->finalizado_en?->toISOString(),
        ];
    }
}
