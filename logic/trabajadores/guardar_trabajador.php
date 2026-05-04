<?php
/**
 * ============================================================
 * HANDLER: GUARDAR NUEVO TRABAJADOR
 * ============================================================
 * 
 * URL: POST /logic/trabajadores/guardar_trabajador.php
 * 
 * Recibe datos del formulario de Raquel (alta-trabajador.php)
 * 
 * Inserta en 4 tablas:
 *   1. PER.PERSONS (datos base)
 *   2. PER.SECONDSURNAMES (apellido 2, si existe)
 *   3. PER.ADDRESSES (dirección completa)
 *   4. CON.CONTRACTS (contrato laboral)
 * 
 * Todo en UNA TRANSACCIÓN (si algo falla, rollback de todo)
 * ============================================================
 */

// ============================================================
// 1. INCLUIR ARCHIVOS NECESARIOS
// ============================================================

require_once '../../config/conexion.php';
require_once '../validation/Validador.php';
require_once '../utils/respuestas.php';

// ============================================================
// 2. VALIDAR QUE SEA POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_respuesta_error("Método HTTP no permitido. Use POST.", 405);
}

// ============================================================
// 3. RECOPILAR DATOS DEL FORMULARIO
// ============================================================

$nombre = $_POST['nombre'] ?? '';
$apellido1 = $_POST['apellido1'] ?? '';
$apellido2 = $_POST['apellido2'] ?? '';
$dni = $_POST['dni'] ?? '';
$genero = $_POST['genero'] ?? null;  // 0 = Hombre, 1 = Mujer
$email = $_POST['email'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$codigo_postal = $_POST['codigo_postal'] ?? '';
$municipio = $_POST['municipio'] ?? '';
$provincia = $_POST['provincia'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$dias_vacaciones = $_POST['dias_vacaciones'] ?? 22;
$dias_moscosos = $_POST['dias_moscosos'] ?? 6;

// ============================================================
// 4. VALIDACIONES (SERVIDOR - NO CONFIAR CLIENTE)
// ============================================================

$errores = [];

// Requeridos
if (!Validador::validar_requerido($nombre)) {
    $errores['nombre'] = "El nombre es requerido";
}
if (!Validador::validar_requerido($apellido1)) {
    $errores['apellido1'] = "El primer apellido es requerido";
}
if (!Validador::validar_requerido($dni)) {
    $errores['dni'] = "El DNI es requerido";
}

// Validar formato
if (!Validador::validar_dni($dni)) {
    $errores['dni'] = "Formato DNI inválido (ej: 12345678A)";
}
if (!empty($email) && !Validador::validar_email($email)) {
    $errores['email'] = "Email inválido";
}
if (!empty($telefono) && !Validador::validar_telefono($telefono)) {
    $errores['telefono'] = "Teléfono inválido (debe tener 9 dígitos)";
}

// Fechas válidas
if (!Validador::validar_fecha($fecha_inicio)) {
    $errores['fecha_inicio'] = "Fecha inicio inválida (formato: YYYY-MM-DD)";
}
if (!Validador::validar_fecha($fecha_fin)) {
    $errores['fecha_fin'] = "Fecha fin inválida (formato: YYYY-MM-DD)";
}

// Validar rango fechas
if (empty($errores['fecha_inicio']) && empty($errores['fecha_fin'])) {
    if (!Validador::validar_rango_fechas($fecha_inicio, $fecha_fin)) {
        $errores['fechas'] = "La fecha de inicio debe ser menor o igual a la fecha de fin";
    }
}

// Si hay errores de validación, devolver
if (!empty($errores)) {
    json_respuesta_validacion($errores);
}

// ============================================================
// 5. VERIFICAR DNI NO DUPLICADO EN BD
// ============================================================

try {
    // CONSULTA A CONVERTIR A SP: sp_verificar_dni_existe
    // Parámetros SP: $dni (VARCHAR)
    // Retorna: número de registros (debe ser 0)
    
    $sql_verificar = "SELECT COUNT(*) as cantidad FROM PER.PERSONS WHERE dni = ?";
    $stmt_verificar = $conexion->prepare($sql_verificar);
    $stmt_verificar->execute([$dni]);
    $resultado = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['cantidad'] > 0) {
        json_respuesta_error("El DNI ya existe en el sistema");
    }
    
} catch (PDOException $e) {
    error_log("Error verificar DNI: " . $e->getMessage());
    json_respuesta_error("Error al verificar DNI. Intente de nuevo.");
}

// ============================================================
// 6. INICIAR TRANSACCIÓN (todo o nada)
// ============================================================

try {
    $conexion->beginTransaction();
    
    // ====================================================
    // 6.1 INSERTAR EN PER.PERSONS
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_crear_persona
    // Parámetros SP: $nombre (VARCHAR), $apellido1 (VARCHAR), $dni (VARCHAR), $genero (BOOLEAN)
    // Retorna: id de la persona creada
    
    $sql_persona = "INSERT INTO PER.PERSONS (perName, surname, dni, woman) VALUES (?, ?, ?, ?)";
    $stmt_persona = $conexion->prepare($sql_persona);
    $stmt_persona->execute([
        $nombre,
        $apellido1,
        $dni,
        $genero === '1' ? 1 : 0
    ]);
    
    // Obtener ID de persona recién creada
    $id_persona = $conexion->lastInsertId();
    
    // ====================================================
    // 6.2 INSERTAR APELLIDO 2 (si existe)
    // ====================================================
    
    if (!empty($apellido2)) {
        // CONSULTA A CONVERTIR A SP: sp_crear_apellido_secundario
        // Parámetros SP: $id_persona (INT), $apellido2 (VARCHAR)
        // Retorna: void
        
        $sql_apellido2 = "INSERT INTO PER.SECONDSURNAMES (idPerson, surname) VALUES (?, ?)";
        $stmt_apellido2 = $conexion->prepare($sql_apellido2);
        $stmt_apellido2->execute([$id_persona, $apellido2]);
    }
    
    // ====================================================
    // 6.3 INSERTAR TELÉFONO (si existe)
    // ====================================================
    
    if (!empty($telefono)) {
        // CONSULTA A CONVERTIR A SP: sp_crear_telefono
        // Parámetros SP: $id_persona (INT), $telefono (VARCHAR)
        // Retorna: void
        
        $sql_telefono = "INSERT INTO PER.PHONES (idPerson, phone) VALUES (?, ?)";
        $stmt_telefono = $conexion->prepare($sql_telefono);
        $stmt_telefono->execute([$id_persona, $telefono]);
    }
    
    // ====================================================
    // 6.4 INSERTAR EMAIL (si existe)
    // ====================================================
    
    if (!empty($email)) {
        // CONSULTA A CONVERTIR A SP: sp_crear_email
        // Parámetros SP: $id_persona (INT), $email (VARCHAR)
        // Retorna: void
        
        $sql_email = "INSERT INTO PER.EMAILS (idPerson, email) VALUES (?, ?)";
        $stmt_email = $conexion->prepare($sql_email);
        $stmt_email->execute([$id_persona, $email]);
    }
    
    // ====================================================
    // 6.5 INSERTAR DIRECCIÓN (si existe)
    // ====================================================
    
    if (!empty($direccion)) {
        // CONSULTA A CONVERTIR A SP: sp_crear_direccion
        // Parámetros SP: $id_persona (INT), $direccion (VARCHAR), $codigo_postal (VARCHAR), 
        //               $municipio (VARCHAR), $provincia (VARCHAR)
        // Retorna: void
        
        $sql_direccion = "INSERT INTO PER.ADDRESSES (idPerson, address, zipCode, town, province) VALUES (?, ?, ?, ?, ?)";
        $stmt_direccion = $conexion->prepare($sql_direccion);
        $stmt_direccion->execute([$id_persona, $direccion, $codigo_postal, $municipio, $provincia]);
    }
    
    // ====================================================
    // 6.6 INSERTAR CONTRATO
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_crear_contrato
    // Parámetros SP: $id_persona (INT), $fecha_inicio (DATE), $fecha_fin (DATE),
    //               $dias_vacaciones (INT), $dias_moscosos (INT)
    // Retorna: id del contrato creado
    
    $sql_contrato = "INSERT INTO CON.CONTRACTS (idPerson, from, to, holidays, ap) VALUES (?, ?, ?, ?, ?)";
    $stmt_contrato = $conexion->prepare($sql_contrato);
    $stmt_contrato->execute([$id_persona, $fecha_inicio, $fecha_fin, $dias_vacaciones, $dias_moscosos]);
    
    $id_contrato = $conexion->lastInsertId();
    
    // ====================================================
    // 7. CONFIRMAR TRANSACCIÓN
    // ====================================================
    
    $conexion->commit();
    
    // Respuesta de éxito
    json_respuesta_exito([
        'id_persona' => $id_persona,
        'id_contrato' => $id_contrato,
        'nombre' => $nombre,
        'dni' => $dni
    ], "Trabajador creado correctamente");
    
} catch (PDOException $e) {
    // Si hay error, deshacer todo
    $conexion->rollBack();
    
    error_log("Error al guardar trabajador: " . $e->getMessage());
    json_respuesta_error("Error al guardar el trabajador. Intente de nuevo.");
}

?>
