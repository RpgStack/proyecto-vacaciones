<?php
/**
 * ============================================================
 * HANDLER: GUARDAR VACACIONES/MOSCOSOS
 * ============================================================
 * 
 * URL: POST /logic/vacaciones/guardar_vacaciones.php
 * 
 * ⚠️ HANDLER CRÍTICO - Aquí se hacen validaciones de seguridad
 * 
 * NO CONFIAR EN VALIDACIÓN CLIENTE (Raquel HTML5)
 * Todas las validaciones se hacen aquí en servidor:
 * 
 *   1. ¿Fechas válidas?
 *   2. ¿Moscosos NO están unidos?
 *   3. ¿NO excede límite de días?
 *   4. ¿NO se superpone con otras vacaciones?
 *   5. ¿Contrato vigente en esas fechas?
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
// 2. VALIDAR QUE SEA POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_respuesta_error("Método HTTP no permitido. Use POST.", 405);
}

// ============================================================
// 3. RECOPILAR DATOS DEL FORMULARIO
// ============================================================

$id_contrato = $_POST['id_contrato'] ?? '';
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$tipo_dia = $_POST['tipo_dia'] ?? ''; // vacaciones o moscosos
$notas = $_POST['notas'] ?? '';

// ============================================================
// 4. VALIDACIONES (SERVIDOR - CRÍTICO)
// ============================================================

$errores = [];

// ====================================================
// 4.1 Validar requeridos
// ====================================================

if (empty($id_contrato) || !Validador::validar_numero_positivo($id_contrato)) {
    $errores['id_contrato'] = "Contrato inválido";
}

if (!Validador::validar_fecha($fecha_inicio)) {
    $errores['fecha_inicio'] = "Fecha inicio inválida (YYYY-MM-DD)";
}

if (!Validador::validar_fecha($fecha_fin)) {
    $errores['fecha_fin'] = "Fecha fin inválida (YYYY-MM-DD)";
}

if (empty($tipo_dia) || !in_array($tipo_dia, ['vacaciones', 'moscosos'])) {
    $errores['tipo_dia'] = "Tipo de día inválido (debe ser 'vacaciones' o 'moscosos')";
}

// Si hay errores, devolver inmediatamente
if (!empty($errores)) {
    json_respuesta_validacion($errores);
}

// ====================================================
// 4.2 Validar rango de fechas
// ====================================================

if (!Validador::validar_rango_fechas($fecha_inicio, $fecha_fin)) {
    json_respuesta_error("La fecha de inicio debe ser menor o igual a la fecha de fin");
}

// ====================================================
// 4.3 Validar que no sean en pasado
// ====================================================

if (!Validador::validar_fecha_no_pasada($fecha_inicio)) {
    json_respuesta_error("La fecha de inicio no puede ser en el pasado");
}

// ============================================================
// 5. VALIDACIONES EN BD
// ============================================================

try {
    $id_contrato = intval($id_contrato);
    
    // ====================================================
    // 5.1 Verificar que contrato existe y es vigente
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_contrato
    // Parámetros SP: $id_contrato (INT)
    // Retorna: Datos contrato vigente
    
    $sql_contrato = "
        SELECT 
            c.idContract as id_contrato,
            c.idPerson as id_persona,
            c.from as fecha_inicio_contrato,
            c.to as fecha_fin_contrato,
            c.holidays as dias_vacaciones_total,
            c.ap as dias_moscosos_total
        FROM CONTRACTS c
        WHERE c.idContract = ? AND c.to >= CURDATE()
    ";
    
    $stmt_contrato = $conexion->prepare($sql_contrato);
    $stmt_contrato->execute([$id_contrato]);
    $contrato = $stmt_contrato->fetch(PDO::FETCH_ASSOC);
    
    if (!$contrato) {
        json_respuesta_error("Contrato no encontrado o no vigente");
    }
    
    // ====================================================
    // 5.2 Validar fechas dentro del contrato
    // ====================================================
    
    if (strtotime($fecha_inicio) < strtotime($contrato['fecha_inicio_contrato'])) {
        json_respuesta_error("La fecha de inicio es anterior al inicio del contrato");
    }
    
    if (strtotime($fecha_fin) > strtotime($contrato['fecha_fin_contrato'])) {
        json_respuesta_error("La fecha de fin es posterior al fin del contrato");
    }
    
    // ====================================================
    // 5.3 Calcular días solicitados (solo hábiles)
    // ====================================================
    
    $dias_solicitados = calcular_dias_habiles($fecha_inicio, $fecha_fin);
    
    if ($dias_solicitados <= 0) {
        json_respuesta_error("El rango de fechas no incluye días hábiles");
    }
    
    // ====================================================
    // 5.4 VALIDACIÓN CRÍTICA: ¿No excede límite?
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_contar_dias_cogidos
    // Parámetros SP: $id_contrato (INT), $tipo_dia (VARCHAR)
    // Retorna: Cantidad de días ya cogidos
    
    $sql_cogidos = "
        SELECT COUNT(*) as dias_cogidos
        FROM WORKERHOLIDAYS wh
        INNER JOIN HOLIDAYTYPES ht ON wh.idHolidayType = ht.idHolidayType
        WHERE wh.idContract = ? AND ht.holidayType = ?
    ";
    
    $stmt_cogidos = $conexion->prepare($sql_cogidos);
    $stmt_cogidos->execute([$id_contrato, $tipo_dia]);
    $resultado_cogidos = $stmt_cogidos->fetch(PDO::FETCH_ASSOC);
    $dias_cogidos = $resultado_cogidos['dias_cogidos'] ?? 0;
    
    // Determinar total según tipo
    $dias_total = ($tipo_dia === 'vacaciones') 
        ? $contrato['dias_vacaciones_total'] 
        : $contrato['dias_moscosos_total'];
    
    $dias_disponibles = $dias_total - $dias_cogidos;
    
    if ($dias_solicitados > $dias_disponibles) {
        json_respuesta_error(
            "No tienes suficientes días disponibles. Solicitado: {$dias_solicitados}, Disponibles: {$dias_disponibles}"
        );
    }
    
    // ====================================================
    // 5.5 VALIDACIÓN ESPECIAL: Moscosos NO unidos
    // ====================================================
    
    if ($tipo_dia === 'moscosos') {
        // CONSULTA A CONVERTIR A SP: sp_validar_moscosos_no_unidos
        // Parámetros SP: $id_contrato (INT), $fecha_inicio (DATE), $fecha_fin (DATE)
        // Retorna: Número de conflictos (debe ser 0)
        
        $sql_moscosos_unidos = "
            SELECT COUNT(*) as conflictos
            FROM WORKERHOLIDAYS wh
            INNER JOIN HOLIDAYTYPES ht ON wh.idHolidayType = ht.idHolidayType
            WHERE wh.idContract = ? 
            AND ht.holidayType = 'moscosos'
            AND (
                (DATE_SUB(wh.holiday, INTERVAL 1 DAY) = ? AND ? = wh.holiday)
                OR (wh.holiday = DATE_ADD(?, INTERVAL 1 DAY))
                OR (wh.holiday BETWEEN ? AND ?)
            )
        ";
        
        $stmt_moscosos = $conexion->prepare($sql_moscosos_unidos);
        $stmt_moscosos->execute([
            $id_contrato,
            $fecha_inicio, $fecha_inicio,
            $fecha_fin,
            $fecha_inicio, $fecha_fin
        ]);
        $resultado_moscosos = $stmt_moscosos->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado_moscosos['conflictos'] > 0) {
            json_respuesta_error("Los moscosos no pueden estar unidos a otras vacaciones o moscosos");
        }
    }
    
    // ====================================================
    // 5.6 VALIDACIÓN: No se superponen
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_validar_sin_solapamientos
    // Parámetros SP: $id_contrato (INT), $fecha_inicio (DATE), $fecha_fin (DATE)
    // Retorna: Número de conflictos (debe ser 0)
    
    $sql_solapamientos = "
        SELECT COUNT(*) as solapamientos
        FROM WORKERHOLIDAYS wh
        WHERE wh.idContract = ? 
        AND wh.holiday BETWEEN ? AND ?
    ";
    
    $stmt_solapamientos = $conexion->prepare($sql_solapamientos);
    $stmt_solapamientos->execute([$id_contrato, $fecha_inicio, $fecha_fin]);
    $resultado_solapamientos = $stmt_solapamientos->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado_solapamientos['solapamientos'] > 0) {
        json_respuesta_error("Ya tienes días solicitados en este rango");
    }
    
    // ====================================================
    // 6. OBTENER ID DEL TIPO DE DÍA
    // ====================================================
    
    // CONSULTA A CONVERTIR A SP: sp_obtener_id_tipo_dia
    // Parámetros SP: $tipo_dia (VARCHAR)
    // Retorna: idHolidayType
    
    $sql_tipo = "
        SELECT idHolidayType as id_tipo
        FROM HOLIDAYTYPES
        WHERE holidayType = ?
    ";
    
    $stmt_tipo = $conexion->prepare($sql_tipo);
    $stmt_tipo->execute([$tipo_dia]);
    $resultado_tipo = $stmt_tipo->fetch(PDO::FETCH_ASSOC);
    
    if (!$resultado_tipo) {
        json_respuesta_error("Tipo de día no reconocido");
    }
    
    $id_tipo_dia = $resultado_tipo['id_tipo'];
    
    // ============================================================
    // 7. INSERTAR REGISTRO DE VACACIONES (para cada día)
    // ============================================================
    
    // Generar array de fechas entre inicio y fin
    $fechas = generar_rango_fechas($fecha_inicio, $fecha_fin);
    
    $conexion->beginTransaction();
    
    try {
        foreach ($fechas as $fecha) {
            // CONSULTA A CONVERTIR A SP: sp_crear_vacacion
            // Parámetros SP: $id_contrato (INT), $id_tipo_dia (INT), $fecha (DATE), $notas (TEXT)
            // Retorna: id del registro creado
            
            $sql_insertar = "
                INSERT INTO WORKERHOLIDAYS (idContract, idHolidayType, holiday, notes)
                VALUES (?, ?, ?, ?)
            ";
            
            $stmt_insertar = $conexion->prepare($sql_insertar);
            $stmt_insertar->execute([$id_contrato, $id_tipo_dia, $fecha, $notas]);
        }
        
        $conexion->commit();
        
        json_respuesta_exito([
            'id_contrato' => $id_contrato,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'tipo_dia' => $tipo_dia,
            'dias_registrados' => count($fechas)
        ], "Vacaciones registradas correctamente");
        
    } catch (PDOException $e) {
        $conexion->rollBack();
        error_log("Error insertar vacaciones: " . $e->getMessage());
        json_respuesta_error("Error al registrar vacaciones. Intente de nuevo.");
    }
    
} catch (PDOException $e) {
    error_log("Error guardar vacaciones: " . $e->getMessage());
    json_respuesta_error("Error al guardar vacaciones. Intente de nuevo.");
}

// ============================================================
// FUNCIONES AUXILIARES
// ============================================================

/**
 * Calcula días hábiles entre dos fechas
 * Excluye sábados, domingos y festivos
 * 
 * @param string $fecha_inicio - YYYY-MM-DD
 * @param string $fecha_fin - YYYY-MM-DD
 * @return int - Número de días hábiles
 */
function calcular_dias_habiles($fecha_inicio, $fecha_fin) {
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $dias_habiles = 0;
    
    while ($inicio <= $fin) {
        // 0 = domingo, 6 = sábado
        if ($inicio->format('w') !== '0' && $inicio->format('w') !== '6') {
            $dias_habiles++;
        }
        $inicio->modify('+1 day');
    }
    
    return $dias_habiles;
}

/**
 * Genera array de todas las fechas en un rango
 * 
 * @param string $fecha_inicio - YYYY-MM-DD
 * @param string $fecha_fin - YYYY-MM-DD
 * @return array - Array de fechas
 */
function generar_rango_fechas($fecha_inicio, $fecha_fin) {
    $fechas = [];
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    
    while ($inicio <= $fin) {
        // Solo hábiles (no sábados, no domingos)
        if ($inicio->format('w') !== '0' && $inicio->format('w') !== '6') {
            $fechas[] = $inicio->format('Y-m-d');
        }
        $inicio->modify('+1 day');
    }
    
    return $fechas;
}

?>
