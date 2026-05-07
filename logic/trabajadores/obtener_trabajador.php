<?php
/**
 * ============================================================
 * HANDLER: OBTENER DATOS TRABAJADOR
 * ============================================================
 * 
 * URL: GET /logic/trabajadores/obtener_trabajador.php?id=5
 * 
 * Devuelve datos completos de un trabajador:
 *   - Datos personales (nombre, apellidos, DNI, email, teléfono)
 *   - Dirección completa
 *   - Contrato vigente (fechas, días de vacaciones)
 * 
 * Se usa cuando:
 *   1. Selecciona un trabajador en la tabla
 *   2. Quiere ver detalles antes de registrar vacaciones
 * 
 * ============================================================
 */

// ============================================================
// 1. INCLUIR ARCHIVOS NECESARIOS
// ============================================================

require_once '../../config/conexion.php';
require_once '../validation/Validador.php';
require_once '../utils/respuestas.php';

// ============================================================
// 2. VALIDAR QUE SEA GET
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_respuesta_error("Método HTTP no permitido. Use GET.", 405);
}

// ============================================================
// 3. RECOPILAR ID TRABAJADOR
// ============================================================

$id_persona = $_GET['id'] ?? '';

// Validar que ID sea válido (número positivo)
if (empty($id_persona) || !Validador::validar_numero_positivo($id_persona)) {
    json_respuesta_error("ID de trabajador inválido");
}

$id_persona = intval($id_persona);

// ============================================================
// 4. OBTENER DATOS DEL TRABAJADOR
// ============================================================

try {
    // ====================================================
    // 4.1 DATOS PERSONALES
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_persona
    // Parámetros SP: $id_persona (INT)
    // Retorna: Datos personales de la persona
    
    $sql_persona = "
        SELECT 
            p.idPerson as id,
            p.perName as nombre,
            p.surname as apellido1,
            p.dni as dni,
            p.woman as genero
        FROM PERSONS p
        WHERE p.idPerson = ?
    ";
    
    $stmt_persona = $conexion->prepare($sql_persona);
    $stmt_persona->execute([$id_persona]);
    $persona = $stmt_persona->fetch(PDO::FETCH_ASSOC);
    
    if (!$persona) {
        json_respuesta_error("Trabajador no encontrado");
    }
    
    // ====================================================
    // 4.2 SEGUNDO APELLIDO
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_apellido_secundario
    // Parámetros SP: $id_persona (INT)
    // Retorna: Segundo apellido (NULL si no existe)
    
    $sql_apellido2 = "
        SELECT surname as apellido2 
        FROM SECONDSURNAMES 
        WHERE idPerson = ? 
        LIMIT 1
    ";
    
    $stmt_apellido2 = $conexion->prepare($sql_apellido2);
    $stmt_apellido2->execute([$id_persona]);
    $resultado_apellido2 = $stmt_apellido2->fetch(PDO::FETCH_ASSOC);
    $persona['apellido2'] = $resultado_apellido2['apellido2'] ?? null;
    
    // ====================================================
    // 4.3 TELÉFONO
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_telefonos
    // Parámetros SP: $id_persona (INT)
    // Retorna: Array de teléfonos
    
    $sql_telefono = "
        SELECT phone as telefono 
        FROM PHONES 
        WHERE idPerson = ? 
        LIMIT 1
    ";
    
    $stmt_telefono = $conexion->prepare($sql_telefono);
    $stmt_telefono->execute([$id_persona]);
    $resultado_telefono = $stmt_telefono->fetch(PDO::FETCH_ASSOC);
    $persona['telefono'] = $resultado_telefono['telefono'] ?? null;
    
    // ====================================================
    // 4.4 EMAIL
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_emails
    // Parámetros SP: $id_persona (INT)
    // Retorna: Array de emails
    
    $sql_email = "
        SELECT email 
        FROM EMAILS 
        WHERE idPerson = ? 
        LIMIT 1
    ";
    
    $stmt_email = $conexion->prepare($sql_email);
    $stmt_email->execute([$id_persona]);
    $resultado_email = $stmt_email->fetch(PDO::FETCH_ASSOC);
    $persona['email'] = $resultado_email['email'] ?? null;
    
    // ====================================================
    // 4.5 DIRECCIÓN
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_direcciones
    // Parámetros SP: $id_persona (INT)
    // Retorna: Array de direcciones
    
    $sql_direccion = "
        SELECT 
            address as direccion,
            zipCode as codigo_postal,
            town as municipio,
            province as provincia
        FROM ADDRESSES 
        WHERE idPerson = ? 
        LIMIT 1
    ";
    
    $stmt_direccion = $conexion->prepare($sql_direccion);
    $stmt_direccion->execute([$id_persona]);
    $resultado_direccion = $stmt_direccion->fetch(PDO::FETCH_ASSOC);
    $persona['direccion'] = $resultado_direccion['direccion'] ?? null;
    $persona['codigo_postal'] = $resultado_direccion['codigo_postal'] ?? null;
    $persona['municipio'] = $resultado_direccion['municipio'] ?? null;
    $persona['provincia'] = $resultado_direccion['provincia'] ?? null;
    
    // ====================================================
    // 4.6 CONTRATO VIGENTE
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_contrato_vigente
    // Parámetros SP: $id_persona (INT)
    // Retorna: Datos del contrato activo (si existe)
    
    $sql_contrato = "
        SELECT 
            idContract as id_contrato,
            from as fecha_inicio,
            to as fecha_fin,
            holidays as dias_vacaciones,
            ap as dias_moscosos
        FROM CONTRACTS 
        WHERE idPerson = ? AND to >= CURDATE()
        LIMIT 1
    ";
    
    $stmt_contrato = $conexion->prepare($sql_contrato);
    $stmt_contrato->execute([$id_persona]);
    $contrato = $stmt_contrato->fetch(PDO::FETCH_ASSOC);
    
    if (!$contrato) {
        $persona['contrato'] = null;
        $persona['estado'] = 'Sin contrato vigente';
    } else {
        $persona['contrato'] = $contrato;
        $persona['estado'] = 'Activo';
    }
    
    // ====================================================
    // 5. DEVOLVER DATOS
    // ====================================================
    
    json_respuesta_exito($persona, "Datos del trabajador obtenidos");
    
} catch (PDOException $e) {
    error_log("Error obtener trabajador: " . $e->getMessage());
    json_respuesta_error("Error al obtener datos del trabajador. Intente de nuevo.");
}

?>
