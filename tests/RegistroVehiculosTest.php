<?php

use App\DatosInvalidosException;
use App\RegistroVehiculos;
use PHPUnit\Framework\TestCase;

class RegistroVehiculosTest extends TestCase
{
    private const MENSAJE_ERROR = 'La placa es obligatoria y no puede repetirse';

    // ---------- casos normales ----------

    public function test_registra_un_vehiculo_y_queda_disponible(): void
    {
        $registro = new RegistroVehiculos();

        $vehiculo = $registro->registrar('TXY123', 'Renault', 'Logan', 2020);

        $this->assertSame('TXY123', $vehiculo['placa']);
        $this->assertSame('Renault', $vehiculo['marca']);
        $this->assertSame('Logan', $vehiculo['modelo']);
        $this->assertSame(2020, $vehiculo['anio']);
        $this->assertSame('disponible', $vehiculo['estado']);
    }

    public function test_registra_dos_vehiculos_con_placas_distintas(): void
    {
        $registro = new RegistroVehiculos();

        $registro->registrar('TXY123', 'Renault', 'Logan', 2020);
        $registro->registrar('KLM456', 'Chevrolet', 'Spark', 2019);

        $this->assertCount(2, $registro->listar());
    }

    // ---------- casos límite ----------

    public function test_el_primer_vehiculo_entra_en_un_registro_vacio(): void
    {
        $registro = new RegistroVehiculos();

        $this->assertCount(0, $registro->listar());
        $registro->registrar('TXY123', 'Renault', 'Logan', 2020);

        $this->assertCount(1, $registro->listar());
    }

    public function test_guarda_la_placa_en_mayusculas_y_sin_espacios_a_los_lados(): void
    {
        $registro = new RegistroVehiculos();

        $vehiculo = $registro->registrar('  txy123 ', 'Renault', 'Logan', 2020);

        $this->assertSame('TXY123', $vehiculo['placa']);
    }

    public function test_una_placa_con_solo_espacios_cuenta_como_vacia(): void
    {
        $registro = new RegistroVehiculos();

        $this->expectException(DatosInvalidosException::class);
        $this->expectExceptionMessage(self::MENSAJE_ERROR);

        $registro->registrar('   ', 'Renault', 'Logan', 2020);
    }

    public function test_la_misma_placa_escrita_en_minusculas_cuenta_como_repetida(): void
    {
        $registro = new RegistroVehiculos();
        $registro->registrar('TXY123', 'Renault', 'Logan', 2020);

        $this->expectException(DatosInvalidosException::class);

        $registro->registrar('txy123', 'Mazda', '3', 2018);
    }

    // ---------- casos de error (escenario 2 de HU-03) ----------

    public function test_placa_vacia_lanza_error_y_no_guarda_nada(): void
    {
        $registro = new RegistroVehiculos();

        try {
            $registro->registrar('', 'Renault', 'Logan', 2020);
            $this->fail('Debió lanzar DatosInvalidosException');
        } catch (DatosInvalidosException $e) {
            $this->assertSame(self::MENSAJE_ERROR, $e->getMessage());
        }

        $this->assertCount(0, $registro->listar());
    }

    public function test_placa_repetida_lanza_error_y_no_guarda_el_segundo(): void
    {
        $registro = new RegistroVehiculos();
        $registro->registrar('TXY123', 'Renault', 'Logan', 2020);

        try {
            $registro->registrar('TXY123', 'Mazda', '3', 2018);
            $this->fail('Debió lanzar DatosInvalidosException');
        } catch (DatosInvalidosException $e) {
            $this->assertSame(self::MENSAJE_ERROR, $e->getMessage());
        }

        $this->assertCount(1, $registro->listar());
    }
}
