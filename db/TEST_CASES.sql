/*
 * ============================================================
 * TEST SUITE: PRUEBAS DE VALIDACIÓN Y SEGURIDAD
 * ============================================================
 * Finalidad: Verificar que los SP, Functions y Checks 
 * bloquean datos incorrectos y aceptan datos válidos.
 * ============================================================
 */

USE proyecto_vacaciones;

-- Limpieza previa para que las pruebas no fallen por restos de datos
DELETE FROM persons; 

-- ------------------------------------------------------------
-- BLOQUE 1: PRUEBAS DE ÉXITO (Happy Path)
-- ------------------------------------------------------------
-- Comprueba: Normalización (TRIM, UPPER, LOWER) y caracteres especiales.

-- 1.1 Crear persona con apóstrofe y guion (DNI inventado pero real: cumple con el MOD(23))
CALL sp_crear_persona('Jean-Luc', 'O''Donnell', '12345678Z', 0); -- Devuelve ID 

-- 1.2. Capturar el ID generado
SET @id_test = LAST_INSERT_ID();

-- 1.3 Añadir datos vinculados al ID 
CALL sp_crear_apellido_secundario(1, 'Sánchez-Villalobos');
CALL sp_crear_telefono(1, '600111222');
CALL sp_crear_email(1, 'USUARIO@Empresa.COM'); -- Se debe guardar: usuario@empresa.com
CALL sp_crear_direccion(1, 'Calle l''Empordà', '08001', 'L''Hospitalet', 'Barcelona');

-- 1.3 Crear contrato inicial
CALL sp_crear_contrato(1, '2024-01-01', '2024-12-31', 22, 6);


-- ------------------------------------------------------------
-- BLOQUE 2: PRUEBAS DE ERROR - FORMATO (Deben fallar)
-- ------------------------------------------------------------
-- Cada instrucción debería devolver un error de SQL personalizado.

-- 2.1 Error en DNI (Letra incorrecta según módulo 23)
-- Mensaje esperado: "Error: DNI/NIE no válido o mal formado"
CALL sp_crear_persona('Prueba', 'Fallo', '12345678A', 1);

-- 2.2 Error en Nombre (Contiene caracteres no permitidos)
-- Mensaje esperado: "Error: El nombre contiene caracteres no permitidos"
CALL sp_crear_persona('Luis 123', 'García', '71234567X', 0);

-- 2.3 Error en Teléfono (Formato español incorrecto)
-- Mensaje esperado: "Error: El formato del teléfono es incorrecto..."
CALL sp_crear_telefono(1, '555000111'); 

-- 2.4 Error en Email (No cumple regex)
-- Mensaje esperado: "Error: El formato del correo electrónico no es válido"
CALL sp_crear_email(1, 'email_sin_punto@com');


-- ------------------------------------------------------------
-- BLOQUE 3: PRUEBAS DE ERROR - INTEGRIDAD Y NEGOCIO
-- ------------------------------------------------------------

-- 3.1 Error Dirección Duplicada (PK idPerson - Para idPerson === 1)
-- Mensaje esperado: "Error: El trabajador ya tiene una dirección registrada"
CALL sp_crear_direccion(1, 'Calle Nueva', '28001', 'Madrid', 'Madrid');

-- 3.2 Error Solapamiento Contratos
-- Mensaje esperado: "Error: El periodo de contrato se solapa..."
CALL sp_crear_contrato(1, '2024-06-01', '2024-08-01', 11, 3);


-- ------------------------------------------------------------
-- BLOQUE 4: VERIFICACIÓN DE RESULTADOS
-- ------------------------------------------------------------

-- Comprobar que el buscador funciona con caracteres especiales
CALL sp_buscar_trabajadores('donnell');

-- Comprobar que el email se ha normalizado a minúsculas
SELECT email FROM emails WHERE idPerson = 1;
