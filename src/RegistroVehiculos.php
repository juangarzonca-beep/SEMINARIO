<?php

namespace App;

class RegistroVehiculos
{
    private array $vehiculos = [];

    public function registrar(string $placa, string $marca, string $modelo, int $anio): array
    {
        $placa = strtoupper(trim($placa));

        if ($placa === '' || isset($this->vehiculos[$placa])) {
            throw new DatosInvalidosException('La placa es obligatoria y no puede repetirse');
        }

        $vehiculo = [
            'placa' => $placa,
            'marca' => $marca,
            'modelo' => $modelo,
            'anio' => $anio,
            'estado' => 'disponible',
        ];
        $this->vehiculos[$placa] = $vehiculo;

        return $vehiculo;
    }

    public function listar(): array
    {
        return array_values($this->vehiculos);
    }
}
