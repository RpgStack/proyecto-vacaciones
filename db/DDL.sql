/**
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

/**
 * MÓDULO: PERSONAL (Esquema lógico PER)
 */

CREATE TABLE persons (
    idPerson INT AUTO_INCREMENT PRIMARY KEY,
    perName VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    woman TINYINT(1) DEFAULT 0,
    -- ÍNDICES INDEPENDIENTES (Para los 'OR' de la lógica de Fran)
    UNIQUE INDEX idx_dni (dni),
    INDEX idx_nombre (perName),
    INDEX idx_apellido1 (surname)
) ENGINE=InnoDB;

CREATE TABLE secondsurnames (
    idPerson INT PRIMARY KEY,
    surname VARCHAR(100),
    INDEX idx_apellido2 (surname), -- Para que el JOIN sea rápido al buscar
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE addresses (
    idPerson INT PRIMARY KEY,
    address VARCHAR(255),
    zipCode VARCHAR(10),
    town VARCHAR(100),
    province VARCHAR(100),
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE phones (
    idPerson INT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE emails (
    idPerson INT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE,
    INDEX idx_email (email) -- Optimización: Para futuras búsquedas por correo
) ENGINE=InnoDB;

/**
 * MÓDULO: CONTRATOS Y CALENDARIO (Esquema lógico CON)
 */

CREATE TABLE contracts (
    idContract INT AUTO_INCREMENT PRIMARY KEY,
    idPerson INT NOT NULL,
    `from` DATE NOT NULL,
    `to` DATE NOT NULL,
    holidays INT DEFAULT 22,
    ap INT DEFAULT 6,
    FOREIGN KEY (idPerson) REFERENCES persons(idPerson) ON DELETE CASCADE,
    -- ÍNDICE:
    INDEX idx_vigencia (`to`) -- Optimización: Usado por CURDATE() para filtrar activos
) ENGINE=InnoDB;

CREATE TABLE bankholidays (
    bankHoliday DATE PRIMARY KEY,
    description VARCHAR(100)
) ENGINE=InnoDB;

/**
 * MÓDULO: VACACIONES
 */

CREATE TABLE holidaytypes (
    idHolidayType INT AUTO_INCREMENT PRIMARY KEY,
    holidayType VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE workerholidays (
    idWorkerHoliday INT AUTO_INCREMENT PRIMARY KEY,
    idContract INT NOT NULL,
    idHolidayType INT NOT NULL,
    holiday DATE NOT NULL,
    notes TEXT,
    FOREIGN KEY (idContract) REFERENCES contracts(idContract) ON DELETE CASCADE,
    FOREIGN KEY (idHolidayType) REFERENCES holidaytypes(idHolidayType),
    -- ÍNDICE:
    UNIQUE INDEX idx_no_duplicate_days (idContract, holiday) -- Integridad: Evita solapamientos
) ENGINE=InnoDB;

/**
 * SEEDERS: CARGA DE CONFIGURACIÓN INICIAL
 */
INSERT IGNORE INTO holidaytypes (holidayType) VALUES ('vacaciones'), ('moscosos');
