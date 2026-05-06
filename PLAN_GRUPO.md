# 📋 PLAN - Gestión de Vacaciones | 4-8 Mayo 2026

**Estado**: Backend 100% ✅ | Frontend 0% ⏳ | BD 0% ⏳

---

## 🎯 LO QUE HIZO FRAN (Hoy - 4 Mayo)

### ✅ Completado - Capa de Acceso a Datos (Backend)

**Estructura creada:**
```
config/
  └── conexion.php          (PDO a MariaDB)
logic/
  ├── trabajadores/
  │   ├── guardar_trabajador.php    (POST - alta con transacción)
  │   ├── buscar_trabajadores.php   (GET - búsqueda por nombre/DNI)
  │   └── obtener_trabajador.php    (GET - detalles completos)
  ├── vacaciones/
  │   ├── obtener_trabajadores_activos.php  (GET - dropdown)
  │   ├── obtener_disponibilidad.php        (GET - días restantes)
  │   └── guardar_vacaciones.php            (POST - CRÍTICO con 5 validaciones)
  ├── validation/
  │   └── Validador.php      (9 métodos: DNI, email, fechas, etc)
  └── utils/
      └── respuestas.php     (JSON centralizado)
```

**Características de Seguridad:**
- ✅ Prepared statements en 100% de queries (previene SQL Injection)
- ✅ Transacciones ACID (alta trabajador, guardar vacaciones)
- ✅ Validación triple capa (HTML5, PHP, BD)
- ✅ Respuestas JSON estándar

**7 Endpoints Listos:**
1. `POST /logic/trabajadores/guardar_trabajador.php` - Alta
2. `GET /logic/trabajadores/buscar_trabajadores.php?q=juan` - Búsqueda
3. `GET /logic/trabajadores/obtener_trabajador.php?id=1` - Detalles
4. `GET /logic/vacaciones/obtener_trabajadores_activos.php` - Dropdown
5. `GET /logic/vacaciones/obtener_disponibilidad.php?id_contrato=1` - Disponibilidad
6. `POST /logic/vacaciones/guardar_vacaciones.php` - Guardar vacaciones
7. **EXTRA**: Panel testing interactivo (`test-api.html`)

---

## 🔄 PRÓXIMOS PASOS - SECUENCIA (5-8 Mayo)

### 1️⃣ DAVID - URGENTE (Hoy/Mañana)
**¿QUÉ NECESITAMOS?**
- ✋ **Credenciales BD:**
  - Host MariaDB
  - Usuario
  - Contraseña
  - Nombre base datos

**¿QUÉ VA A HACER?**
- [ ] Proporciona credenciales a Fran
- [ ] **Crea 23 Stored Procedures** (están documentadas en `INSTRUCCIONES_SP_PARA_DAVID.md`)
- [ ] Timeline: Para el 8 mayo (antes de corrección)

**Ayuda**: Guía completa en `INSTRUCCIONES_SP_PARA_DAVID.md` con:
- Parámetros de cada SP
- Query original (copy-paste)
- Ejemplo de uso

---

### 2️⃣ FRAN - Backend Tests (5 Mayo)
**¿QUÉ NECESITO?**
- Credenciales de David (👆)

**¿QUÉ VOY A HACER?**
- [ ] Actualizar `config/conexion.php` con credenciales
- [ ] Testear panel interactivo: `http://localhost:8000/test-api.html`
- [ ] Verificar 7 endpoints responden ✓
- [ ] Push a rama GitHub `fran/backend-capa-acceso-datos`

**Timeline**: 6 Mayo

---

### 3️⃣ RAQUEL - Frontend (6-7 Mayo)
**¿QUÉ NECESITA?**
- Backend testeo completo (Fran)
- Guía de integración: `GUIA_INTEGRACION_RAQUEL.md`

**¿QUÉ VA A HACER?**
- [ ] Modificar `alta-trabajador.php` 
  - Reemplazar form action por `fetch()` a `/logic/trabajadores/guardar_trabajador.php`
  - Validación HTML5 + feedback JSON
- [ ] Modificar `index.php` (búsqueda)
  - Buscar en tiempo real con `/logic/trabajadores/buscar_trabajadores.php`
- [ ] Modificar `registro-vacaciones.php` (crítico)
  - Obtener disponibilidad: `/logic/vacaciones/obtener_disponibilidad.php`
  - Guardar: `/logic/vacaciones/guardar_vacaciones.php`

**Ejemplo proporcionado en GUIA_INTEGRACION_RAQUEL.md**:
```javascript
// Guardar trabajador
fetch('http://localhost:8000/logic/trabajadores/guardar_trabajador.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    nombre: document.getElementById('nombre').value,
    apellido1: document.getElementById('apellido1').value,
    // ... más campos
  })
})
.then(r => r.json())
.then(data => {
  if (data.éxito) {
    console.log('✓ Trabajador creado:', data.datos);
  } else {
    console.error('✗ Error:', data.error);
  }
});
```

**Timeline**: 7 Mayo

---

### 4️⃣ GRUPO - Corrección (8 Mayo)
**Estado esperado:**
- ✅ Backend: 14 archivos, 3,275 líneas
- ✅ Frontend: Formularios conectados
- ✅ BD: 23 SPs creados
- ✅ Seguridad: Prepared statements + validaciones
- ✅ Tests: Panel interactivo funcionando

**Presentación:**
1. Mostrar arquitectura (3 capas)
2. Demo: Panel testing
3. Demo: Formularios (alta trabajador + vacaciones)
4. Explicar: Prepared statements, transacciones, SPs

---

## 📁 ARCHIVOS DE AYUDA

- **Para David**: `INSTRUCCIONES_SP_PARA_DAVID.md` (23 SPs documentadas)
- **Para Raquel**: `GUIA_INTEGRACION_RAQUEL.md` (fetch() ejemplos)
- **General**: `README.md` (arquitectura, seguridad, workflows)
- **Testing**: `test-api.html` (panel interactivo en `localhost:8000/test-api.html`)

---

## 🌳 GIT

**Rama actual**: `fran/backend-capa-acceso-datos`
**Commit**: 20ad407 - Implementación capa acceso datos (14 archivos)

**Para colaborar**:
```bash
# Todos descargan
git clone https://github.com/RpgStack/proyecto-vacaciones.git
git checkout fran/backend-capa-acceso-datos

# Cada uno en su rama cuando sea necesario:
git checkout -b david/stored-procedures
git checkout -b raquel/frontend-integration
```

---

## ⏱️ TIMELINE

| Fecha | Quién | Qué |
|-------|-------|-----|
| **4 Mayo** | Fran | ✅ Backend completado |
| **5 Mayo** | David | Proporciona credenciales |
| **5-6 Mayo** | Fran | Testea backend |
| **6-7 Mayo** | Raquel | Integra frontend |
| **6-7 Mayo** | David | Crea 23 SPs |
| **8 Mayo** | TODOS | Corrección/Review |
| **31 Mayo** | TODOS | Entrega final |

---

## 🚀 PRÓXIMO: David

**Espera tu respuesta para:**
1. Credenciales BD
2. Confirmar que creará los SPs

¿Questions? @channel

---

*Actualizado: 4 Mayo 2026*
*Estado: 100% Backend listo para testing*
