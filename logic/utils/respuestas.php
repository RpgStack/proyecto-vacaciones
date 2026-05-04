<?php
/**
 * ============================================================
 * FUNCIONES AUXILIARES - RESPUESTAS JSON
 * ============================================================
 * 
 * Centralizar formato de respuestas JSON para que:
 * 1. Todas las respuestas sean consistentes
 * 2. Raquel (frontend) siempre sepa qué estructura esperar
 * 3. El código sea más legible
 * 
 * ============================================================
 */

// ============================================================
// 1. RESPUESTA ÉXITO
// ============================================================

/**
 * Devuelve respuesta JSON de éxito
 * 
 * @param mixed $datos - Datos a devolver (array, string, número, etc)
 * @param string $mensaje - Mensaje adicional (opcional)
 * 
 * Ejemplo:
 *   json_respuesta_exito(['id' => 123, 'nombre' => 'Juan']);
 *   // Devuelve: {"éxito":true,"datos":{...},"mensaje":null}
 */
function json_respuesta_exito($datos = null, $mensaje = null) {
    header('Content-Type: application/json; charset=utf-8');
    
    $respuesta = [
        'éxito' => true,
        'datos' => $datos,
        'mensaje' => $mensaje,
        'error' => null
    ];
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// 2. RESPUESTA ERROR
// ============================================================

/**
 * Devuelve respuesta JSON de error
 * 
 * @param string $mensaje - Mensaje de error (el usuario lo verá)
 * @param int $codigo_http - Código HTTP (default: 400)
 * 
 * Ejemplo:
 *   json_respuesta_error("DNI duplicado en la base de datos");
 *   // Devuelve: {"éxito":false,"error":"DNI duplicado...","datos":null}
 * 
 * ⚠️ NUNCA incluir mensajes técnicos de BD (SQL, excepciones internas)
 */
function json_respuesta_error($mensaje, $codigo_http = 400) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code($codigo_http);
    
    $respuesta = [
        'éxito' => false,
        'error' => $mensaje,
        'datos' => null,
        'mensaje' => null
    ];
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// 3. RESPUESTA VALIDACIÓN (Error de entrada)
// ============================================================

/**
 * Devuelve respuesta JSON para errores de validación
 * 
 * @param array $errores - Array con mensajes de error
 *   Ejemplo: ['dni' => 'DNI inválido', 'email' => 'Email incorrecto']
 * 
 * Ejemplo:
 *   json_respuesta_validacion(['dni' => 'Formato inválido']);
 */
function json_respuesta_validacion($errores) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(422); // 422 = Unprocessable Entity
    
    $respuesta = [
        'éxito' => false,
        'error' => 'Errores de validación',
        'validacion' => $errores,
        'datos' => null
    ];
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================================
// 4. FUNCIONES AUXILIARES
// ============================================================

/**
 * Sanitizar entrada (prevenir XSS en JSON)
 * 
 * @param mixed $entrada - Valor a sanitizar
 * @return mixed - Valor sanitizado
 */
function sanitizar($entrada) {
    if (is_array($entrada)) {
        return array_map('sanitizar', $entrada);
    }
    return htmlspecialchars($entrada, ENT_QUOTES, 'UTF-8');
}

/**
 * Validar que una variable POST existe y no está vacía
 * 
 * @param string $nombre - Nombre de variable POST
 * @return bool
 */
function existe_post($nombre) {
    return isset($_POST[$nombre]) && !empty(trim($_POST[$nombre]));
}

/**
 * Validar que una variable GET existe y no está vacía
 * 
 * @param string $nombre - Nombre de variable GET
 * @return bool
 */
function existe_get($nombre) {
    return isset($_GET[$nombre]) && !empty(trim($_GET[$nombre]));
}

?>
