/*
 * ============================================================
 * STORED PROCEDURES Y FUNCIONES - SISTEMA GESTIÓN VACACIONES
 * ============================================================
 * Ejecutar este archivo después de FUNCTIONS.sql
 * ============================================================
 */


DELIMITER //

/* --- MÓDULO TRABAJADORES --- */

-- 1. Verificar si DNI existe
CREATE PROCEDURE sp_verificar_dni_existe(
    IN p_dni VARCHAR(9)
    )
    BEGIN
        SELECT COUNT(*) as cantidad FROM persons WHERE dni = p_dni;
    END //

-- 2. Crear persona base
CREATE PROCEDURE sp_crear_persona(
    IN p_nombre VARCHAR(100), 
    IN p_apellido1 VARCHAR(100), 
    IN p_dni VARCHAR(9), 
    IN p_genero TINYINT
    )
    BEGIN
        -- 1. NORMALIZACIÓN (Eliminar espacios y asegurar mayúsculas en DNI)
        SET p_nombre = TRIM(p_nombre);
        SET p_apellido1 = TRIM(p_apellido1);
        SET p_dni = UPPER(TRIM(p_dni));

        -- 2. VALIDACIÓN MEDIANTE FUNCIONES
        -- Validación DNI (Matemática + Regex)
        IF NOT fn_validar_dni(p_dni) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: DNI/NIE no válido o mal formado';
        END IF;

        -- Validación de formato de texto
        IF NOT fn_validar_texto(p_nombre) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El nombre contiene caracteres no permitidos';
        END IF;

        IF NOT fn_validar_texto(p_apellido1) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El primer apellido contiene caracteres no permitidos';
        END IF;

        -- 3. INSERCIÓN
        INSERT INTO persons (perName, surname, dni, woman) 
        VALUES (p_nombre, p_apellido1, p_dni, p_genero);
        
        -- 4. CAPTURA DE ID
        SELECT LAST_INSERT_ID() as id_persona;
    END //

-- 3. Crear apellido secundario
CREATE PROCEDURE sp_crear_apellido_secundario(
        IN p_id_persona INT, 
        IN p_apellido2 VARCHAR(100)
    )
    BEGIN
        -- 1. NORMALIZACIÓN
        SET p_apellido2 = TRIM(p_apellido2);

        -- 2. VALIDACIÓN (Usando la misma lógica que para nombre y apellido1)
        IF NOT fn_validar_texto(p_apellido2) THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El segundo apellido contiene caracteres no permitidos';
        END IF;

        -- 3. INSERCIÓN
        INSERT INTO secondsurnames (idPerson, surname) 
        VALUES (p_id_persona, p_apellido2);
    END //

-- 4. Crear teléfono
CREATE PROCEDURE sp_crear_telefono(
    IN p_id_persona INT, 
    IN p_telefono VARCHAR(9)
    )
    BEGIN
        -- 1. NORMALIZACIÓN
        SET p_telefono = TRIM(p_telefono);

        -- 2. VERIFICACIÓN DE DUPLICADO EXACTO
        -- Evitamos que la misma persona tenga el mismo número repetido
        IF EXISTS (SELECT 1 FROM phones WHERE idPerson = p_id_persona AND phone = p_telefono) THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: Este número de teléfono ya está registrado para este trabajador';
        ELSE
            -- 3. VALIDACIÓN DE FORMATO (9 dígitos, empieza por 6,7,8 ó 9)
            IF p_telefono NOT REGEXP '^[6789][0-9]{8}$' THEN
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'Error: El formato del teléfono es incorrecto (debe tener 9 dígitos y empezar por 6, 7, 8 o 9)';
            END IF;

            -- 4. INSERCIÓN
            INSERT INTO phones (idPerson, phone) 
            VALUES (p_id_persona, p_telefono);
        END IF;
    END //

-- 5. Crear email
CREATE PROCEDURE sp_crear_email(
    IN p_id_persona INT, 
    IN p_email VARCHAR(100)
    )
    BEGIN
        -- 1. NORMALIZACIÓN
        SET p_email = LOWER(TRIM(p_email));

        -- 2. VERIFICACIÓN DE DUPLICADO EXACTO
        -- Evitamos que la misma persona tenga el mismo email repetido
        IF EXISTS (SELECT 1 FROM emails WHERE idPerson = p_id_persona AND email = p_email) THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: Este correo electrónico ya está registrado para este trabajador';
        ELSE
            -- 3. VALIDACIÓN DE FORMATO
            IF p_email NOT REGEXP '^[a-z0-9._%+-]{3,}@[a-z0-9.-]{2,}\\.[a-z]{2,}$' THEN
                SIGNAL SQLSTATE '45000' 
                SET MESSAGE_TEXT = 'Error: El formato del correo electrónico no es válido';
            END IF;

            -- 4. INSERCIÓN
            INSERT INTO emails (idPerson, email) 
            VALUES (p_id_persona, p_email);
        END IF;
    END //

-- 6. Crear dirección
CREATE PROCEDURE sp_crear_direccion(
    IN p_id_persona INT, 
    IN p_direccion VARCHAR(255), 
    IN p_codigo_postal VARCHAR(5), 
    IN p_municipio VARCHAR(100), 
    IN p_provincia VARCHAR(50)
    )
    BEGIN
        -- 1. VERIFICACIÓN DE EXISTENCIA (Excepción controlada)
        IF EXISTS (SELECT 1 FROM addresses WHERE idPerson = p_id_persona) THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: El trabajador ya tiene una dirección registrada';
        ELSE
            -- 2. NORMALIZACIÓN
            SET p_direccion = TRIM(p_direccion);
            SET p_codigo_postal = TRIM(p_codigo_postal);
            SET p_municipio = TRIM(p_municipio);
            SET p_provincia = TRIM(p_provincia);

            -- 3. VALIDACIONES DE FORMATO
            IF p_codigo_postal NOT REGEXP '^[0-9]{5}$' THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El código postal debe tener 5 dígitos';
            END IF;

            IF NOT fn_validar_texto(p_municipio) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El nombre del municipio contiene caracteres no válidos';
            END IF;

            IF NOT fn_validar_texto(p_provincia) THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: El nombre de la provincia contiene caracteres no válidos';
            END IF;

            -- 4. INSERCIÓN
            INSERT INTO addresses (idPerson, address, zipCode, town, province) 
            VALUES (p_id_persona, p_direccion, p_codigo_postal, p_municipio, p_provincia);
        END IF;
    END //

-- 7. Crear contrato
CREATE PROCEDURE sp_crear_contrato(
    IN p_id_persona INT, 
    IN p_inicio DATE, 
    IN p_fin DATE, 
    IN p_vacas INT, 
    IN p_aps INT
    )
    BEGIN
        -- 1. VALIDACIÓN DE FECHAS (Inicio no puede ser mayor que fin)
        IF p_inicio > p_fin THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: La fecha de inicio no puede ser posterior a la de fin';
        
        -- 2. VALIDACIÓN DE SOLAPAMIENTO
        -- Buscamos si existe algún contrato para esta persona donde los rangos se solapen
        ELSEIF EXISTS (
            SELECT 1 FROM contracts 
            WHERE idPerson = p_id_persona 
            AND p_inicio <= `to`   -- El nuevo inicio es antes de que termine uno existente
            AND p_fin >= `from`    -- El nuevo fin es después de que empiece uno existente
        ) THEN
            SIGNAL SQLSTATE '45000' 
            SET MESSAGE_TEXT = 'Error: El periodo de contrato se solapa con uno ya existente para este trabajador';
        
        ELSE
            -- 3. INSERCIÓN
            INSERT INTO contracts (idPerson, `from`, `to`, holidays, ap) 
            VALUES (p_id_persona, p_inicio, p_fin, p_vacas, p_aps);
            
            -- Devolvemos el ID al PHP para que Fran pueda seguir con la lógica de vacaciones
            SELECT LAST_INSERT_ID() as id_contrato;
        END IF;
    END //

-- 8. Buscar trabajadores (LIKE múltiple)
CREATE PROCEDURE sp_buscar_trabajadores(
    IN p_termino VARCHAR(100)
    )
    BEGIN
        SET @busqueda = CONCAT('%', p_termino, '%');
        SELECT p.idPerson as id, p.perName as nombre, p.surname as apellido1, 
            COALESCE(ps.surname, '') as apellido2, p.dni
        FROM persons p
        LEFT JOIN secondsurnames ps ON p.idPerson = ps.idPerson
        WHERE p.perName LIKE @busqueda OR p.surname LIKE @busqueda OR p.dni LIKE @busqueda
        LIMIT 20;
    END //

-- 9. Obtener detalles persona (de la tabla persons)
CREATE PROCEDURE sp_obtener_persona(
    IN p_id INT
    )
    BEGIN
        SELECT idPerson as id, perName as nombre, surname as apellido1, dni, woman as genero
        FROM persons
        WHERE idPerson = p_id;
    END //

-- 10. Obtener detalles persona (de la tabla secondSurnames)
CREATE PROCEDURE sp_obtener_apellido_secundario(
    IN p_id INT
    )
    BEGIN
        SELECT surname as apellido2 
        FROM secondsurnames
        WHERE idPerson = p_id;
    END //

-- 11. Obtener detalles persona (de la tabla phones)
CREATE PROCEDURE sp_obtener_telefonos(
    IN p_id INT
    )
    BEGIN
        SELECT phone as telefono 
        FROM phones 
        WHERE idPerson = p_id;
    END //

-- 12. Obtener detalles persona (de la tabla emails)
CREATE PROCEDURE sp_obtener_emails(
    IN p_id INT
    )
    BEGIN
        SELECT email 
        FROM emails 
        WHERE idPerson = p_id;
    END //

-- 13. Obtener detalles persona (de la tabla addresses)
CREATE PROCEDURE sp_obtener_direcciones(
    IN p_id INT
    )
    BEGIN
        SELECT address as direccion, zipCode as codigo_postal, town as municipio, province as provincia
        FROM addresses
        WHERE idPerson = p_id;
    END //

-- 14. Contrato vigente
CREATE PROCEDURE sp_obtener_contrato_vigente(
    IN p_id INT
    )
    BEGIN
        SELECT idContract as id_contrato, `from` as fecha_inicio, `to` as fecha_fin, holidays as dias_vacaciones, ap as dias_moscosos 
        FROM contracts 
        WHERE idPerson = p_id AND `to` >= CURDATE() LIMIT 1;
    END //

/* --- MÓDULO VACACIONES --- */

-- 15. Trabajadores activos (Dropdown)
CREATE PROCEDURE sp_obtener_trabajadores_activos()
    BEGIN
        SELECT 
            persons.idPerson as id, 
            persons.perName as nombre, 
            persons.surname as apellido1, 
            COALESCE(secondsurnames.surname, '') as apellido2,
            persons.dni,
            contracts.idContract as id_contrato,
            contracts.holidays as dias_vacaciones,
            contracts.ap as dias_moscosos
        FROM persons
        INNER JOIN contracts ON persons.idPerson = contracts.idPerson
        LEFT JOIN secondsurnames ON persons.idPerson = secondsurnames.idPerson
        WHERE contracts.`to` >= CURDATE() 
        ORDER BY nombre ASC, apellido1 ASC, apellido2 ASC;
    END //

-- 16. Obtener contrato por ID
CREATE PROCEDURE sp_obtener_contrato(
    IN p_id_con INT
    )
    BEGIN
        SELECT idContract, idPerson, `from`, `to`, holidays, ap 
        FROM contracts
        WHERE idContract = p_id_con;
    END //

-- 17. Contar días de vacaciones disfrutados
CREATE PROCEDURE sp_contar_dias_cogidos(
    IN p_id_contrato INT, 
    IN p_tipo_vacaciones VARCHAR(20)
    )
    BEGIN
        SELECT COUNT(*) as dias_cogidos 
        FROM workerholidays 
        INNER JOIN holidaytypes ON workerholidays.idHolidayType = holidaytypes.idHolidayType
        WHERE workerholidays.idContract = p_id_contrato 
        AND holidaytypes.holidayType = p_tipo_vacaciones;
    END //

-- 18. Contar días de moscosos disfrutados
CREATE PROCEDURE sp_contar_moscosos_cogidos(
    IN p_id_contrato INT
    )
    BEGIN
        SELECT COUNT(*) as moscosos_cogidos 
        FROM workerholidays 
        INNER JOIN holidaytypes ON workerholidays.idHolidayType = holidaytypes.idHolidayType
        WHERE workerholidays.idContract = p_id_contrato 
        AND holidaytypes.holidayType = 'moscosos';
    END //

-- 19. Festivos en rango
CREATE PROCEDURE sp_obtener_festivos_rango(
    IN p_f1 DATE, 
    IN p_f2 DATE
    )
    BEGIN
        SELECT bankHoliday 
        FROM bankholidays 
        WHERE bankHoliday BETWEEN p_f1 AND p_f2;
    END //

-- 20. ID tipo día
CREATE PROCEDURE sp_obtener_id_tipo_dia(
    IN p_tipo VARCHAR(20)
    )
    BEGIN
        SELECT idHolidayType as id_tipo 
        FROM holidaytypes
        WHERE holidayType = p_tipo;
    END //

-- 21. Validar moscosos no unidos (Lógica de proximidad +-1 día)
CREATE PROCEDURE sp_validar_moscosos_no_unidos(
    IN p_id_contrato INT, 
    IN p_fecha_inicio DATE, 
    IN p_fecha_fin DATE
    )
    BEGIN
        SELECT COUNT(*) as conflictos 
        FROM workerholidays
        INNER JOIN holidaytypes ON workerholidays.idHolidayType = holidaytypes.idHolidayType
        WHERE workerholidays.idContract = p_id_contrato 
        AND holidaytypes.holidayType = 'moscosos'
        AND (
            workerholidays.holiday = DATE_SUB(p_fecha_inicio, INTERVAL 1 DAY) -- Día inmediatamente anterior
            OR workerholidays.holiday = DATE_ADD(p_fecha_fin, INTERVAL 1 DAY) -- Día inmediatamente posterior
            OR workerholidays.holiday BETWEEN p_fecha_inicio AND p_fecha_fin  -- Dentro del rango solicitado
        );
    END //

-- 22. Validar solapamientos
CREATE PROCEDURE sp_validar_sin_solapamientos(
    IN p_id_contrato INT, 
    IN p_fecha_inicio DATE, 
    IN p_fecha_fin DATE
    )
    BEGIN
        SELECT COUNT(*) as solapamientos 
        FROM workerholidays 
        WHERE workerholidays.idContract = p_id_contrato 
        AND workerholidays.holiday BETWEEN p_fecha_inicio AND p_fecha_fin;
    END //

-- 23. Crear registro vacación
CREATE PROCEDURE sp_crear_vacacion(
    IN p_id_contrato INT, 
    IN p_id_tipo_dia INT, 
    IN p_fecha DATE, 
    IN p_notas TEXT
)
BEGIN
    DECLARE v_dias_totales INT;
    DECLARE v_dias_gastados INT;
    DECLARE v_tipo_nombre VARCHAR(20);

    -- 1. Obtener el nombre del tipo y el cupo total del contrato
    SELECT holidayType INTO v_tipo_nombre FROM holidaytypes WHERE idHolidayType = p_id_tipo_dia;
    
    IF v_tipo_nombre = 'vacaciones' THEN
        SELECT holidays INTO v_dias_totales FROM contracts WHERE idContract = p_id_contrato;
    ELSE
        SELECT ap INTO v_dias_totales FROM contracts WHERE idContract = p_id_contrato;
    END IF;

    -- 2. Contar cuántos lleva ya (incluyendo el que intentamos meter)
    SELECT COUNT(*) INTO v_dias_gastados 
    FROM workerholidays 
    WHERE idContract = p_id_contrato AND idHolidayType = p_id_tipo_dia;

    -- 3. VALIDACIÓN CRÍTICA
    IF v_dias_gastados >= v_dias_totales THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Error: No se puede registrar el día. Cupo máximo alcanzado para este tipo de día.';
    ELSE
        -- 4. Inserción solo si hay saldo
        INSERT INTO workerholidays (idContract, idHolidayType, holiday, notes) 
        VALUES (p_id_contrato, p_id_tipo_dia, p_fecha, p_notas);
    END IF;
END //



DELIMITER ;


