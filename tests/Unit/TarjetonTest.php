<?php

namespace Tests\Unit;

use App\Models\Tarjeton;
use Carbon\Carbon;
use Tests\TestCase;

class TarjetonTest extends TestCase
{
    public function test_calcula_tiempo_operacion_mismo_dia(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = Carbon::parse('2026-03-09 08:00:00');
        $tarjeton->hora_apagado = Carbon::parse('2026-03-09 17:00:00');

        $tarjeton->calcularTiempoOperacion();

        $this->assertEquals(540, $tarjeton->tiempo_operacion_minutos);
    }

    public function test_calcula_tiempo_operacion_cruza_medianoche(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = Carbon::parse('2026-03-09 22:00:00');
        $tarjeton->hora_apagado = Carbon::parse('2026-03-10 06:00:00');

        $tarjeton->calcularTiempoOperacion();

        $this->assertEquals(480, $tarjeton->tiempo_operacion_minutos);
    }

    public function test_tiempo_operacion_formateado_devuelve_na_sin_horas(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = null;
        $tarjeton->hora_apagado = null;

        $this->assertEquals('N/A', $tarjeton->tiempo_operacion_formateado);
    }

    public function test_tiempo_operacion_formateado_muestra_horas_y_minutos(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = Carbon::parse('2026-03-09 08:00:00');
        $tarjeton->hora_apagado = Carbon::parse('2026-03-09 10:30:00');

        $this->assertEquals('2h 30m', $tarjeton->tiempo_operacion_formateado);
    }

    public function test_tiempo_operacion_formateado_indica_nocturno_al_cruzar_medianoche(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = Carbon::parse('2026-03-09 22:00:00');
        $tarjeton->hora_apagado = Carbon::parse('2026-03-10 06:00:00');

        $this->assertStringContainsString('nocturno', $tarjeton->tiempo_operacion_formateado);
    }

    public function test_tiempo_operacion_es_nulo_sin_hora_apagado(): void
    {
        $tarjeton = new Tarjeton;
        $tarjeton->hora_encendido = Carbon::parse('2026-03-09 08:00:00');
        $tarjeton->hora_apagado = null;

        $tarjeton->calcularTiempoOperacion();

        $this->assertNull($tarjeton->tiempo_operacion_minutos);
    }
}
