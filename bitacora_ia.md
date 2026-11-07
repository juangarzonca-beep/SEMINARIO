# Bitácora de uso de IA

Proyecto: API de la app tipo Uber (Laravel, JWT y PostgreSQL). Aquí, el registro de vehículos en un repositorio aparte
Estudiante: Juan José Garzón Cárdenas
Funcionalidad: HU-03 Registrar un vehículo
Asistente usado: GitHub Copilot en Visual Studio Code, con el chat y las sugerencias mientras escribía

## Entradas

### Entrada 1

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** «Con esta historia HU-03 y sus dos escenarios, dame casos de prueba en PHPUnit para registrar un vehículo: normales, de límite y de error.»
- **Decisión:** Aceptada
- **Por qué:** Los casos cubrían los dos escenarios de mi criterio de aceptación (registro correcto y placa repetida o vacía) y además pensó en el registro vacío y en la placa con espacios, que yo no había anotado.
- **Verificación:** Leí cada prueba y comprobé que tuviera nombre claro y que el mensaje de error fuera el mismo de la historia. Con el código sin escribir las 8 pruebas fallaron todas, como debía ser (`phpunit`).
- **Commit relacionado:** 621f63f «Pruebas de registrar vehículo, HU-03 (en rojo)»

### Entrada 2

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Copilot sugirió agregar pruebas para rechazar un año menor que 1900 o mayor que el año actual, y una marca o modelo vacíos.
- **Decisión:** Rechazada
- **Por qué:** El cliente no dijo ninguna regla para el año, la marca ni el modelo; en mi documento de requisitos solo piden la placa única y obligatoria. Si las agregaba, el código tendría reglas sin origen en las historias.
- **Verificación:** Revisé la HU-03 y el RNF-05 del documento de requisitos y busqué esas reglas: no aparecen. Dejé anotado en el README que esos campos por ahora no se validan.
- **Commit relacionado:** 621f63f «Pruebas de registrar vehículo, HU-03 (en rojo)»

### Entrada 3

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** «Con el contrato del README y estas pruebas, escribe la clase RegistroVehiculos.» Copilot comparó la placa con `empty($placa)` y buscó repetidas con `isset($this->vehiculos[$placa])`, sin tocar el texto de la placa.
- **Decisión:** Aceptada con cambios
- **Por qué:** La idea de guardar los vehículos con la placa como llave me sirvió, pero no limpiaba la placa: «  txy123 » se guardaba tal cual y «txy123» no se detectaba como repetida de «TXY123». Le agregué `strtoupper(trim($placa))` y cambié `empty()` por `=== ''`.
- **Verificación:** Al correr `phpunit` fallaron 3 de las 8 pruebas (mayúsculas y espacios, placa de solo espacios y placa repetida en minúsculas). Después del cambio pasaron las 8.
- **Commit relacionado:** 98ca753 «Registrar vehículo: implementación, las 8 pruebas pasan (en verde)»

### Entrada 4

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Copilot propuso guardar los vehículos con un modelo Eloquent y la base de datos PostgreSQL desde el principio.
- **Decisión:** Rechazada
- **Por qué:** Esta tarea es una función pequeña y las pruebas deben correr con un solo comando, sin instalar Laravel ni conectar una base de datos. Lo de PostgreSQL ya está decidido en mi diseño (ADR-001), pero lo hago después, cuando esta lógica pase a la API.
- **Verificación:** Con una lista en memoria las pruebas corren con `vendor/bin/phpunit` y tardan menos de un segundo.
- **Commit relacionado:** 98ca753 «Registrar vehículo: implementación, las 8 pruebas pasan (en verde)»

### Entrada 5

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** «Refactoriza RegistroVehiculos sin cambiar su comportamiento: quita los valores mágicos y separa lo que se pueda en métodos pequeños.»
- **Decisión:** Aceptada
- **Por qué:** Dejó el estado inicial y el mensaje de error en constantes y sacó dos métodos privados (`normalizarPlaca` y `placaSePuedeUsar`). El método `registrar` ahora se lee casi como la historia.
- **Verificación:** No toqué ninguna prueba (`git diff` de la carpeta `tests` sale vacío) y siguen pasando las 8 pruebas.
- **Commit relacionado:** 7e9dff4 «Refactor: constantes para el estado y el mensaje, y métodos normalizarPlaca y placaSePuedeUsar»

### Entrada 6

- **Fecha:** 2026-11-07
- **Prompt o sugerencia:** Para el error de placa inválida, Copilot sugirió lanzar la `InvalidArgumentException` que ya trae PHP con el mensaje de texto.
- **Decisión:** Aceptada con cambios
- **Por qué:** Prefería una excepción propia, `DatosInvalidosException`, para que la API pueda convertirla en un 422 sin confundirla con otros errores de PHP. La hice heredar de `InvalidArgumentException`, así que sigue siendo una excepción de PHP.
- **Verificación:** Las pruebas de error usan `expectException(DatosInvalidosException::class)` y comprueban el mensaje exacto de la historia; pasan las dos.
- **Commit relacionado:** 98ca753 «Registrar vehículo: implementación, las 8 pruebas pasan (en verde)»

## Resumen

| Decisión | Cantidad |
|---|---|
| Aceptadas | 2 |
| Aceptadas con cambios | 2 |
| Rechazadas | 2 |

## Errores que atraparon las pruebas

La primera versión que sugirió Copilot comparaba la placa tal como llegaba. Tres pruebas fallaron:
`test_guarda_la_placa_en_mayusculas_y_sin_espacios_a_los_lados` (la placa quedaba como `'  txy123 '`),
`test_una_placa_con_solo_espacios_cuenta_como_vacia` (no lanzaba el error, porque `empty('   ')` es falso)
y `test_la_misma_placa_escrita_en_minusculas_cuenta_como_repetida` (dejaba registrar «txy123» aunque «TXY123»
ya existía). Lo corregí limpiando la placa con `trim` y `strtoupper` antes de validarla, y comparando con `=== ''`.
Sin esas pruebas habría quedado un código que parecía funcionar con el ejemplo de la historia, pero dejaba duplicar
placas con solo cambiar las mayúsculas, que es justo lo que el cliente no quiere.

## Reflexión final

Lo que más me ayudó Copilot fue a pensar casos que yo no tenía, como el registro vacío y la placa con espacios, y a
armar rápido la estructura de la clase. El error que atraparon mis pruebas fue que la primera versión no limpiaba la
placa, así que se podía repetir con minúsculas. Aprendí que el código que da la IA puede pasar el ejemplo de la
historia y aun así fallar en los casos límite, y que por eso conviene escribir las pruebas antes. También tuve que
rechazar sugerencias que se pasaban del contrato, como validar el año o usar la base de datos, porque nadie las había
pedido. En la próxima funcionalidad escribiría yo mismo la primera versión de los casos límite antes de pedirle
ayuda al asistente, y confirmaría con el cliente los supuestos (como lo de las mayúsculas) antes de programarlos.
