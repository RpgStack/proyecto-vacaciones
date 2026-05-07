# 🎓 Proyecto: Gestión de Vacaciones - Capa de Acceso a Datos

**Curso:** FP Grado Superior DAW
**Equipo:** Fran (Backend), Raquel (Frontend), David (BD)
**Fecha Inicio:** 4 mayo 2026
**Plazo Corrección:** 8 mayo 2026
**Plazo Entrega:** 31 mayo 2026

---

## 📚 Estructura del Proyecto

```
proyecto-vacaciones/
├── config/
│   └── conexion.php                    ← Conexión PDO a MariaDB
├── logic/
│   ├── trabajadores/
│   │   ├── guardar_trabajador.php      ← POST: Crear trabajador
│   │   ├── buscar_trabajadores.php     ← GET: Búsqueda dinámica
│   │   └── obtener_trabajador.php      ← GET: Detalles trabajador
│   ├── vacaciones/
│   │   ├── obtener_trabajadores_activos.php    ← GET: Dropdown
│   │   ├── obtener_disponibilidad.php          ← GET: Calcular días
│   │   └── guardar_vacaciones.php              ← POST: Registrar vacaciones
│   ├── validation/
│   │   └── Validador.php               ← Clase: Validaciones
│   └── utils/
│       └── respuestas.php              ← Funciones: JSON responses
├── INSTRUCCIONES_SP_PARA_DAVID.md      ← Para David: Convertir a SPs
├── GUIA_INTEGRACION_RAQUEL.md          ← Para Raquel: Integración
└── README.md                           ← Este archivo
```

---

## 🔧 Configuración Inicial

### 1. Coordinar con David

- [ ] Obtener credenciales BD (host, user, password)
- [ ] Confirmar nombres exactos tablas (PER.PERSONS, CON.CONTRACTS, etc)
- [ ] Si no existe BD, ejecutar script SQL de David

### 2. Actualizar conexión

Editar `config/conexion.php`:

```php
$config = [
    'host' => 'localhost',
    'base_datos' => 'proyecto_vacaciones',
    'usuario' => 'root',
    'contraseña' => 'tu_password_aqui'
];
```

### 3. Testear conexión

```bash
# En navegador, abrir:
http://localhost/proyecto-vacaciones/config/conexion.php

# Debe mostrar: ✓ Conectado correctamente a: proyecto_vacaciones
```

---

## 📋 Rol de Cada Archivo

### **Configuración & Utilidades**

| Archivo                            | Responsabilidad                             |
| ---------------------------------- | ------------------------------------------- |
| `config/conexion.php`            | Conexión PDO + manejo errores              |
| `logic/utils/respuestas.php`     | Funciones JSON (éxito, error, validación) |
| `logic/validation/Validador.php` | Clase validación (DNI, email, fechas, etc) |

### **Módulo Trabajadores**

| Archivo                     | Método | Parámetros                  | Devuelve                               |
| --------------------------- | ------- | ---------------------------- | -------------------------------------- |
| `guardar_trabajador.php`  | POST    | Form data (nombre, DNI, etc) | JSON con id_persona e id_contrato      |
| `buscar_trabajadores.php` | GET     | `q=Juan` (búsqueda)       | Array JSON de trabajadores encontrados |
| `obtener_trabajador.php`  | GET     | `id=5`                     | JSON con datos completos + contrato    |

### **Módulo Vacaciones**

| Archivo                              | Método | Parámetros              | Devuelve                                       |
| ------------------------------------ | ------- | ------------------------ | ---------------------------------------------- |
| `obtener_trabajadores_activos.php` | GET     | ninguno                  | Array JSON trabajadores con contrato vigente   |
| `obtener_disponibilidad.php`       | GET     | `id_contrato=123`      | JSON con días disponibles, moscosos, festivos |
| `guardar_vacaciones.php`           | POST    | Form data (fechas, tipo) | JSON con confirmación o error                 |

---

## 🔐 Características de Seguridad

### ✅ Implementado

- **Prepared Statements:** Previene SQL Injection
- **Validación Server-side:** No confía en cliente (Raquel HTML5)
- **Sanitización:** Datos procesados antes de usar
- **Transacciones:** Múltiples inserts garantizados (todo o nada)
- **Mensajes de Error:** Nunca muestra SQL crudo
- **Respuestas JSON:** Siempre consistente

### ⚠️ Validaciones Críticas

- DNI: Formato + dígito de control
- Email: Formato válido
- Teléfono: 9 dígitos
- Fechas: Formato YYYY-MM-DD + rango válido
- Moscosos: NO pueden estar unidos
- Disponibilidad: NO exceder límite de días
- Solapamientos: NO hay conflictos de fechas

---

## 🚀 Flujos Principales

### Flujo 1: ALTA TRABAJADOR

```
1. Raquel: Envía POST a /logic/trabajadores/guardar_trabajador.php
2. Fran:   Valida DNI, email, fechas
3. Fran:   Verifica DNI NO duplicado en BD
4. Fran:   BEGIN TRANSACTION
5. Fran:   INSERT PER.PERSONS + SECONDSURNAMES + ADDRESSES + CONTRACTS
6. Fran:   COMMIT o ROLLBACK
7. Raquel: Recibe JSON con éxito/error
8. Raquel: Muestra mensaje + recarga lista
```

### Flujo 2: BUSCAR TRABAJADOR

```
1. Raquel: Escribe en input búsqueda
2. Raquel: Envía GET a /logic/trabajadores/buscar_trabajadores.php?q=Juan
3. Fran:   Query: SELECT * WHERE nombre LIKE % OR dni LIKE %
4. Raquel: Recibe JSON con resultados
5. Raquel: Inyecta dinámicamente en tabla
```

### Flujo 3: REGISTRAR VACACIONES

```
1. Raquel: Selecciona trabajador en tabla
2. Raquel: GET /logic/vacaciones/obtener_disponibilidad.php?id_contrato=X
3. Fran:   Calcula: total - cogidos - festivos
4. Raquel: Muestra "Tienes 12 días disponibles"
5. Raquel: Usuario elige fechas + tipo (vacaciones/moscosos)
6. Raquel: POST /logic/vacaciones/guardar_vacaciones.php
7. Fran:   VALIDACIONES CRÍTICAS:
            - ¿Fechas válidas?
            - ¿Moscosos NO unidos?
            - ¿NO excede límite?
            - ¿NO se superpone?
8. Fran:   INSERT cada día en CON.WORKERHOLIDAYS
9. Raquel: Recibe confirmación
10. Raquel: Refresh tabla
```

---

## 🛠️ Desarrollo Paso a Paso

### Setup

- [X] Crear carpeta `config/` + `logic/`
- [X] Crear `conexion.php` + `Validador.php` + `respuestas.php`
- [ ] **Coordinar con David:** Credenciales BD

### Módulo Trabajadores

- [X] `guardar_trabajador.php` - Alta
- [X] `buscar_trabajadores.php` - Búsqueda
- [X] `obtener_trabajador.php` - Detalles
- [ ] **Testear manualmente**

### Módulo Vacaciones

- [X] `obtener_trabajadores_activos.php`
- [X] `obtener_disponibilidad.php`
- [X] `guardar_vacaciones.php` - MÁS CRÍTICO
- [ ] **Testear manualmente**

### CORRECCIÓN ALEJANDRO

- [ ] Crear trabajador → aparece en listado ✓
- [ ] Buscar trabajador → resultados ✓
- [ ] Ver disponibilidad → calcula ✓
- [ ] Guardar vacaciones → inserta ✓
- [ ] NO permite exceder límites ✓
- [ ] Errores claros (sin SQL) ✓

### REFINAMIENTO

- [ ] Integración Raquel (fetch() en formularios)
- [ ] David crea Stored Procedures
- [ ] Fran reemplaza queries por CALL SPs
- [ ] Testing completo
- [ ] Documentación memoria

---

## 📞 Comunicación Entre Equipos

### Para Raquel (Frontend)

**Lee:** `GUIA_INTEGRACION_RAQUEL.md`

- Endpoints disponibles (URLs)
- Parámetros POST/GET esperados
- Estructura JSON de respuestas
- Ejemplos código fetch()

### Para David (Base Datos)

**Lee:** `INSTRUCCIONES_SP_PARA_DAVID.md`

- 23 Stored Procedures a crear
- Query original de cada uno
- Parámetros entrada/salida
- Cuál es prioritario

### Para Fran (Backend - TÚ)

- Este README
- Comentarios en el código
- Validaciones críticas en `guardar_vacaciones.php`

---

## 🧪 Testing Manual

### Test 1: Crear trabajador

```bash
curl -X POST http://localhost/proyecto-vacaciones/logic/trabajadores/guardar_trabajador.php \
  -d "nombre=Juan&apellido1=Garcia&dni=12345678Z&genero=0&fecha_inicio=2026-05-01&fecha_fin=2026-12-31&dias_vacaciones=22&dias_moscosos=6"

# Esperar: {"éxito":true,"datos":{"id_persona":1,"id_contrato":1,...}}
```

### Test 2: Buscar trabajador

```bash
curl http://localhost/proyecto-vacaciones/logic/trabajadores/buscar_trabajadores.php?q=Juan

# Esperar: {"éxito":true,"datos":[{...},...]}
```

### Test 3: Obtener disponibilidad

```bash
curl http://localhost/proyecto-vacaciones/logic/vacaciones/obtener_disponibilidad.php?id_contrato=1

# Esperar: {"éxito":true,"datos":{"vacaciones":{...},"moscosos":{...}}}
```

---

## 📖 Documentación Código

**Cada archivo tiene:**

1. Comentario cabecera (qué hace, URL, parámetros)
2. Comentarios de cada sección
3. **"// CONSULTA A CONVERTIR A SP"** para David
4. Try-catch para errores
5. JSON response siempre

**Ejemplo estándar:**

```php
<?php
/**
 * ============================================================
 * HANDLER: [NOMBRE]
 * ============================================================
 * 
 * URL: [METHOD] /logic/ruta/archivo.php
 * 
 * [Descripción de qué hace]
 * 
 * ============================================================
 */

require_once '../../config/conexion.php';
require_once '../validation/Validador.php';
require_once '../utils/respuestas.php';

// VALIDAR MÉTODO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_respuesta_error("Método no permitido", 405);
}

// RECOPILAR DATOS
$param1 = $_POST['param1'] ?? '';

// VALIDAR
if (empty($param1)) {
    json_respuesta_error("Param1 es requerido");
}

// PROCESAR BD
try {
    // CONSULTA A CONVERTIR A SP: sp_nombre
    // Parámetros: ...
    // Retorna: ...
  
    $sql = "SELECT * FROM TABLA WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$param1]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
  
    json_respuesta_exito($resultado);
  
} catch (PDOException $e) {
    error_log("Error: " . $e->getMessage());
    json_respuesta_error("Error al procesar");
}
?>
```

---

## ⚠️ Errores Comunes

| Error                                 | Solución                                                         |
| ------------------------------------- | ----------------------------------------------------------------- |
| `PDOException: SQLSTATE[08006]`     | BD no conecta. Verificar credenciales `config/conexion.php`     |
| `Undefined index: $_POST['nombre']` | Input HTML tiene name incorrecto. Verificar match con handler     |
| `SQL Syntax error near '?'`         | Query parametrizada incorrecta. Revisar `execute([...])`        |
| `UNIQUE constraint fails`           | DNI duplicado. Verificar validación en handler                   |
| `Integrity constraint: Foreign Key` | ID referenciado no existe. Verificar relaciones BD                |
| Respuesta vacía                      | Función `json_respuesta_*` nunca se llama. Revisar exit/return |

---

## 📝 Notas Importantes

- **NO concatenar SQL** - Siempre prepared statements `?`
- **NO mostrar errores SQL** - Usar mensajes amigables
- **NO confiar cliente** - Validar TODO en servidor
- **SIEMPRE JSON** - Nunca `echo` directo
- **Comentarios en ESPAÑOL** - Para memoria/presentación
- **Variables claras** - `$idTrabajador` no `$id`
- **Transacciones críticas** - Alta trabajador inserta 4 tablas

---

## 🎯 Checklist Final (8 mayo - Corrección)

- [ ] BD conecta sin errores
- [ ] Alta trabajador funciona (inserta en 4 tablas)
- [ ] Búsqueda devuelve resultados
- [ ] Listado dinámico desde BD
- [ ] Disponibilidad calcula correcto
- [ ] Guardar vacaciones NO excede límite
- [ ] Moscosos NO unidos
- [ ] Sin solapamientos
- [ ] Errores claros (no SQL)
- [ ] Respuestas JSON consistentes

---

## 📚 Referencias

- [Validación DNI español](https://www.boe.es)
- [PDO Prepared Statements](https://www.php.net/manual/en/pdo.prepared-statements.php)
- [DateTime PHP](https://www.php.net/manual/en/class.datetime.php)
- [MariaDB Documentation](https://mariadb.org/documentation/)

---

**Autor:** Fran
**Equipo:** Raquel + David + Fran
**Última actualización:** 4 mayo 2026
