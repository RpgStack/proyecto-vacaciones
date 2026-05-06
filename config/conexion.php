<?php
/**
 * ============================================================
 * CONFIGURACIÓN DE CONEXIÓN A BASE DE DATOS
 * ============================================================
 * 
 * Este archivo configura la conexión PDO a MariaDB
 * Es el punto central para acceder a la base de datos
 * 
 * ⚠️ IMPORTANTE: Coordinar con David los parámetros reales
 * ============================================================
 */

// ============================================================
// 1. CONFIGURAR CREDENCIALES (Cambiar con valores reales)
// ============================================================

$config = [
    'host' => 'localhost',           // Servidor BD (ej: localhost, 192.168.x.x)
    'puerto' => 3306,                // Puerto MySQL/MariaDB (default: 3306)
    'base_datos' => 'proyecto_vacaciones',  // Nombre BD
    'usuario' => 'root',             // Usuario BD
    'contraseña' => '',              // Contraseña BD
    'charset' => 'utf8mb4'           // Codificación caracteres
];

// ============================================================
// 2. CONECTAR CON PDO
// ============================================================

try {
    // Construir DSN (Data Source Name) para PDO
    $dsn = "mysql:host={$config['host']};port={$config['puerto']};dbname={$config['base_datos']};charset={$config['charset']}";
    
    // Crear instancia PDO
    $conexion = new PDO(
        $dsn,
        $config['usuario'],
        $config['contraseña'],
        [
            // Opciones de seguridad y comportamiento
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,     // Lanzar excepciones en errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Devolver arrays asociativos
            PDO::ATTR_EMULATE_PREPARES => false              // No emular prepared statements
        ]
    );
    
} catch (PDOException $e) {
    // Si hay error de conexión, mostrar mensaje (SOLO en desarrollo)
    // En producción, registrar en log y mostrar mensaje genérico
    die("❌ Error de conexión a BD: " . $e->getMessage());
}

/**
 * NOTAS PARA DAVID:
 * 
 * 1. Script SQL crear tablas:
 *    - Si no existen, enviar script CREATE TABLE para cada tabla
 *    - Verificar nombres exactos: PER.PERSONS, CON.CONTRACTS, etc
 * 
 * 2. Usuarios/Permisos:
 *    - El usuario debe tener SELECT, INSERT, UPDATE, DELETE
 *    - Si usa SPs después, necesitará EXECUTE
 * 
 * 3. Testing conexión:
 *    - Descomentar última línea para verificar conexión
 *    - echo "✓ Conectado correctamente"; exit;
 */

// DESCOMENTAR PARA TESTEAR CONEXIÓN:
// echo "✓ Conectado correctamente a: " . $config['base_datos'];
// exit;

?>
