# Registro de pruebas — Semana 7

**Proyecto:** API de la cooperativa de taxis — funcionalidad de registro de vehículos (HU-03)  
**Estudiante:** Juan José Garzón Cárdenas  
**Fecha:** 26/11/2026

> La guía de la actividad muestra ejemplos con `pytest`, pero el repositorio entregado en la semana 6 es PHP 8.1 y ya usa PHPUnit. Por eso los casos nuevos se escribieron para PHPUnit con `@dataProvider`, que cumple el mismo propósito de ejecutar varios datos bajo una prueba. No convertí el proyecto a Python ni cambié su código de producción.

## 1. Reglas y tablas de casos

### Regla A: registrar un vehículo — aceptación y normalización de la placa

**Oráculo:** HU-03 dice que, con una placa nueva, el vehículo se guarda con placa, marca, modelo y año, y comienza en estado `disponible`. El contrato del repositorio agrega el supuesto técnico de quitar espacios laterales y convertir la placa a mayúsculas. Marca, modelo y año no se validan porque el cliente no definió reglas para esos campos.

| Técnica | Partición o límite | Entrada representativa | Esperado | Prueba que lo cubre |
|---|---|---|---|---|
| Equivalencia válida | Placa nueva con datos ordinarios | `TXY123`, Renault, Logan, 2020 | Vehículo completo; estado `disponible` | `RegistroVehiculosTest::test_registra_un_vehiculo_y_queda_disponible` |
| Límite | Primer registro en una colección vacía | Registro vacío + una placa nueva | La lista pasa de 0 a 1 vehículo | `RegistroVehiculosTest::test_el_primer_vehiculo_entra_en_un_registro_vacio` |
| Equivalencia válida | Minúsculas y espacios laterales | `  txy123 ` | Placa almacenada como `TXY123` | `RegistroVehiculosTest::test_guarda_la_placa_en_mayusculas_y_sin_espacios_a_los_lados` |
| Partición válida sintética | Marca con tilde, modelo con apóstrofo y año 0 | Datos de `tests/Fixtures/vehiculos_sinteticos.php` | Se conserva cada valor; no se inventa una validación | `RegistroVehiculosSemana7Test::test_registrar_acepta_placa_nueva_y_devuelve_el_vehiculo` |
| Partición válida sintética | Marca y modelo vacíos | Datos de fixture; placa válida | Se registra; esos campos no son obligatorios según la regla disponible | Misma prueba parametrizada, dataset `campos descriptivos vacíos` |

### Regla B: rechazar la placa inválida o duplicada sin alterar el registro

**Oráculo:** HU-03 exige placa obligatoria y única. Si está vacía o repetida, el sistema responde con `DatosInvalidosException` y el mensaje `La placa es obligatoria y no puede repetirse`; no debe guardar ni sobrescribir el vehículo. El contrato de la implementación también rechaza texto que no sea UTF-8 válido y considera duplicadas las placas iguales después de normalizarlas.

| Técnica | Partición o límite | Entrada representativa | Esperado | Prueba que lo cubre |
|---|---|---|---|---|
| Partición inválida | Cadena vacía | `''` | Excepción exacta; lista sigue vacía | `RegistroVehiculosTest::test_placa_vacia_lanza_error_y_no_guarda_nada` |
| Partición inválida | Solo espacios ASCII | `'   '` | Excepción exacta; no se guarda | `RegistroVehiculosTest::test_una_placa_con_solo_espacios_cuenta_como_vacia` |
| Partición inválida | Solo espacio Unicode no separable | U+00A0 | Excepción exacta; no se guarda | `RegistroVehiculosAbusoTest::test_una_placa_de_solo_espacios_de_no_separacion_cuenta_como_vacia` |
| Partición inválida | Solo carácter invisible | U+200B | Excepción exacta; no se guarda | `RegistroVehiculosAbusoTest::test_una_placa_de_solo_espacios_de_no_separacion_cuenta_como_vacia` y `RegistroVehiculosAbusoTest::test_espacio_de_ancho_cero_al_final_cuenta_como_placa_repetida` |
| Partición inválida | Secuencia no UTF-8 | `\xFF\xFE` | Excepción exacta; no se guarda | `RegistroVehiculosAbusoTest::test_una_placa_con_texto_que_no_es_utf8_se_rechaza_y_no_se_guarda` |
| Partición inválida | Texto normalizado a vacío por control/BOM | NUL o BOM solamente | Excepción exacta; no se guarda | `RegistroVehiculosAbusoTest::test_una_placa_de_solo_espacios_de_no_separacion_cuenta_como_vacia` y `RegistroVehiculosAbusoTest::test_espacio_de_ancho_cero_al_final_cuenta_como_placa_repetida` |
| Partición duplicada | Repetición exacta | `TXY123` después de registrar `TXY123` | Excepción exacta; conserva el primer vehículo | `RegistroVehiculosTest::test_placa_repetida_lanza_error_y_no_guarda_el_segundo` |
| Partición duplicada | Diferencia solo de mayúsculas | `txy123` después de `TXY123` | Excepción exacta; conserva el primer vehículo | `RegistroVehiculosTest::test_la_misma_placa_escrita_en_minusculas_cuenta_como_repetida` |
| Partición duplicada | Espacio Unicode o carácter invisible al final | `TXY123` + U+00A0 / U+200B | Excepción exacta; no crea un duplicado | `RegistroVehiculosAbusoTest` y nueva suite para variantes de prefijo |

### Tabla de decisión para `registrar`

Las dos condiciones observables son: **A)** la placa queda no vacía tras normalizarse y **B)** no existe ya una placa equivalente normalizada. Se exige que ambas sean verdaderas.

| A: no vacía | B: única | ¿Combinación alcanzable? | Acción esperada | Caso de prueba |
|---|---|---|---|---|
| Sí | Sí | Sí | Registrar y devolver el vehículo con estado `disponible` | `test_registrar_acepta_placa_nueva_y_devuelve_el_vehiculo` y HU-03 existente |
| Sí | No | Sí | Lanzar `DatosInvalidosException`; lista sin cambios | Pruebas de placa repetida exacta y tras normalización |
| No | Sí | Sí | Lanzar `DatosInvalidosException`; lista vacía | Pruebas de placa vacía, espacios e invisibles |
| No | No | No con el contrato actual | No hay placa vacía válida que pueda haberse guardado; se documenta como combinación imposible | No se inventa un estado artificial para forzarla |

### Regla C: `listar` conserva el contenido y el orden

**Oráculo:** el contrato de `listar` indica que devuelve una lista vacía si no hay registros y, en otro caso, todos los vehículos en el orden en que se registraron.

| Técnica | Partición o límite | Entrada | Esperado | Prueba que lo cubre |
|---|---|---|---|---|
| Límite inferior | Cero vehículos | Registro recién creado | `[]` | Nueva suite: `test_listar_devuelve_vacio_y_conserva_el_orden_de_registro` |
| Partición válida | Dos vehículos distintos | Dos registros sucesivos | Ambos, en el mismo orden | Misma prueba parametrizada/escenario |

**Ambigüedades y supuestos:** el formato de placa permitido, longitud máxima y si las mayúsculas deben ignorarse requieren confirmación del cliente; no se inventaron. El código trata diferencias de mayúscula como la misma placa, según el supuesto ya registrado en el README. No hay regla de negocio para marca, modelo ni rango del año; los datos sintéticos prueban que no se añadan restricciones arbitrarias. La combinación “placa vacía y ya duplicada” no puede construirse por la API porque una placa vacía nunca queda almacenada.

**Alcance real del fragmento recibido:** el repositorio expone dos métodos públicos con reglas (`registrar` y `listar`). La suite cubre tres grupos de comportamiento/reglas observables: aceptación y normalización, rechazo con integridad del estado y consulta ordenada. No añadí tres operaciones de negocio ficticias al proyecto solo para aumentar el conteo de funciones.

## 2. Revisión de pruebas propuestas por IA

**Prompt usado:** “A partir de HU-03 y del contrato de `RegistroVehiculos`, diseña particiones de equivalencia, límites y una tabla de decisión para registrar y listar. Después propone pruebas PHPUnit con `@dataProvider`, esperados fijos y `DatosInvalidosException`; no cambies el código de producción ni agregues reglas para marca, modelo o año.” La entrada completa y su revisión están en la bitácora, entrada 11.

| Propuesta | Decisión | Revisión | Qué quedó en su lugar |
|---|---|---|---|
| Probar solo una placa normal y afirmar que el resultado no sea nulo | Descartada | Aserción débil: no verifica campos, estado ni normalización | Aserciones `assertSame` sobre el arreglo completo en pruebas existentes y nuevas |
| Exigir patrón `AAA999` o longitud fija | Descartada | No aparece en HU-03 ni en el contrato acordado | No se agrega esa regla; queda como pregunta abierta al cliente |
| Rechazar marca/modelo vacíos o año 0 | Descartada | Regla inventada: el documento dice que esos campos no se validan por ahora | Datos sintéticos verifican que no se imponga esa validación |
| Esperar excepción genérica `Exception` | Corregida | No comprueba el tipo exacto requerido por el contrato | Se comprueba `DatosInvalidosException` y el mensaje exacto |
| Repetir exactamente todas las pruebas de semanas 5 y 6 | Descartada como casos nuevos | Serían redundantes; las pruebas previas ya cubren placa vacía, duplicada, minúsculas, NBSP, ZWSP y UTF-8 inválido | Se conservan las pruebas históricas y se añaden solo casos nuevos: fixture sintético, controles/BOM y orden de listado |
| Tabla de decisión con combinación vacía + duplicada como caso normal | Corregida | Esa combinación no es alcanzable si la API no permite guardar una placa vacía | Se marca como imposible y se cubren las otras tres combinaciones |
| Prueba de listado que solo comprueba cantidad | Corregida | Podría pasar aunque se devolvieran registros en orden distinto | Se compara la lista completa esperada con `assertSame` |

## 3. Romper el código a propósito

La suite base se ejecutó en Windows con PHP 8.3.36, Composer 2.11.2 y `mbstring` habilitada: `composer test` terminó con `OK (18 tests, 35 assertions)`. Los tres experimentos de mutación siguen pendientes; registra en esta tabla el resultado observado de cada uno.

| Mutación temporal (una por vez) | Prueba que debe fallar si se ejecuta | Resultado en esta preparación | Cómo revertir |
|---|---|---|---|
| Cambiar `$placa !== ''` por `$placa === ''` en `placaSePuedeUsar` | Las pruebas de datos válidos fallan y las de placa vacía dejan pasar la entrada | Detectada: `composer test` reportó 12 errores y 5 fallos; código de salida 2 | Restaurar la condición original `!==` en `src/RegistroVehiculos.php` |
| Quitar `mb_strtoupper($limpia, 'UTF-8')` de `normalizarPlaca` | Prueba de minúsculas/placa equivalente debería aceptar el duplicado | No ejecutado: PHP/PHPUnit ausentes | `git restore src/RegistroVehiculos.php` |
| Cambiar `!isset($this->vehiculos[$placa])` por `isset($this->vehiculos[$placa])` | Prueba de placa repetida debería permitir el duplicado y fallar | No ejecutado: PHP/PHPUnit ausentes | `git restore src/RegistroVehiculos.php` |

**Comandos al ejecutar cada mutación:** `composer test` (o `vendor/bin/phpunit --testdox`). Después de cada experimento, restaurar el archivo y volver a ejecutar la suite limpia. No entregar la mutación activa.

## 4. Datos sintéticos

**Origen:** valores ficticios preparados para esta tarea, en `tests/Fixtures/vehiculos_sinteticos.php`. No se usaron personas reales ni datos de contacto.

**Casos difíciles incluidos:** tilde y ñ (`Peña`), apóstrofo (`O'Río`), marca/modelo vacíos, año cero, cadena vacía, espacios, espacio no separable, carácter de ancho cero, NUL/BOM y bytes que no forman UTF-8. Los caracteres extremos se usan solo en la placa porque esa es la única entrada con una regla de normalización en este contrato.

## 5. Ejecución de la suite base

**Entorno:** Windows, PHP 8.3.36, Composer 2.11.2 y extensión `mbstring` habilitada.

- `composer install`: completó la instalación de dependencias. Como el proyecto no incluía `composer.lock`, Composer resolvió las versiones e hizo uno nuevo en la carpeta local.
- `composer test`: **OK (18 tests, 35 assertions)**.

Composer también mostró el aviso no fatal de que el script personalizado `audit` se omite porque coincide con un comando integrado. No impidió la instalación ni la ejecución de PHPUnit.

**Pendiente:** ejecutar cada mutación de la sección 3, anotar qué prueba falla y restaurar el código. Al final, correr `composer test` otra vez para confirmar que vuelve a pasar.
