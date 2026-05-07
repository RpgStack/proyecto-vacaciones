# 📋 INSTRUCCIONES PARA DAVID: Convertir Queries a Stored Procedures

**Autor:** David
**Fecha:** 7 mayo 2026
**Propósito:** Resumen de los avances realizados

---

## 06/05/2026 - BBDD. Credenciales de acceso y creación arquitectura

Se han mantenido las credenciales tal y como estaban definidas en conexion.php: tratándose de un superusuario root, en el momento de producción, es más ágil para evitar errores por diferentes configuraciones de MariaDB.

DDL de las tablas previstas con acabado profesional:

- Índices necesarios para las consultas previstas
- Implementación de chacks para limpieza de datos y mantenimiento para cualquier versión y configuración de MariaDB

Modificación de nomenclatura en código de archivos de lógica (Fran) para evitar creación de múltiples bases de datos y simplificación de la lógica de claves foráneas.

---

## 07/05/2026 - Creación de SPs

Creados los 23 SPs en STORED_PROCEDURES.sql

He añadido 2 funciones de verificación (en FUNCTIONS.sql) para que la lógica esté centralizada también en la base de datos:

- Verificación de formato de DNI/NIE
- Verificación de formato de texto para nombre, apellido1, apellido2, localidad y provincia

Para comprobar que no existe ningún fallo en el funcionamiento de la bbdd, he añadido unos tests (TEST_CASES.sql) para comprobar la que los SPs lanzan los errores requeridos

He creado creado unos datos genéricos (en DML.sql) para poder hacer pruebas CRUD

## IMPORTANTE: Orden de ejecución de los .sql

1. DDL.sql
2. FUNCTIONS.sql
3. STORED_PROCEDURES.sql
4. TEST_CASES.sql (Recordad que los últimos tests tiene como idPerson === 1, si hacéis varias veces los primeros, por lo que sea, os recomiendo reejecutar todo desde el principio)
5. DML.sql (Para que las inserciones se hagan correctamente, es necesario reejecutar todo salvo TEST_CASES.sql)
