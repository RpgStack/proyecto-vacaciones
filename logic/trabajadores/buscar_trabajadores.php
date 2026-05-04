<?php
/**
 * ============================================================
 * HANDLER: BUSCAR TRABAJADORES
 * ============================================================
 * 
 * URL: GET /logic/trabajadores/buscar_trabajadores.php?q=Juan
 * 
 * Busca trabajadores por:
 *   - Nombre (LIKE búsqueda parcial)
 *   - DNI (LIKE búsqueda parcial)
 * 
 * Devuelve lista JSON para mostrar en tabla dinámica
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
// 3. RECOPILAR PARÁMETRO DE BÚSQUEDA
// ============================================================

$busqueda = $_GET['q'] ?? '';

// Si búsqueda está vacía, devolver lista completa o error
if (empty(trim($busqueda))) {
    json_respuesta_error("Ingrese un término de búsqueda");
}

// Sanitizar búsqueda (aunque usemos prepared statements)
$busqueda = Validador::sanitizar_texto($busqueda);

// ============================================================
// 4. BUSCAR EN BD
// ============================================================

try {
    // CONSULTA A CONVERTIR A SP: sp_buscar_trabajadores
    // Parámetros SP: $termino (VARCHAR)
    // Retorna: Array con resultados (id, nombre, dni, estado)
    // 
    // Nota: El % en la búsqueda LIKE es para coincidencias parciales
    //       Ej: "Juan" coincide con "Juan García", "Juanito", etc
    
    $sql = "
        SELECT 
            p.idPerson as id,
            p.perName as nombre,
            COALESCE(ps.surname, '') as apellido2,
            p.surname as apellido1,
            p.dni as dni,
            p.woman as genero,
            CASE WHEN c.to >= CURDATE() THEN 'Activo' ELSE 'Inactivo' END as estado,
            c.idContract as id_contrato,
            c.from as fecha_inicio,
            c.to as fecha_fin,
            c.holidays as dias_vacaciones
        FROM PER.PERSONS p
        LEFT JOIN PER.SECONDSURNAMES ps ON p.idPerson = ps.idPerson
        LEFT JOIN CON.CONTRACTS c ON p.idPerson = c.idPerson AND c.to >= CURDATE()
        WHERE 
            p.perName LIKE ? 
            OR p.dni LIKE ?
            OR p.surname LIKE ?
        ORDER BY p.perName ASC
        LIMIT 20
    ";
    
    $stmt = $conexion->prepare($sql);
    $busqueda_like = "%{$busqueda}%";
    $stmt->execute([$busqueda_like, $busqueda_like, $busqueda_like]);
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay resultados
    if (empty($resultados)) {
        json_respuesta_exito([], "No se encontraron resultados");
    }
    
    // Respuesta con resultados
    json_respuesta_exito($resultados, "Búsqueda completada");
    
} catch (PDOException $e) {
    error_log("Error en búsqueda: " . $e->getMessage());
    json_respuesta_error("Error al buscar trabajadores. Intente de nuevo.");
}

?>
