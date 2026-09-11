<?php

namespace Tests\Feature;

use App\Livewire\Analisis\AnalisisHojaChequeo;
use App\Models\CentroCosto;
use App\Models\Equipo;
use App\Models\HojaChequeo;
use App\Models\HojaEjecucion;
use App\Models\OffDay;
use App\Models\Perfil;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AnalisisHojaChequeoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $perfil = Perfil::factory()->accesoTotal()->create();
        Role::firstOrCreate(['name' => 'Administrador', 'guard_name' => 'web']);
        $this->user = User::factory()->create([
            'perfil_id' => $perfil->id,
            'turno_id' => null,
        ]);
    }

    public function test_cumplimiento_counts_one_valid_check_per_equipo_per_working_day(): void
    {
        $centroCosto = CentroCosto::create(['nombre' => 'Mantenimiento']);

        $turno = Turno::create([
            'centro_costo_id' => $centroCosto->id,
            'nombre' => 'Turno Mantenimiento',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '06:00:00',
            'hora_final' => '14:00:00',
            'activo' => true,
        ]);

        OffDay::create([
            'centro_costo_id' => $centroCosto->id,
            'fecha' => '2026-03-02',
            'motivo' => 'Día inhábil',
        ]);

        $equipo1 = Equipo::factory()->create(['tag' => 'CM-CAL-01']);
        $equipo2 = Equipo::factory()->create(['tag' => 'CM-CAL-02']);
        $turno->equipos()->attach([$equipo1->id, $equipo2->id]);

        $hoja1 = HojaChequeo::factory()->create(['equipo_id' => $equipo1->id]);
        $hoja2 = HojaChequeo::factory()->create(['equipo_id' => $equipo2->id]);

        foreach (['2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06', '2026-03-07'] as $date) {
            HojaEjecucion::factory()->create([
                'hoja_chequeo_id' => $hoja1->id,
                'user_id' => $this->user->id,
                'turno_id' => $turno->id,
                'centro_costo_id' => $centroCosto->id,
                'finalizado_en' => $date.' 08:00:00',
                'created_at' => $date.' 08:00:00',
            ]);

            HojaEjecucion::factory()->create([
                'hoja_chequeo_id' => $hoja2->id,
                'user_id' => $this->user->id,
                'turno_id' => $turno->id,
                'centro_costo_id' => $centroCosto->id,
                'finalizado_en' => $date.' 09:00:00',
                'created_at' => $date.' 09:00:00',
            ]);
        }

        // Duplicate chequeo on the same day for the same equipo should still count as 1.
        HojaEjecucion::factory()->create([
            'hoja_chequeo_id' => $hoja1->id,
            'user_id' => $this->user->id,
            'turno_id' => $turno->id,
            'centro_costo_id' => $centroCosto->id,
            'finalizado_en' => '2026-03-03 12:00:00',
            'created_at' => '2026-03-03 12:00:00',
        ]);

        $component = Livewire::test(AnalisisHojaChequeo::class, [
            'startDate' => '2026-03-01',
            'endDate' => '2026-03-15',
        ]);

        $stats = $component->get('cumplimientoPorCentroCosto');

        $this->assertCount(1, $stats);
        $centroStats = $stats[0];
        $turnoStats = $centroStats['turnos'][0];

        $this->assertSame(22, $centroStats['total_expected']);
        $this->assertSame(10, $centroStats['total_actual']);
        $this->assertEquals(45.5, $centroStats['percentage']);

        $this->assertSame(11, $turnoStats['working_days']);
        $this->assertSame(22, $turnoStats['expected']);
        $this->assertSame(10, $turnoStats['actual']);
        $this->assertEquals(45.5, $turnoStats['percentage']);

        $this->assertSame('CM-CAL-01', $turnoStats['equipos_detail'][0]['tag']);
        $this->assertSame(5, $turnoStats['equipos_detail'][0]['dias_revisados']);
        $this->assertSame(11, $turnoStats['equipos_detail'][0]['dias_esperados']);
        $this->assertSame('CM-CAL-02', $turnoStats['equipos_detail'][1]['tag']);
        $this->assertSame(5, $turnoStats['equipos_detail'][1]['dias_revisados']);
        $this->assertSame(11, $turnoStats['equipos_detail'][1]['dias_esperados']);
    }

    public function test_cumplimiento_respects_selected_hoja_chequeo_filter(): void
    {
        $centroCosto = CentroCosto::create(['nombre' => 'Mantenimiento']);

        $turno = Turno::create([
            'centro_costo_id' => $centroCosto->id,
            'nombre' => 'Turno Filtrado',
            'dias' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
            'hora_inicio' => '06:00:00',
            'hora_final' => '14:00:00',
            'activo' => true,
        ]);

        OffDay::create([
            'centro_costo_id' => $centroCosto->id,
            'fecha' => '2026-03-02',
        ]);

        $equipo1 = Equipo::factory()->create(['tag' => 'CM-CAL-01']);
        $equipo2 = Equipo::factory()->create(['tag' => 'CM-CAL-02']);
        $turno->equipos()->attach([$equipo1->id, $equipo2->id]);

        $hoja1 = HojaChequeo::factory()->create(['equipo_id' => $equipo1->id]);
        $hoja2 = HojaChequeo::factory()->create(['equipo_id' => $equipo2->id]);

        foreach (['2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06', '2026-03-07'] as $date) {
            HojaEjecucion::factory()->create([
                'hoja_chequeo_id' => $hoja1->id,
                'user_id' => $this->user->id,
                'turno_id' => $turno->id,
                'centro_costo_id' => $centroCosto->id,
                'finalizado_en' => $date.' 08:00:00',
                'created_at' => $date.' 08:00:00',
            ]);

            HojaEjecucion::factory()->create([
                'hoja_chequeo_id' => $hoja2->id,
                'user_id' => $this->user->id,
                'turno_id' => $turno->id,
                'centro_costo_id' => $centroCosto->id,
                'finalizado_en' => $date.' 09:00:00',
                'created_at' => $date.' 09:00:00',
            ]);
        }

        $component = Livewire::test(AnalisisHojaChequeo::class, [
            'startDate' => '2026-03-01',
            'endDate' => '2026-03-15',
        ])->set('hojaChequeoId', $hoja1->id);

        $stats = $component->get('cumplimientoPorCentroCosto');
        $centroStats = $stats[0];
        $turnoStats = $centroStats['turnos'][0];

        $this->assertSame(11, $centroStats['total_expected']);
        $this->assertSame(5, $centroStats['total_actual']);
        $this->assertEquals(45.5, $centroStats['percentage']);
        $this->assertSame(1, $turnoStats['equipos']);
        $this->assertCount(1, $turnoStats['equipos_detail']);
        $this->assertSame('CM-CAL-01', $turnoStats['equipos_detail'][0]['tag']);
        $this->assertSame(5, $turnoStats['equipos_detail'][0]['dias_revisados']);
        $this->assertSame(11, $turnoStats['equipos_detail'][0]['dias_esperados']);
    }
}
