drop DATABASE proyecto_vacaciones;
/*
 * ============================================================
 * DDL: DATA DEFINITION LANGUAGE - SISTEMA GESTIÓN VACACIONES
 * ============================================================
 * 
 * Este archivo define la estructura física de la base de datos.
 * Incluye: Tablas, Relaciones (FK) e Índices de rendimiento.
 * ============================================================
 */

CREATE DATABASE IF NOT EXISTS proyecto_vacaciones 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE proyecto_vacaciones;

/*
 * MÓDULO: PERSONAL (Esquema lógico PER)
 */

CREATE TABLE persons (
    idPerson  INT AUTO_INCREMENT PRIMARY KEY,
    perName   VARCHAR(100) NOT NULL CHECK (perName REGEXP '^[[:alpha:][:space:]áéíóúÁÉÍÓÚñÑüÜ\' \'-]+$'),
    surname   VARCHAR(100) NOT NULL CHECK (surname REGEXP '^[[:alpha:][:space:]áéíóúÁÉÍÓÚñÑüÜ\' \'-]+$'),
    dni       VARCHAR(9) NOT NULL CHECK (dni REGEXP '^[0-9XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$'),
    woman     TINYINT(1) DEFAULT 0,
    -- ÍNDICES INDEPENDIENTES (Para los 'OR' de la lógica de Fran)
    UNIQUE INDEX idx_dni (dni),
    INDEX idx_name (perName),
    INDEX idx_first_surname (surname)
) ENGINE=InnoDB;

CREATE TABLE secondsurnames (
    idPerson  INT PRIMARY KEY,
    surname   VARCHAR(100) NOT NULL CHECK (surname REGEXP '^[[:alpha:][:space:]áéíóúÁÉÍÓÚñÑüÜ\' \'-]+$'),
    INDEX idx_second_surname (surname), -- Para que el JOIN sea rápido al buscar
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE addresses (
    idPerson  INT PRIMARY KEY,
    address   VARCHAR(255) NOT NULL,
    zipCode   VARCHAR(5) NOT NULL,
    town      VARCHAR(100) NOT NULL,
    province  VARCHAR(50) NOT NULL,
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE phones (
    idPerson  INT NOT NULL,
    phone     VARCHAR(9) NOT NULL,
    UNIQUE (idPerson, phone),
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE,
    -- Validación: Exactamente 9 números (formato español estándar: 6,7,8 ó 9 + 8 dígitos)
    CONSTRAINT chk_phone_format CHECK (phone REGEXP '^[6789][0-9]{8}$')
) ENGINE=InnoDB;

CREATE TABLE emails (
    idPerson  INT NOT NULL,
    email     VARCHAR(100) NOT NULL CHECK (email REGEXP '^[a-z0-9._%+-]{3,}@[a-z0-9.-]{2,}\\.[a-z]{2,}$'),
    UNIQUE (idPerson, email),
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE,
    INDEX idx_email (email) -- Optimización: Para futuras búsquedas por correo
) ENGINE=InnoDB;

/*
 * MÓDULO: CONTRATOS Y CALENDARIO (Esquema lógico CON)
 */

CREATE TABLE contracts (
    idContract  INT AUTO_INCREMENT PRIMARY KEY,
    idPerson    INT NOT NULL,
    `from`      DATE NOT NULL,
    `to`        DATE NOT NULL,
    holidays    INT DEFAULT 22,
    ap          INT DEFAULT 6,
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE,
    -- ÍNDICES
    INDEX idx_contract_person (idPerson), -- Optimiza los JOIN y búsquedas por empleado
    INDEX idx_contract_active (`to`)              -- Optimiza el filtrado de empleados activos
) ENGINE=InnoDB;

CREATE TABLE bankholidays (
    bankHoliday   DATE PRIMARY KEY
) ENGINE=InnoDB;

/*
 * MÓDULO: VACACIONES
 */

CREATE TABLE holidaytypes (
    idHolidayType INT AUTO_INCREMENT PRIMARY KEY,
    holidayType   VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE workerholidays (
    idContract      INT NOT NULL,
    idHolidayType   INT NOT NULL,
    holiday         DATE NOT NULL,
    -- PK de dos campos: un contrato no puede tener dos registros el mismo día
    PRIMARY KEY (idContract, holiday), 
    FOREIGN KEY (idContract) REFERENCES contracts(idContract) ON DELETE CASCADE,
    FOREIGN KEY (idHolidayType) REFERENCES holidaytypes(idHolidayType),
    -- Índice para el tipo de día (útil para los COUNT de disponibilidad)
    INDEX idx_day_type (idHolidayType)
) ENGINE=InnoDB;

/*
 * SEEDERS: CARGA DE CONFIGURACIÓN INICIAL
 */
INSERT IGNORE INTO holidaytypes (holidayType) VALUES ('vacaciones'), ('moscosos');
