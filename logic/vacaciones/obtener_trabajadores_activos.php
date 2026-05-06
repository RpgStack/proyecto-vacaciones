<?php
/**
 * ============================================================
 * HANDLER: OBTENER TRABAJADORES ACTIVOS
 * ============================================================
 * 
 * URL: GET /logic/vacaciones/obtener_trabajadores_activos.php
 * 
 * Devuelve lista de trabajadores con contrato vigente
 * Se usa para llenar el dropdown/select al registrar vacaciones
 * 
 * ============================================================
 */

// ============================================================
// 1. INCLUIR ARCHIVOS NECESARIOS
// ============================================================

require_once '../../config/conexion.php';
require_once '../utils/respuestas.php';

// ============================================================
// 2. VALIDAR QUE SEA GET
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_respuesta_error("Método HTTP no permitido. Use GET.", 405);
}

// ============================================================
// 3. OBTENER TRABAJADORES ACTIVOS
// ============================================================

try {
    // CONSULTA A CONVERTIR A SP: sp_obtener_trabajadores_activos
    // Parámetros SP: ninguno
    // Retorna: Lista de trabajadores con contrato vigente hoy
    
    $sql = "
        SELECT DISTINCT
            p.idPerson as id,
            p.perName as nombre,
            p.surname as apellido1,
            COALESCE(ps.surname, '') as apellido2,
            p.dni as dni,
            c.idContract as id_contrato,
            c.from as fecha_inicio,
            c.to as fecha_fin,
            c.holidays as dias_vacaciones,
            c.ap as dias_moscosos
        FROM PER.PERSONS p
        INNER JOIN CON.CONTRACTS c ON p.idPerson = c.idPerson
        LEFT JOIN PER.SECONDSURNAMES ps ON p.idPerson = ps.idPerson
        WHERE c.to >= CURDATE()
        ORDER BY p.perName ASC
    ";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $trabajadores = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay trabajadores activos
    if (empty($trabajadores)) {
        json_respuesta_exito([], "No hay trabajadores con contrato vigente");
    }
    
    // Respuesta con lista
    json_respuesta_exito($trabajadores, "Lista de trabajadores activos");
    
} catch (PDOException $e) {
    error_log("Error obtener trabajadores activos: " . $e->getMessage());
    json_respuesta_error("Error al obtener trabajadores. Intente de nuevo.");
}

?>
