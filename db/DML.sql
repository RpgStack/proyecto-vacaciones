USE proyecto_vacaciones;

-- 1. CARGA DE FESTIVOS (BankHolidays) 2026
INSERT IGNORE INTO bankholidays (bankHoliday) 
  VALUES 
  ('2026-01-01'), 
  ('2026-01-06'), 
  ('2026-04-02'), 
  ('2026-04-03'),
  ('2026-05-01'), 
  ('2026-05-02'),
  ('2026-07-25'),
  ('2026-08-15'),
  ('2026-10-12'),
  ('2026-11-02'),
  ('2026-11-09'),
  ('2026-12-07'),
  ('2026-12-08'),
  ('2026-12-25');

-- 2. TRABAJADORES (20 Personas)
-- Bloque 1: Contratos Antiguos (IDs 1-5)
CALL sp_crear_persona('Juan', 'García', '12345678Z', 0);
CALL sp_crear_persona('Marta', 'Sánchez', '23456789D', 1);
CALL sp_crear_persona('Luis', 'Gómez', '34567890V', 0);
CALL sp_crear_persona('Ana', 'Pérez', '45678901G', 1);
CALL sp_crear_persona('Pedro', 'Ruiz', '56789012B', 0);

-- Bloque 2: Temporales 2026 (IDs 6-10)
CALL sp_crear_persona('Elena', 'Martín', 'X1234567L', 1); -- NIE
CALL sp_crear_persona('Jordi', 'Vila', 'Y2345678Z', 0);  -- NIE
CALL sp_crear_persona('Sofía', 'López', '67890123B', 1);
CALL sp_crear_persona('Marc', 'Serrano', '78901234X', 0);
CALL sp_crear_persona('Lucía', 'Casas', '89012345E', 1);

-- Bloque 3: Fijos desde Nov 2025 + Antiguo extinto (IDs 11-15)
CALL sp_crear_persona('Raúl', 'Jiménez', '90123456A', 0);
CALL sp_crear_persona('Carla', 'Díaz', '01234567L', 1);
CALL sp_crear_persona('Hugo', 'Torres', '11223344B', 0);
CALL sp_crear_persona('Irene', 'Vidal', '22334455Y', 1);
CALL sp_crear_persona('Óscar', 'Marín', '33445566R', 0);

-- Bloque 4: Fijos desde Ene 2025 (IDs 16-20)
CALL sp_crear_persona('Nerea', 'Moya', 'Z3456789D', 1); -- NIE
CALL sp_crear_persona('Iván', 'Peña', '44556677L', 0);
CALL sp_crear_persona('Julia', 'Ramos', '55667788Z', 1);
CALL sp_crear_persona('Saúl', 'Blanco', '66778899D', 0);
CALL sp_crear_persona('Eva', 'Sanz', '77889900D', 1);

-- 3. CONTRATOS
-- 5 Antiguos (extintos)
INSERT INTO contracts (idPerson, `from`, `to`, holidays, ap)
  VALUES 
  (1,'2023-01-01','2023-12-31',22,6),
  (2,'2022-01-01','2022-12-31',22,6),
  (3,'2021-01-01','2021-12-31',22,6),
  (4,'2023-06-01','2023-12-31',11,3),
  (5,'2022-05-01','2023-04-30',22,6);

-- 5 Temporales Feb-Sep 2026 (idContract 6-10)
INSERT INTO contracts (idPerson, `from`, `to`, holidays, ap) 
  VALUES 
  (6,'2026-02-01','2026-09-30',15,4),
  (7,'2026-02-01','2026-09-30',15,4),
  (8,'2026-02-01','2026-09-30',15,4),
  (9,'2026-02-01','2026-09-30',15,4),
  (10,'2026-02-01','2026-09-30',15,4);

-- 5 Fijos Nov 2025 + Sus antiguos extintos (idContract 11-20)
INSERT INTO contracts (idPerson, `from`, `to`, holidays, ap)
  VALUES 
  (11,'2024-01-01','2025-09-20',22,6),
  (11,'2025-11-01','2030-12-31',22,6),
  (12,'2023-01-01','2025-08-15',22,6),
  (12,'2025-11-01','2030-12-31',22,6),
  (13,'2024-05-01','2025-09-01',10,3),
  (13,'2025-11-01','2030-12-31',22,6),
  (14,'2024-01-01','2025-09-10',22,6),
  (14,'2025-11-01','2030-12-31',22,6),
  (15,'2023-10-01','2025-07-01',22,6),
  (15,'2025-11-01','2030-12-31',22,6);

-- 5 Fijos Ene 2025 (idContract 21-25)
INSERT INTO contracts (idPerson, `from`, `to`, holidays, ap) 
  VALUES 
  (16,'2025-01-01','2030-12-31',22,6),
  (17,'2025-01-01','2030-12-31',22,6),
  (18,'2025-01-01','2030-12-31',22,6),
  (19,'2025-01-01','2030-12-31',22,6),
  (20,'2025-01-01','2030-12-31',22,6);

-- 4. VACACIONES Y MOSCOSOS (Gasto parcial para vigentes)
-- Nota: idHolidayType 1=vacaciones, 2=moscosos
-- Gastamos 7 días de vacas (aprox 1/3 de 22) y 3 de AP (mitad de 6)
-- Aplicamos a contratos IDs: 6 al 10, 12, 14, 16, 18, 20... (Contratos vigentes)

-- Ejemplo para el contrato 12 (Vigente de Carla Díaz)
INSERT INTO workerholidays (idContract, idHolidayType, holiday)
  VALUES 
  (12, 1, '2026-02-16'),
  (12, 1, '2026-02-17'),
  (12, 1, '2026-02-18'),
  (12, 1, '2026-02-19'),
  (12, 1, '2026-02-20'),
  (12, 1, '2026-03-02'),
  (12, 1, '2026-03-03'),
  (12, 2, '2026-01-15'),
  (12, 2, '2026-03-20'),
  (12, 2, '2026-04-10');

