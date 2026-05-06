<?php
/**
 * ============================================================
 * HANDLER: OBTENER DISPONIBILIDAD DE VACACIONES
 * ============================================================
 * 
 * URL: GET /logic/vacaciones/obtener_disponibilidad.php?id_contrato=123
 * 
 * Calcula cuántos días tiene disponible un trabajador:
 *   - Días totales (del contrato)
 *   - Días ya cogidos (de CON.WORKERHOLIDAYS)
 *   - Festivos en el período vigente
 * 
 * Disponibles = Totales - Ya_cogidos - Festivos
 * 
 * Se usa ANTES de guardar vacaciones para:
 *   1. Mostrar disponibilidad al usuario
 *   2. Validar que NO excedan límite
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
// 3. RECOPILAR PARÁMETROS
// ============================================================

$id_contrato = $_GET['id_contrato'] ?? '';

if (empty($id_contrato) || !Validador::validar_numero_positivo($id_contrato)) {
    json_respuesta_error("ID de contrato inválido");
}

$id_contrato = intval($id_contrato);

// ============================================================
// 4. OBTENER DATOS DEL CONTRATO
// ============================================================

try {
    // CONSULTA A CONVERTIR A SP: sp_obtener_contrato
    // Parámetros SP: $id_contrato (INT)
    // Retorna: Datos del contrato (fechas, días totales)
    
    $sql_contrato = "
        SELECT 
            c.idContract as id_contrato,
            c.idPerson as id_persona,
            c.from as fecha_inicio,
            c.to as fecha_fin,
            c.holidays as dias_vacaciones_total,
            c.ap as dias_moscosos_total
        FROM CON.CONTRACTS c
        WHERE c.idContract = ?
    ";
    
    $stmt_contrato = $conexion->prepare($sql_contrato);
    $stmt_contrato->execute([$id_contrato]);
    $contrato = $stmt_contrato->fetch(PDO::FETCH_ASSOC);
    
    if (!$contrato) {
        json_respuesta_error("Contrato no encontrado");
    }
    
    // ====================================================
    // 5. CONTAR DÍAS DE VACACIONES YA COGIDOS
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_contar_dias_cogidos
    // Parámetros SP: $id_contrato (INT), $tipo_vacaciones (VARCHAR)
    // Retorna: Número de días cogidos (contar registros)
    
    $sql_dias_cogidos = "
        SELECT COUNT(*) as dias_cogidos
        FROM CON.WORKERHOLIDAYS wh
        INNER JOIN CON.HOLIDAYTYPES ht ON wh.idHolidayType = ht.idHolidayType
        WHERE wh.idContract = ? AND ht.holidayType = 'vacaciones'
    ";
    
    $stmt_dias_cogidos = $conexion->prepare($sql_dias_cogidos);
    $stmt_dias_cogidos->execute([$id_contrato]);
    $resultado_dias_cogidos = $stmt_dias_cogidos->fetch(PDO::FETCH_ASSOC);
    $dias_vacaciones_cogidos = $resultado_dias_cogidos['dias_cogidos'] ?? 0;
    
    // ====================================================
    // 6. CONTAR DÍAS MOSCOSOS YA COGIDOS
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_contar_moscosos_cogidos
    // Parámetros SP: $id_contrato (INT)
    // Retorna: Número de moscosos cogidos
    
    $sql_moscosos_cogidos = "
        SELECT COUNT(*) as moscosos_cogidos
        FROM CON.WORKERHOLIDAYS wh
        INNER JOIN CON.HOLIDAYTYPES ht ON wh.idHolidayType = ht.idHolidayType
        WHERE wh.idContract = ? AND ht.holidayType = 'moscosos'
    ";
    
    $stmt_moscosos_cogidos = $conexion->prepare($sql_moscosos_cogidos);
    $stmt_moscosos_cogidos->execute([$id_contrato]);
    $resultado_moscosos_cogidos = $stmt_moscosos_cogidos->fetch(PDO::FETCH_ASSOC);
    $dias_moscosos_cogidos = $resultado_moscosos_cogidos['moscosos_cogidos'] ?? 0;
    
    // ====================================================
    // 7. OBTENER FESTIVOS EN EL RANGO
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_festivos_rango
    // Parámetros SP: $fecha_inicio (DATE), $fecha_fin (DATE)
    // Retorna: Lista de festivos en el rango
    
    $sql_festivos = "
        SELECT bankHoliday as festivo
        FROM CON.BANKHOLIDAYS
        WHERE bankHoliday >= ? AND bankHoliday <= ?
        ORDER BY bankHoliday ASC
    ";
    
    $stmt_festivos = $conexion->prepare($sql_festivos);
    $stmt_festivos->execute([$contrato['fecha_inicio'], $contrato['fecha_fin']]);
    $festivos = $stmt_festivos->fetchAll(PDO::FETCH_COLUMN);
    
    // ====================================================
    // 8. CALCULAR DISPONIBILIDAD
    // ====================================================
    
    $dias_vacaciones_disponibles = $contrato['dias_vacaciones_total'] - $dias_vacaciones_cogidos;
    $dias_moscosos_disponibles = $contrato['dias_moscosos_total'] - $dias_moscosos_cogidos;
    
    // ====================================================
    // 9. DEVOLVER RESPUESTA
    // ====================================================
    
    $datos_disponibilidad = [
        'id_contrato' => $id_contrato,
        'id_persona' => $contrato['id_persona'],
        'periodo' => [
            'desde' => $contrato['fecha_inicio'],
            'hasta' => $contrato['fecha_fin']
        ],
        'vacaciones' => [
            'total' => (int)$contrato['dias_vacaciones_total'],
            'cogidos' => (int)$dias_vacaciones_cogidos,
            'disponibles' => (int)$dias_vacaciones_disponibles
        ],
        'moscosos' => [
            'total' => (int)$contrato['dias_moscosos_total'],
            'cogidos' => (int)$dias_moscosos_cogidos,
            'disponibles' => (int)$dias_moscosos_disponibles
        ],
        'festivos_periodo' => $festivos,
        'total_festivos' => count($festivos)
    ];
    
    json_respuesta_exito($datos_disponibilidad, "Disponibilidad calculada");
    
} catch (PDOException $e) {
    error_log("Error calcular disponibilidad: " . $e->getMessage());
    json_respuesta_error("Error al calcular disponibilidad. Intente de nuevo.");
}

?>
