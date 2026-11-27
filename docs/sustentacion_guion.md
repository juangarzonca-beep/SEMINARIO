# Guion para el video y la sustentación

**Duración objetivo:** 9 a 10 minutos. El docente dispone de 4 minutos para preguntas. Usa las diapositivas como guía; explica con tus palabras y abre el repositorio en las evidencias indicadas.

## Diapositiva 1 | Presentación (30 segundos)

Buenos días. Soy Juan José Garzón Cárdenas y voy a presentar mi proyecto integrador del seminario. Mi trabajo se centra en una funcionalidad de una aplicación para una cooperativa de taxis: registrar vehículos y consultar la flota. Voy a explicar qué resuelve esta versión, cómo utilicé inteligencia artificial durante el desarrollo y, sobre todo, cómo comprobé sus sugerencias con pruebas y revisión propia.

## Diapositiva 2 | Problema y usuario (1 minuto)

El usuario de esta historia es el administrador de la cooperativa. Necesita tener los vehículos registrados en un solo lugar y evitar que se repita la placa. La historia HU-03 pide registrar placa, marca, modelo y año. Para esta versión, la regla obligatoria está en la placa: no puede estar vacía ni repetirse. También se acordó que un vehículo nuevo empieza disponible.

La solución todavía es un componente pequeño para demostrar esa regla. No es la aplicación completa ni una API funcionando en internet. Esta precisión importa porque PostgreSQL, el inicio de sesión y la autorización por roles están definidos como trabajo posterior, no como funcionalidades que ya estén construidas.

## Diapositiva 3 | Solución y arquitectura (1 minuto)

El proyecto está escrito en PHP. La clase principal se llama RegistroVehiculos. Mantiene los vehículos en un arreglo cuya llave es la placa. El método registrar limpia los espacios y caracteres invisibles en los extremos, convierte la placa a mayúsculas, verifica que no quede vacía y revisa que no exista. Si pasa esas condiciones, devuelve los datos con estado disponible. Si falla, lanza DatosInvalidosException y no modifica el registro. El método listar devuelve la colección en el orden en que se registró.

El diseño en memoria me permitió ejecutar pruebas unitarias rápidas sin preparar una base de datos. La limitación es que los datos se pierden cuando termina el proceso; por eso no afirmo que esta versión ya persista información.

## Diapositiva 4 | Uso de IA en el ciclo de vida (1 minuto 20 segundos)

Usé GitHub Copilot para proponer pruebas y una primera implementación; Claude para revisar código y riesgos; y ChatGPT como apoyo para diseñar casos de la semana 7 y organizar esta presentación. La bitácora del repositorio registra prompts, decisiones y verificaciones.

En requisitos, por ejemplo, Copilot sugirió validar el año y exigir marca y modelo. Rechacé esa propuesta porque la historia no contiene esas reglas. En diseño, preferí una clase en memoria para probar la función aislada y dejé Eloquent y PostgreSQL para una etapa posterior. En código y pruebas, usé PHPUnit para comprobar casos válidos, inválidos y de límite. Para operación agregué un workflow de GitHub Actions que instala PHP con mbstring y ejecuta PHPUnit cuando hay cambios.

Hay una limitación documental que quiero reconocer: el repositorio no tiene una evidencia independiente de IA para el módulo 1, ni el ADR, la guía de despliegue o el plan de operación completo. No estoy atribuyendo esos documentos a una fase si no están ahí.

## Diapositiva 5 | Diseño y resultados de pruebas (1 minuto 40 segundos)

Las pruebas parten de las reglas observables de la historia. Incluyen placas nuevas, placas vacías, duplicadas, escritas con distintas mayúsculas, espacios Unicode, caracteres invisibles y texto que no es UTF-8. También comprueban que un error no sobrescriba el vehículo existente y que listar conserve el orden.

La bitácora y registro_pruebas.md muestran la suite y las técnicas: particiones de equivalencia, valores límite y tabla de decisión. En mi computador con Windows, PHP 8.3.36 y Composer 2.11.2 ejecuté composer test. El resultado fue 18 pruebas y 35 aserciones aprobadas. Aquí puedo abrir el archivo de pruebas y mostrar un caso concreto: después de registrar TXY123, una placa que se normaliza a la misma forma debe producir DatosInvalidosException y dejar intacto el primer vehículo.

## Diapositiva 6 | Revisión crítica de IA (1 minuto 30 segundos)

Un primer error de alcance fue la propuesta de añadir validaciones sin respaldo, como rechazar el año cero o una marca vacía. Revisé la historia y rechacé esas reglas. Así evité que una respuesta de IA cambiara el comportamiento esperado por cuenta propia.

Otro caso fue la primera implementación que comparaba la placa sin normalizarla de forma suficiente. Las pruebas de espacios, mayúsculas y duplicados fallaron. Corregí la normalización y volví a ejecutar la suite. La revisión de seguridad también señaló espacios Unicode e invisibles. Escribí pruebas para confirmar ese problema, corregí la función y dejé el resultado en la bitácora y en los commits registrados.

Estos casos muestran el proceso que seguí: comparar con el requisito, reproducir el comportamiento y aceptar el cambio solo después de observar una prueba.

## Diapositiva 7 | Mutaciones y pipeline (1 minuto 20 segundos)

Para evaluar si las pruebas realmente detectaban errores, cambié temporalmente tres condiciones del código, una por vez. Al invertir la condición que acepta placas nuevas, la ejecución que compartí mostró 12 errores y 5 fallos. También quité la conversión a mayúsculas y cambié la comprobación de duplicados. Reporté que las pruebas detectaron las dos mutaciones con los efectos previstos, pero no guardé sus conteos exactos, así que no voy a inventarlos.

Además, agregué el workflow de integración continua para PHP 8.3. El archivo está en .github/workflows/ci.yml. La primera ejecución ya terminó correctamente en 14 segundos, en el commit 97efd36. GitHub muestra una advertencia sobre la migración de Node.js 20 a 24 en checkout v4, pero no afectó el resultado. El pipeline no sustituye la ejecución local ni demuestra que el programa esté desplegado; automatiza la instalación y la suite al subir cambios.

## Diapositiva 8 | Ética, límites y cierre (1 minuto)

Las pruebas usan datos ficticios. Esta funcionalidad no necesita datos de pasajeros ni conductores. Si la aplicación futura almacena placas o datos personales, antes de desplegarla tendría que definir la finalidad, quién puede consultar la información, cuánto tiempo se conserva y cómo se protege.

También mantuve visibles los límites: esta versión no tiene API, base de datos ni control de acceso y no atribuyo un porcentaje de cobertura que no medí. Como siguiente paso, consolidaría la evidencia que falta de los módulos iniciales, publicaría el archivo de bloqueo de dependencias, revisaría la auditoría y preguntaría al cliente por el formato permitido de las placas.

La principal conclusión es que la IA me ayudó a avanzar, pero la decisión final dependió de los requisitos y de las pruebas ejecutadas. Muchas gracias. Quedo atento a sus preguntas.

## Preguntas probables

**¿Qué hace la normalización de placa?** Quita espacios y caracteres invisibles al comienzo y al final, y convierte el texto a mayúsculas para comparar de forma consistente.

**¿Qué pasa si la placa ya existe?** El método lanza DatosInvalidosException con el mensaje definido y no sobrescribe el vehículo existente.

**¿Por qué no validaste año, marca y modelo?** Porque la HU-03 no define reglas para esos campos. Agregarlas sin consultar al cliente habría sido inventar requisitos.

**¿El sistema ya guarda en PostgreSQL?** No. Esta entrega es un componente en memoria. La persistencia aparece como etapa posterior del proyecto.

**¿Cómo sabes que las pruebas detectan errores?** Además de la suite base, ejecuté mutaciones temporales. Al invertir la condición de placa válida, PHPUnit reportó 12 errores y 5 fallos. Para las otras dos mutaciones solo registro el efecto reportado, no un conteo.

**¿La IA es responsable si produce un defecto?** No. El desarrollador debe revisar y validar el código. La IA es apoyo y sus sugerencias no reemplazan los requisitos ni las pruebas.
