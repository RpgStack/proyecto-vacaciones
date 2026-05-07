/**
 * ============================================================
 * QUERIES DE COMPROBACIÓN - DATOS SEMI-REALES
 * ============================================================
 */

USE proyecto_vacaciones;

-- 1. RESUMEN DE DÍAS DISFRUTADOS POR TRABAJADOR (VIGENTES)
-- Muestra el conteo de vacaciones y moscosos de cada empleado con contrato activo.
SELECT 
    persons.perName as nombre,
    persons.surname as apellido,
    contracts.idContract as contrato,
    holidaytypes.holidayType as tipo,
    COUNT(workerholidays.holiday) as dias_consumidos
FROM persons
INNER JOIN contracts ON persons.idPerson = contracts.idPerson
INNER JOIN workerholidays ON contracts.idContract = workerholidays.idContract
INNER JOIN holidaytypes ON workerholidays.idHolidayType = holidaytypes.idHolidayType
WHERE contracts.`to` >= CURDATE()
GROUP BY persons.idPerson, contracts.idContract, holidaytypes.idHolidayType;


-- 2. HISTORIAL DE CONTRATOS DE UN TRABAJADOR ESPECÍFICO
-- Útil para ver la evolución de empleados como el ID 11 o 12 (fijos con pasado extinto).
SELECT 
    persons.perName as nombre,
    contracts.`from` as fecha_inicio,
    contracts.`to` as fecha_fin,
    contracts.holidays as cupo_vacaciones,
    IF(contracts.`to` >= CURDATE(), 'ACTIVO', 'EXTINTO') as estado
FROM contracts
INNER JOIN persons ON contracts.idPerson = persons.idPerson
WHERE persons.dni = '90123456A' -- Ejemplo con Raúl Jiménez
ORDER BY contracts.`from` ASC;


-- 3. DETALLE DE DÍAS PEDIDOS (CALENDARIO PERSONAL)
-- Lista cada fecha concreta solicitada por un trabajador.
SELECT 
    persons.perName as nombre,
    workerholidays.holiday as fecha_disfrutada,
    holidaytypes.holidayType as tipo_dia
FROM workerholidays
INNER JOIN contracts ON workerholidays.idContract = contracts.idContract
INNER JOIN persons ON contracts.idPerson = persons.idPerson
INNER JOIN holidaytypes ON workerholidays.idHolidayType = holidaytypes.idHolidayType
WHERE persons.idPerson = 11 -- Ejemplo con Raúl Jiménez
ORDER BY workerholidays.holiday DESC;


-- 4. VERIFICACIÓN DE FESTIVOS QUE CAEN EN FIN DE SEMANA
-- Ayuda a validar por qué algunos días no se cuentan como hábiles en el backend.
SELECT 
    bankHoliday as fecha,
    DAYNAME(bankHoliday) as dia_semana
FROM bankholidays
WHERE DAYOFWEEK(bankHoliday) IN (1, 7); -- 1 = Domingo, 7 = Sábado
