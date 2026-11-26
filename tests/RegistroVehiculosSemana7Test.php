<?php

declare(strict_types=1);

use App\DatosInvalidosException;
use App\RegistroVehiculos;
use PHPUnit\Framework\TestCase;

/** Casos diseñados para la actividad de la semana 7 a partir de HU-03. */
class RegistroVehiculosSemana7Test extends TestCase
{
    private const MENSAJE_ERROR = 'La placa es obligatoria y no puede repetirse';

    /** @return array<string, array{string, string, string, int, string}> */
    public static function datosRegistroCorrecto(): array
    {
        /** @var array<string, array{placa: string, marca: string, modelo: string, anio: int}> $fixtures */
        $fixtures = require __DIR__ . '/Fixtures/vehiculos_sinteticos.php';

        return [
            // Los campos no definidos como obligatorios no se validan; texto UTF-8 y año cero se conservan.
            'tildes apostrofo y año cero' => [
                $fixtures['campos_textuales_con_caracteres']['placa'],
                $fixtures['campos_textuales_con_caracteres']['marca'],
                $fixtures['campos_textuales_con_caracteres']['modelo'],
                $fixtures['campos_textuales_con_caracteres']['anio'],
                $fixtures['campos_textuales_con_caracteres']['placa'],
            ],
            // Marca y modelo vacíos son aceptados porque HU-03 no define una regla para ellos.
            'campos descriptivos vacíos' => [
                $fixtures['campos_textuales_vacios']['placa'],
                $fixtures['campos_textuales_vacios']['marca'],
                $fixtures['campos_textuales_vacios']['modelo'],
                $fixtures['campos_textuales_vacios']['anio'],
                $fixtures['campos_textuales_vacios']['placa'],
            ],
        ];
    }

    /** @dataProvider datosRegistroCorrecto */
    public function test_registrar_acepta_placa_nueva_y_devuelve_el_vehiculo(
        string $placa,
        string $marca,
        string $modelo,
        int $anio,
        string $placaEsperada
    ): void {
        $registro = new RegistroVehiculos();

        $resultado = $registro->registrar($placa, $marca, $modelo, $anio);

        self::assertSame([
            'placa' => $placaEsperada,
            'marca' => $marca,
            'modelo' => $modelo,
            'anio' => $anio,
            'estado' => 'disponible',
        ], $resultado);
        self::assertSame([$resultado], $registro->listar());
    }

    /** @return array<string, array{string}> */
    public static function placasInvalidas(): array
    {
        return [
            // Partición inválida nueva: un byte de control se elimina y deja placa vacía.
            'NUL solamente' => ["\x00"],
            // Partición inválida nueva: BOM/control Unicode solamente.
            'BOM solamente' => ["\u{FEFF}"],
        ];
    }

    /** @dataProvider placasInvalidas */
    public function test_registrar_rechaza_placas_vacias_o_no_normalizables(string $placa): void
    {
        $registro = new RegistroVehiculos();

        try {
            $registro->registrar($placa, 'Renault', 'Logan', 2020);
            self::fail('Se esperaba DatosInvalidosException.');
        } catch (DatosInvalidosException $exception) {
            self::assertSame(self::MENSAJE_ERROR, $exception->getMessage());
        }

        self::assertSame([], $registro->listar(), 'Una entrada inválida no debe modificar el registro.');
    }

    /** @return array<string, array{string}> */
    public static function placasRepetidasTrasNormalizar(): array
    {
        return [
            // Caso nuevo: el BOM al inicio se elimina igual que el de borde final.
            'BOM antes de placa existente' => ["\u{FEFF}TXY123"],
        ];
    }

    /** @dataProvider placasRepetidasTrasNormalizar */
    public function test_registrar_rechaza_placa_duplicada_y_no_sobrescribe_el_vehiculo(string $segundaPlaca): void
    {
        $registro = new RegistroVehiculos();
        $original = $registro->registrar('TXY123', 'Renault', 'Logan', 2020);

        try {
            $registro->registrar($segundaPlaca, 'Mazda', '3', 2018);
            self::fail('Se esperaba DatosInvalidosException para la placa repetida.');
        } catch (DatosInvalidosException $exception) {
            self::assertSame(self::MENSAJE_ERROR, $exception->getMessage());
        }

        self::assertSame([$original], $registro->listar());
    }

    public function test_listar_devuelve_vacio_y_conserva_el_orden_de_registro(): void
    {
        $registro = new RegistroVehiculos();
        self::assertSame([], $registro->listar());

        $primero = $registro->registrar('TXY123', 'Renault', 'Logan', 2020);
        $segundo = $registro->registrar('KLM456', 'Chevrolet', 'Spark', 2019);

        self::assertSame([$primero, $segundo], $registro->listar());
    }
}
