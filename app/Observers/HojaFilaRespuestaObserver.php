<?php

namespace App\Observers;

use App\Models\HojaFilaRespuesta;
use Illuminate\Validation\ValidationException;

class HojaFilaRespuestaObserver
{
    /**
     * Handle the HojaFilaRespuesta "creating" and "updating" event.
     * Validates BEFORE saving to database.
     */
    public function saving(HojaFilaRespuesta $hojaFilaRespuesta): void
    {
        $this->validateAnswerTypeMatch($hojaFilaRespuesta);
    }

    /**
     * Validate that the AnswerOption matches the HojaFila's AnswerType.
     *
     * Business Rule: Each row (HojaFila) has an AnswerType, and any
     * AnswerOption selected in HojaFilaRespuesta MUST belong to that
     * same AnswerType.
     *
     * @throws ValidationException
     */
    protected function validateAnswerTypeMatch(HojaFilaRespuesta $respuesta): void
    {
        // Skip validation if no answer_option_id is provided
        if (! $respuesta->answer_option_id) {
            return;
        }

        // Load the required relationships
        $respuesta->loadMissing(['hojaFila.answerType', 'answerOption.answerType']);

        // Get the AnswerType from the HojaFila (expected)
        $expectedAnswerTypeId = $respuesta->hojaFila->answer_type_id;

        // Get the AnswerType from the selected AnswerOption (actual)
        $actualAnswerTypeId = $respuesta->answerOption->answer_type_id;

        // Validate they match
        if ($expectedAnswerTypeId !== $actualAnswerTypeId) {
            throw ValidationException::withMessages([
                'answer_option_id' => sprintf(
                    'La opción de respuesta seleccionada no coincide con el tipo de respuesta esperado. '.
                    'La fila requiere respuestas de tipo "%s" (ID: %d), pero la opción seleccionada pertenece al tipo "%s" (ID: %d).',
                    $respuesta->hojaFila->answerType->label ?? 'desconocido',
                    $expectedAnswerTypeId,
                    $respuesta->answerOption->answerType->label ?? 'desconocido',
                    $actualAnswerTypeId
                ),
            ]);
        }
    }
}
