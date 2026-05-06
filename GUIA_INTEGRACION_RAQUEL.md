# 🔧 GUÍA DE INTEGRACIÓN: Fran + Raquel

**Objetivo:** Conexión formularios HTML ↔ handlers PHP ↔ Base de Datos

---

## 1. PARA RAQUEL: Modificar Formularios

### ✅ FORMA ANTIGUO (No funciona):
```html
<!-- MALO: Apunta a nada -->
<form action="guardar" method="POST">
    <input type="text" name="nombre">
    <button type="submit">Guardar</button>
</form>
```

### ✅ FORMA CORRECTA (Nueva):
```html
<!-- BIEN: Apunta a handler PHP -->
<form id="form-alta-trabajador" method="POST" action="/logic/trabajadores/guardar_trabajador.php">
    <input type="text" name="nombre" required>
    <input type="email" name="email">
    <button type="submit">Guardar</button>
</form>
```

**O mejor aún (sin recargar página):**
```html
<form id="form-alta-trabajador">
    <input type="text" name="nombre" required>
    <input type="email" name="email">
    <button type="submit">Guardar</button>
</form>

<script>
// Interceptar envío del formulario
document.getElementById('form-alta-trabajador').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const respuesta = await fetch('/logic/trabajadores/guardar_trabajador.php', {
            method: 'POST',
            body: formData
        });
        
        const datos = await respuesta.json();
        
        if (datos.éxito) {
            alert('✓ ' + datos.mensaje);
            // Recargar lista de trabajadores
            location.reload();
        } else {
            alert('❌ Error: ' + datos.error);
        }
        
    } catch (error) {
        console.error('Error:', error);
        alert('Error al guardar');
    }
});
</script>
```

---

## 2. ESTRUCTURAS DE RESPUESTA JSON

**Todos los handlers devuelven JSON con esta estructura:**

### Éxito:
```json
{
    "éxito": true,
    "datos": {
        "id_trabajador": 123,
        "nombre": "Juan García",
        "dni": "12345678A"
    },
    "mensaje": "Trabajador creado correctamente",
    "error": null
}
```

### Error:
```json
{
    "éxito": false,
    "error": "El DNI ya existe en el sistema",
    "datos": null,
    "mensaje": null
}
```

### Errores de Validación:
```json
{
    "éxito": false,
    "error": "Errores de validación",
    "validacion": {
        "dni": "Formato DNI inválido (ej: 12345678A)",
        "email": "Email inválido"
    },
    "datos": null
}
```

---

## 3. ENDPOINTS (URLs) DISPONIBLES

### Trabajadores

#### **POST /logic/trabajadores/guardar_trabajador.php**
- **Qué hace:** Crea nuevo trabajador
- **Parámetros POST requeridos:**
  - `nombre` (string)
  - `apellido1` (string)
  - `apellido2` (string, opcional)
  - `dni` (string)
  - `genero` (0 o 1)
  - `email` (string, opcional)
  - `telefono` (string, opcional)
  - `direccion` (string, opcional)
  - `codigo_postal` (string, opcional)
  - `municipio` (string, opcional)
  - `provincia` (string, opcional)
  - `fecha_inicio` (YYYY-MM-DD)
  - `fecha_fin` (YYYY-MM-DD)
  - `dias_vacaciones` (int, default 22)
  - `dias_moscosos` (int, default 6)
- **Respuesta:** Éxito con id_persona e id_contrato

#### **GET /logic/trabajadores/buscar_trabajadores.php**
- **Qué hace:** Busca trabajadores
- **Parámetros GET:**
  - `q` (string): término búsqueda (nombre o DNI)
- **Respuesta:** Array de trabajadores encontrados

#### **GET /logic/trabajadores/obtener_trabajador.php**
- **Qué hace:** Obtiene datos completos de 1 trabajador
- **Parámetros GET:**
  - `id` (int): id_persona
- **Respuesta:** Objeto con datos personales + dirección + contrato

### Vacaciones

#### **GET /logic/vacaciones/obtener_trabajadores_activos.php**
- **Qué hace:** Lista de trabajadores con contrato vigente
- **Parámetros GET:** ninguno
- **Respuesta:** Array de trabajadores (para dropdown)

#### **GET /logic/vacaciones/obtener_disponibilidad.php**
- **Qué hace:** Calcula días disponibles
- **Parámetros GET:**
  - `id_contrato` (int)
- **Respuesta:** 
  ```json
  {
      "vacaciones": {"total": 22, "cogidos": 5, "disponibles": 17},
      "moscosos": {"total": 6, "cogidos": 0, "disponibles": 6},
      "festivos_periodo": ["2026-05-01", "2026-08-15"],
      "total_festivos": 2
  }
  ```

#### **POST /logic/vacaciones/guardar_vacaciones.php**
- **Qué hace:** Registra días de vacaciones/moscosos
- **Parámetros POST requeridos:**
  - `id_contrato` (int)
  - `fecha_inicio` (YYYY-MM-DD)
  - `fecha_fin` (YYYY-MM-DD)
  - `tipo_dia` ('vacaciones' o 'moscosos')
  - `notas` (string, opcional)
- **Respuesta:** Éxito con días registrados

---

## 4. EJEMPLO COMPLETO: Flujo Alta Trabajador

```html
<!-- alta-trabajador.php (Raquel) -->

<h1>Dar de alta nuevo trabajador</h1>

<form id="form-alta">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido1" placeholder="Primer apellido" required>
    <input type="text" name="apellido2" placeholder="Segundo apellido (opcional)">
    <input type="text" name="dni" placeholder="DNI (ej: 12345678A)" required>
    
    <select name="genero" required>
        <option value="">Seleccionar género</option>
        <option value="0">Hombre</option>
        <option value="1">Mujer</option>
    </select>
    
    <input type="email" name="email" placeholder="Email (opcional)">
    <input type="tel" name="telefono" placeholder="Teléfono (opcional)">
    <input type="text" name="direccion" placeholder="Dirección (opcional)">
    <input type="text" name="codigo_postal" placeholder="Código postal">
    <input type="text" name="municipio" placeholder="Municipio">
    <input type="text" name="provincia" placeholder="Provincia">
    
    <input type="date" name="fecha_inicio" required>
    <input type="date" name="fecha_fin" required>
    <input type="number" name="dias_vacaciones" value="22" required>
    <input type="number" name="dias_moscosos" value="6" required>
    
    <button type="submit">Crear Trabajador</button>
</form>

<div id="mensaje"></div>

<script>
document.getElementById('form-alta').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const res = await fetch('/logic/trabajadores/guardar_trabajador.php', {
            method: 'POST',
            body: formData
        });
        
        const json = await res.json();
        const div = document.getElementById('mensaje');
        
        if (json.éxito) {
            div.innerHTML = `<div class="alert alert-success">✓ ${json.mensaje}</div>`;
            e.target.reset();
            // Recargar lista después de 2 segundos
            setTimeout(() => location.reload(), 2000);
        } else if (json.validacion) {
            // Errores de validación
            let msg = '<div class="alert alert-danger"><ul>';
            for (const [campo, error] of Object.entries(json.validacion)) {
                msg += `<li><strong>${campo}:</strong> ${error}</li>`;
            }
            msg += '</ul></div>';
            div.innerHTML = msg;
        } else {
            div.innerHTML = `<div class="alert alert-danger">❌ ${json.error}</div>`;
        }
        
    } catch (error) {
        console.error(error);
        document.getElementById('mensaje').innerHTML = 
            '<div class="alert alert-danger">Error de conexión</div>';
    }
});
</script>
```

---

## 5. EJEMPLO: Buscar Trabajadores (Tabla Dinámica)

```html
<!-- index.php (Raquel) -->

<input type="text" id="busqueda" placeholder="Buscar por nombre o DNI">

<table id="tabla-trabajadores">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tbody">
    </tbody>
</table>

<script>
const inputBusqueda = document.getElementById('busqueda');
const tbody = document.getElementById('tbody');

inputBusqueda.addEventListener('input', async (e) => {
    const q = e.target.value.trim();
    
    if (q.length < 2) {
        tbody.innerHTML = '';
        return;
    }
    
    try {
        const res = await fetch(`/logic/trabajadores/buscar_trabajadores.php?q=${q}`);
        const json = await res.json();
        
        if (json.éxito && json.datos.length > 0) {
            tbody.innerHTML = json.datos.map(t => `
                <tr>
                    <td>${t.nombre} ${t.apellido1} ${t.apellido2 || ''}</td>
                    <td>${t.dni}</td>
                    <td>${t.estado}</td>
                    <td>
                        <a href="#" onclick="editarDias(${t.id})">Editar Días</a>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="4">No encontrado</td></tr>';
        }
        
    } catch (error) {
        console.error(error);
    }
});

function editarDias(id) {
    // Redirigir a registro-vacaciones.php?id=X
    window.location.href = `/registro-vacaciones.php?id=${id}`;
}
</script>
```

---

## 6. CHECKLIST DE INTEGRACIÓN

- [ ] Todos los formularios apuntan a `/logic/...`
- [ ] Se usa `fetch()` para enviar sin recargar
- [ ] Se valida respuesta JSON antes de procesar
- [ ] Se muestran errores claros al usuario
- [ ] Se recargan tablas después de guardar
- [ ] Los inputs tienen `name` correcto (match con `$_POST`)
- [ ] Se prueban todos los flujos manualmente

---

## 7. DEPURACIÓN (Si algo no funciona)

**1. Ver error en consola browser:**
```javascript
// Abrir DevTools (F12) → Console
// Ver si hay errores de fetch
```

**2. Ver error en servidor:**
```bash
# Ver logs PHP (si existen)
tail -f /var/log/apache2/error.log
```

**3. Testear endpoint directamente:**
```bash
# Terminal:
curl -X POST http://localhost/proyecto-vacaciones/logic/trabajadores/guardar_trabajador.php \
  -d "nombre=Juan&apellido1=Garcia&dni=12345678A&genero=0&fecha_inicio=2026-05-01&fecha_fin=2026-05-31"

# Debe devolver JSON
```

**4. Verificar conexión BD:**
```php
// En config/conexion.php, descomenta:
echo "✓ Conectado correctamente a: " . $config['base_datos'];
exit;
```

---

## 8. CONFIGURACIÓN SERVIDOR (Si no funciona)

**Asegúrate que:**
1. PHP está instalado: `php -v`
2. Servidor web corre: `apache2ctl status` o `service nginx status`
3. MariaDB está corriendo: `mysql -u root -p`
4. Permisos carpetas: `chmod 755 logic/`
5. `.htaccess` permite acceso a `/logic/` (si usa Apache)

