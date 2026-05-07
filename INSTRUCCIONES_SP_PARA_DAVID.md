# 📋 INSTRUCCIONES PARA DAVID: Convertir Queries a Stored Procedures

**Autor:** Fran  
**Fecha:** 4 mayo 2026  
**Propósito:** Guía para parametrizar las queries PHP en Stored Procedures (SPs)

---

## ¿Qué hacer?

Todas las queries en `logic/` tienen comentarios que dicen:
```
// CONSULTA A CONVERTIR A SP: sp_nombre
// Parámetros SP: $param1 (TIPO), $param2 (TIPO)
// Retorna: Descripción
```

**Tu trabajo:** Convertir esas queries en SPs en MariaDB, luego Fran reemplaza el código.

---

## Estructura de un SP (Ejemplo)

**Query original (PHP):**
```php
// CONSULTA A CONVERTIR A SP: sp_obtener_persona
// Parámetros SP: $id_persona (INT)
// Retorna: Datos personales de la persona

$sql_persona = "
    SELECT 
        p.idPerson as id,
        p.perName as nombre,
        p.surname as apellido1,
        p.dni as dni,
        p.woman as genero
    FROM PERSONS p
    WHERE p.idPerson = ?
";

$stmt_persona = $conexion->prepare($sql_persona);
$stmt_persona->execute([$id_persona]);
$persona = $stmt_persona->fetch(PDO::FETCH_ASSOC);
```

**SP equivalente (MariaDB):**
```sql
CREATE PROCEDURE sp_obtener_persona(
    IN p_id_persona INT
)
BEGIN
    SELECT 
        p.idPerson as id,
        p.perName as nombre,
        p.surname as apellido1,
        p.dni as dni,
        p.woman as genero
    FROM PERSONS p
    WHERE p.idPerson = p_id_persona;
END;
```

**Cómo Fran lo usa después:**
```php
$stmt = $conexion->prepare("CALL sp_obtener_persona(?)");
$stmt->execute([$id_persona]);
$persona = $stmt->fetch(PDO::FETCH_ASSOC);
```

---

## LISTA DE SPs A CREAR

### **MÓDULO TRABAJADORES**

#### 1. **sp_verificar_dni_existe**
- **Archivo:** `guardar_trabajador.php` (línea ~95)
- **Parámetros IN:** `p_dni VARCHAR(9)`
- **Retorna:** `COUNT(*) as cantidad`
- **Query:**
  ```sql
  SELECT COUNT(*) as cantidad FROM PERSONS WHERE dni = p_dni;
  ```

#### 2. **sp_crear_persona**
- **Archivo:** `guardar_trabajador.php` (línea ~130)
- **Parámetros IN:** `p_nombre VARCHAR(50)`, `p_apellido1 VARCHAR(50)`, `p_dni VARCHAR(9)`, `p_genero BOOLEAN`
- **Retorna:** `LAST_INSERT_ID()` (id persona creada)
- **Query:**
  ```sql
  INSERT INTO PERSONS (perName, surname, dni, woman) 
  VALUES (p_nombre, p_apellido1, p_dni, p_genero);
  SELECT LAST_INSERT_ID() as id_persona;
  ```

#### 3. **sp_crear_apellido_secundario**
- **Archivo:** `guardar_trabajador.php` (línea ~145)
- **Parámetros IN:** `p_id_persona INT`, `p_apellido2 VARCHAR(50)`
- **Retorna:** void
- **Query:**
  ```sql
  INSERT INTO SECONDSURNAMES (idPerson, surname) VALUES (p_id_persona, p_apellido2);
  ```

#### 4. **sp_crear_telefono**
- **Archivo:** `guardar_trabajador.php` (línea ~160)
- **Parámetros IN:** `p_id_persona INT`, `p_telefono VARCHAR(20)`
- **Retorna:** void
- **Query:**
  ```sql
  INSERT INTO PHONES (idPerson, phone) VALUES (p_id_persona, p_telefono);
  ```

#### 5. **sp_crear_email**
- **Archivo:** `guardar_trabajador.php` (línea ~174)
- **Parámetros IN:** `p_id_persona INT`, `p_email VARCHAR(50)`
- **Retorna:** void
- **Query:**
  ```sql
  INSERT INTO EMAILS (idPerson, email) VALUES (p_id_persona, p_email);
  ```

#### 6. **sp_crear_direccion**
- **Archivo:** `guardar_trabajador.php` (línea ~188)
- **Parámetros IN:** `p_id_persona INT`, `p_direccion VARCHAR(100)`, `p_codigo_postal VARCHAR(5)`, `p_municipio VARCHAR(50)`, `p_provincia VARCHAR(50)`
- **Retorna:** void
- **Query:**
  ```sql
  INSERT INTO ADDRESSES (idPerson, address, zipCode, town, province) 
  VALUES (p_id_persona, p_direccion, p_codigo_postal, p_municipio, p_provincia);
  ```

#### 7. **sp_crear_contrato**
- **Archivo:** `guardar_trabajador.php` (línea ~204)
- **Parámetros IN:** `p_id_persona INT`, `p_fecha_inicio DATE`, `p_fecha_fin DATE`, `p_dias_vacaciones INT`, `p_dias_moscosos INT`
- **Retorna:** `LAST_INSERT_ID()` (id contrato creado)
- **Query:**
  ```sql
  INSERT INTO CONTRACTS (idPerson, from, to, holidays, ap) 
  VALUES (p_id_persona, p_fecha_inicio, p_fecha_fin, p_dias_vacaciones, p_dias_moscosos);
  SELECT LAST_INSERT_ID() as id_contrato;
  ```

#### 8. **sp_buscar_trabajadores**
- **Archivo:** `buscar_trabajadores.php` (línea ~60)
- **Parámetros IN:** `p_termino VARCHAR(100)`
- **Retorna:** SELECT completo (ver query en PHP)
- **Notas:** Búsqueda LIKE en 3 campos. Limitar a 20 resultados.

#### 9. **sp_obtener_persona**
- **Archivo:** `obtener_trabajador.php` (línea ~60)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** Datos personales (id, nombre, apellido1, dni, genero)

#### 10. **sp_obtener_apellido_secundario**
- **Archivo:** `obtener_trabajador.php` (línea ~80)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** `surname as apellido2` o NULL

#### 11. **sp_obtener_telefonos**
- **Archivo:** `obtener_trabajador.php` (línea ~95)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** `phone as telefono` (primer registro)

#### 12. **sp_obtener_emails**
- **Archivo:** `obtener_trabajador.php` (línea ~110)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** `email` (primer registro)

#### 13. **sp_obtener_direcciones**
- **Archivo:** `obtener_trabajador.php` (línea ~125)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** address, zipCode, town, province

#### 14. **sp_obtener_contrato_vigente**
- **Archivo:** `obtener_trabajador.php` (línea ~145)
- **Parámetros IN:** `p_id_persona INT`
- **Retorna:** Datos contrato vigente (id, fechas, días)

### **MÓDULO VACACIONES**

#### 15. **sp_obtener_trabajadores_activos**
- **Archivo:** `obtener_trabajadores_activos.php` (línea ~35)
- **Parámetros IN:** ninguno
- **Retorna:** Lista de personas con contrato vigente

#### 16. **sp_obtener_contrato**
- **Archivo:** `obtener_disponibilidad.php` (línea ~55)
- **Parámetros IN:** `p_id_contrato INT`
- **Retorna:** Datos contrato (fechas, días totales)

#### 17. **sp_contar_dias_cogidos**
- **Archivo:** `obtener_disponibilidad.php` (línea ~80)
- **Parámetros IN:** `p_id_contrato INT`, `p_tipo_vacaciones VARCHAR(20)`
- **Retorna:** `COUNT(*) as dias_cogidos`

#### 18. **sp_contar_moscosos_cogidos**
- **Archivo:** `obtener_disponibilidad.php` (línea ~96)
- **Parámetros IN:** `p_id_contrato INT`
- **Retorna:** `COUNT(*) as moscosos_cogidos`

#### 19. **sp_obtener_festivos_rango**
- **Archivo:** `obtener_disponibilidad.php` (línea ~111)
- **Parámetros IN:** `p_fecha_inicio DATE`, `p_fecha_fin DATE`
- **Retorna:** Lista de `bankHoliday` en el rango

#### 20. **sp_obtener_id_tipo_dia**
- **Archivo:** `guardar_vacaciones.php` (línea ~420)
- **Parámetros IN:** `p_tipo_dia VARCHAR(20)` ('vacaciones' o 'moscosos')
- **Retorna:** `idHolidayType as id_tipo`

#### 21. **sp_validar_moscosos_no_unidos**
- **Archivo:** `guardar_vacaciones.php` (línea ~360)
- **Parámetros IN:** `p_id_contrato INT`, `p_fecha_inicio DATE`, `p_fecha_fin DATE`
- **Retorna:** `COUNT(*) as conflictos` (debe ser 0)
- **Lógica:** Verifica que no haya moscosos ±1 día del rango

#### 22. **sp_validar_sin_solapamientos**
- **Archivo:** `guardar_vacaciones.php` (línea ~383)
- **Parámetros IN:** `p_id_contrato INT`, `p_fecha_inicio DATE`, `p_fecha_fin DATE`
- **Retorna:** `COUNT(*) as solapamientos` (debe ser 0)
- **Lógica:** Verifica que no hay vacaciones en el rango

#### 23. **sp_crear_vacacion**
- **Archivo:** `guardar_vacaciones.php` (línea ~470)
- **Parámetros IN:** `p_id_contrato INT`, `p_id_tipo_dia INT`, `p_fecha DATE`, `p_notas TEXT`
- **Retorna:** void
- **Query:**
  ```sql
  INSERT INTO WORKERHOLIDAYS (idContract, idHolidayType, holiday, notes)
  VALUES (p_id_contrato, p_id_tipo_dia, p_fecha, p_notas);
  ```

---

## PRIORIDAD DE SPs

**CRÍTICOS (para 8 mayo - corrección):**
1. sp_crear_persona
2. sp_crear_contrato
3. sp_buscar_trabajadores
4. sp_obtener_trabajadores_activos
5. sp_crear_vacacion
6. sp_validar_sin_solapamientos

**SECUNDARIOS (para 31 mayo - entrega):**
- El resto de SPs

---

## CÓMO COMUNICAR CUANDO TERMINES

1. Crea archivo `STORED_PROCEDURES.sql` con TODOS los CREATE PROCEDURE
2. Envía a Raquel + Fran para que prueben
3. Fran reemplaza: `$conexion->prepare($sql)` → `$conexion->prepare("CALL sp_nombre(?, ...)")`

---

## NOTAS IMPORTANTES

- **Nombres:** Todos los SPs empiezan con `sp_`
- **Parámetros:** Usar `IN`, no `OUT` (excepto si devuelve múltiples valores)
- **Seguridad:** Los SPs parametrizados previenen SQL Injection
- **Testing:** Prueba cada SP en MariaDB antes de entregar
- **Si hay duda:** Pregunta a Fran sobre qué devuelve la query

