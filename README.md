# Registro de vehículos (cooperativa de taxis)

Proyecto del Seminario de IA aplicada al ciclo de vida del software (FET).
Es una parte pequeña de mi proyecto final, la API de la app tipo Uber (Laravel, JWT y PostgreSQL).
Aquí hice solo una función, la de registrar vehículos, para practicar con pruebas primero y con
Copilot en Visual Studio Code. No usa Laravel ni base de datos: los vehículos se guardan en una lista
en memoria, y más adelante esa lista se cambia por PostgreSQL.

## Funcionalidad de la semana 5

### Historia de usuario

**HU-03. Registrar un vehículo** (prioridad Must, necesidades N-04 y N-10)

Como administrador, quiero registrar un vehículo con su placa, marca, modelo y año para tener la
flota completa en un solo lugar.

Criterios de aceptación:

- **Escenario 1 (registro correcto):** dado que no existe la placa «TXY123», cuando registro placa
  «TXY123», marca «Renault», modelo «Logan» y año 2020, entonces el vehículo queda guardado con
  estado «disponible».
- **Escenario 2 (error: placa repetida o vacía):** dado que ya existe un vehículo con placa
  «TXY123», cuando intento registrar otro con la misma placa, o uno con la placa vacía, entonces el
  sistema responde con el mensaje «La placa es obligatoria y no puede repetirse» y no guarda el
  vehículo. (En la API este error sería un 422.)

### Contrato de la función

Clase: `App\RegistroVehiculos` (archivo `src/RegistroVehiculos.php`)

```php
public function registrar(string $placa, string $marca, string $modelo, int $anio): array
public function listar(): array
```

**Qué recibe `registrar`:** la placa, la marca, el modelo (texto) y el año (número entero).

**Qué devuelve:** un arreglo con los datos del vehículo guardado:
`['placa' => string, 'marca' => string, 'modelo' => string, 'anio' => int, 'estado' => 'disponible']`.

**Qué lanza:** `App\DatosInvalidosException` con el mensaje
«La placa es obligatoria y no puede repetirse» si la placa está vacía o ya existe.
Cuando lanza el error no se guarda nada.

**`listar`:** devuelve todos los vehículos guardados, en el orden en que se registraron.

**Supuestos:**

- Todo vehículo nuevo empieza con estado «disponible» (así lo dice la historia).
- La placa se guarda en mayúsculas y sin espacios a los lados. Lo supuse para que «txy123» y
  «TXY123» no cuenten como placas distintas (el cliente dijo que no quiere dos carros con la misma
  placa). Hay que confirmarlo con el cliente.
- Una placa de solo espacios cuenta como vacía.
- Del año, la marca y el modelo el cliente no dijo ninguna regla, así que por ahora no se validan.

**Casos límite:**

- Registro vacío: el primer vehículo se guarda sin problema.
- Placa con espacios o en minúsculas: se guarda limpia y en mayúsculas.
- Placa de solo espacios: error, igual que la placa vacía.
- Misma placa escrita distinto («txy123» contra «TXY123»): error por repetida.

**Ejemplos:**

| Entrada | Salida |
|---|---|
| `registrar('TXY123', 'Renault', 'Logan', 2020)` | `['placa' => 'TXY123', 'marca' => 'Renault', 'modelo' => 'Logan', 'anio' => 2020, 'estado' => 'disponible']` |
| `registrar('  txy123 ', 'Renault', 'Logan', 2020)` | el vehículo se guarda con placa `'TXY123'` |
| `registrar('', 'Renault', 'Logan', 2020)` | lanza `DatosInvalidosException`: «La placa es obligatoria y no puede repetirse» |
| `registrar('TXY123', ...)` cuando `TXY123` ya existe | lanza `DatosInvalidosException`, y `listar()` sigue con 1 vehículo |

### Cómo ejecutar las pruebas

Necesitas PHP 8.1 o más y Composer.

```bash
composer install
vendor/bin/phpunit
```

En una sola corrida salen las 8 pruebas (2 normales, 4 de límite y 2 de error). Si ya tienes PHPUnit
instalado en tu computador, también sirve solo `phpunit`.

## Bitácora de uso de IA

Está en [`bitacora_ia.md`](bitacora_ia.md).

## Estructura

```
src/            código de la funcionalidad
tests/          pruebas con PHPUnit
bitacora_ia.md  registro de lo que sugirió Copilot y qué decidí
```
