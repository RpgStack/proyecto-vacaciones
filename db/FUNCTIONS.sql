/*
 * ============================================================
 * STORED PROCEDURES Y FUNCIONES - SISTEMA GESTIÓN VACACIONES
 * ============================================================
 * Ejecutar este archivo después de DDL.sql
 * ============================================================
 */


DELIMITER //

-- 1.Validación de DNI. Cumplimiento mod(23)
CREATE FUNCTION fn_validar_dni(p_dni VARCHAR(9)) 
    RETURNS BOOLEAN
    DETERMINISTIC
    BEGIN
        DECLARE v_dni_num VARCHAR(9);
        DECLARE v_letra_calculada CHAR(1);
        DECLARE v_letras_control CHAR(23) DEFAULT 'TRWAGMYFPDXBNJZSQVHLCKE';

        -- 1. Formato básico (Regex)
        IF p_dni NOT REGEXP '^[0-9XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$' THEN
            RETURN FALSE;
        END IF;

        -- 2. Transformación de NIE (X=0, Y=1, Z=2)
        SET v_dni_num = CASE 
            WHEN p_dni LIKE 'X%' THEN REPLACE(p_dni, 'X', '0')
            WHEN p_dni LIKE 'Y%' THEN REPLACE(p_dni, 'Y', '1')
            WHEN p_dni LIKE 'Z%' THEN REPLACE(p_dni, 'Z', '2')
            ELSE p_dni
        END;

        -- 3. Cálculo del algoritmo Módulo 23
        SET v_letra_calculada = SUBSTRING(v_letras_control, (CAST(SUBSTRING(v_dni_num, 1, 8) AS UNSIGNED) % 23) + 1, 1);

        -- 4. Comparación final
        RETURN (v_letra_calculada = SUBSTRING(p_dni, 9, 1));
    END //

-- 2. Validación de formato de textos (nombres, apellido1, apellido2, población y provincia)
CREATE FUNCTION fn_validar_texto(p_texto VARCHAR(100)) 
    RETURNS BOOLEAN
    DETERMINISTIC
    BEGIN
        RETURN (p_texto REGEXP '^[[:alpha:][:space:]áéíóúÁÉÍÓÚñÑüÜ\' \'-]+$');
    END //

DELIMITER ;