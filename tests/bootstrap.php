<?php
// Carga las clases de src/ sin necesitar nada más (sirve con o sin composer)
spl_autoload_register(function (string $clase) {
    $prefijo = 'App\\';
    if (strpos($clase, $prefijo) === 0) {
        $archivo = __DIR__ . '/../src/' . substr($clase, strlen($prefijo)) . '.php';
        if (file_exists($archivo)) {
            require $archivo;
        }
    }
});
