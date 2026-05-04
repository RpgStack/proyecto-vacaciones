<?php
/**
 * ============================================================
 * CLASE: VALIDADOR
 * ============================================================
 * 
 * Centraliza todas las validaciones de datos de entrada.
 * 
 * Ventajas:
 * 1. Una sola fuente de validación (no repetir código)
 * 2. Fácil de mantener y modificar reglas
 * 3. Métodos estáticos (no necesita instanciar)
 * 
 * Uso:
 *   if (!Validador::validar_dni($dni)) { ... }
 *   if (!Validador::validar_email($email)) { ... }
 * 
 * ============================================================
 */

class Validador {
    
    // ========================================================
    // 1. VALIDACIÓN DNI/NIE
    // ========================================================
    
    /**
     * Valida formato y dígito de control del DNI/NIE español
     * 
     * @param string $dni - DNI a validar (ej: "12345678A")
     * @return bool - true si es válido
     * 
     * Regla: 8 números + 1 letra
     *        La letra debe coincidir con número % 23
     */
    public static function validar_dni($dni) {
        // Limpiar espacios
        $dni = strtoupper(trim($dni));
        
        // Expresión regular: 8 dígitos + 1 letra (o X, Y, Z para NIE)
        if (!preg_match('/^[0-9]{8}[A-Z]$/', $dni)) {
            return false;
        }
        
        // Tabla de letras para validar dígito de control
        $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
        
        // Extraer número (primeros 8 dígitos)
        $numero = substr($dni, 0, 8);
        
        // Extraer letra (última posición)
        $letra = substr($dni, 8, 1);
        
        // Calcular letra correcta: número % 23
        $letra_correcta = $letras[$numero % 23];
        
        // Verificar que la letra coincida
        return $letra === $letra_correcta;
    }
    
    // ========================================================
    // 2. VALIDACIÓN EMAIL
    // ========================================================
    
    /**
     * Valida formato de email
     * 
     * @param string $email - Email a validar
     * @return bool - true si es válido
     */
    public static function validar_email($email) {
        $email = trim($email);
        
        // Usar filtro de PHP (standard de validación)
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    // ========================================================
    // 3. VALIDACIÓN TELÉFONO
    // ========================================================
    
    /**
     * Valida teléfono español (9 dígitos)
     * 
     * @param string $telefono - Teléfono a validar
     * @return bool - true si es válido
     * 
     * Acepta: 123456789, 12 3456789, +34123456789, etc
     */
    public static function validar_telefono($telefono) {
        $telefono = preg_replace('/[^\d+]/', '', $telefono); // Quitar espacios, guiones, etc
        
        // Aceptar: 9 dígitos, o +34 + 9 dígitos
        return preg_match('/^(\+34|0034|0)?\d{9}$/', $telefono) === 1;
    }
    
    // ========================================================
    // 4. VALIDACIÓN FECHAS
    // ========================================================
    
    /**
     * Valida que una fecha sea válida (formato YYYY-MM-DD)
     * 
     * @param string $fecha - Fecha a validar
     * @return bool - true si es válida y es una fecha real
     */
    public static function validar_fecha($fecha) {
        $fecha = trim($fecha);
        
        // Expresión regular: YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return false;
        }
        
        // Extraer año, mes, día
        list($ano, $mes, $dia) = explode('-', $fecha);
        
        // Verificar que sea una fecha real (ej: 31/02 no existe)
        return checkdate($mes, $dia, $ano);
    }
    
    /**
     * Valida que una fecha NO sea en el pasado
     * 
     * @param string $fecha - Fecha a validar (YYYY-MM-DD)
     * @return bool - true si es hoy o futuro
     */
    public static function validar_fecha_no_pasada($fecha) {
        if (!self::validar_fecha($fecha)) {
            return false;
        }
        
        $fecha_obj = new DateTime($fecha);
        $hoy = new DateTime('today');
        
        return $fecha_obj >= $hoy;
    }
    
    /**
     * Valida que fecha_inicio <= fecha_fin
     * 
     * @param string $fecha_inicio - Fecha inicio
     * @param string $fecha_fin - Fecha fin
     * @return bool - true si inicio <= fin
     */
    public static function validar_rango_fechas($fecha_inicio, $fecha_fin) {
        if (!self::validar_fecha($fecha_inicio) || !self::validar_fecha($fecha_fin)) {
            return false;
        }
        
        return strtotime($fecha_inicio) <= strtotime($fecha_fin);
    }
    
    // ========================================================
    // 5. VALIDACIÓN CAMPOS DE TEXTO
    // ========================================================
    
    /**
     * Valida que un texto NO esté vacío
     * 
     * @param string $texto - Texto a validar
     * @return bool - true si no está vacío
     */
    public static function validar_requerido($texto) {
        return !empty(trim($texto));
    }
    
    /**
     * Valida longitud mínima y máxima
     * 
     * @param string $texto - Texto a validar
     * @param int $minimo - Longitud mínima
     * @param int $maximo - Longitud máxima
     * @return bool - true si cumple
     */
    public static function validar_longitud($texto, $minimo = 0, $maximo = 255) {
        $longitud = strlen(trim($texto));
        return $longitud >= $minimo && $longitud <= $maximo;
    }
    
    /**
     * Valida que sea solo letras y espacios
     * 
     * @param string $texto - Texto a validar
     * @return bool - true si solo tiene letras y espacios
     */
    public static function validar_solo_letras($texto) {
        return preg_match('/^[a-záéíóúñ\s]+$/i', trim($texto)) === 1;
    }
    
    // ========================================================
    // 6. VALIDACIÓN NÚMEROS
    // ========================================================
    
    /**
     * Valida que sea un número entero positivo
     * 
     * @param mixed $numero - Número a validar
     * @param int $minimo - Mínimo (default: 0)
     * @return bool - true si es válido
     */
    public static function validar_numero_positivo($numero, $minimo = 0) {
        if (!is_numeric($numero)) {
            return false;
        }
        
        $numero = intval($numero);
        return $numero >= $minimo;
    }
    
    // ========================================================
    // 7. SANITIZACIÓN
    // ========================================================
    
    /**
     * Sanitiza un string (elimina caracteres peligrosos)
     * 
     * @param string $texto - Texto a sanitizar
     * @return string - Texto sanitizado
     */
    public static function sanitizar_texto($texto) {
        $texto = trim($texto);
        $texto = htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
        return $texto;
    }
    
    /**
     * Sanitiza para consultas SQL (aunque usamos prepared statements)
     * 
     * @param string $texto - Texto a sanitizar
     * @return string - Texto sanitizado
     */
    public static function sanitizar_sql($texto) {
        // Nota: Preferimos prepared statements en lugar de sanitizar
        // Pero como medida de defensa en profundidad:
        return addslashes(trim($texto));
    }
}

?>
