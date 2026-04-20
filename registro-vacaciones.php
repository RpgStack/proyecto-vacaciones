<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- He usado el AQUÍ! para que sea fácil encontrar los comentarios.
 He marcado donde habría que meter lógica y creo que todo tiene ID, NAME...
 A esta hoja se llega cuando desde LISTADO (index), una vez buscas al trabajador le das a asignar, ahora mismo funciona por href="registro-vacaciones.php?id=1"  -->

<main class="container py-4">
    <div class="row justify-content-center">
        <section class="col-md-8">
<!-- AQUÍ! Este es el cuadro de la cabecera que debería arrastrar los datos de la persona que se selecciona en LISTADO y así saber a quién se le están asignando días -->
            <div class="card shadow-sm mb-4 border-start border-4 border-primary">
                <div class="card-body bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <p class="text-primary fw-bold mb-1">Registrando para:</p>
<!-- AQUÍ! parte dinámica nombre, dni, días de vacas y moscosos  :)  -->
                            <h2 class="h4 mb-0">Juan Ejemplo García</h2>
                            <small class="text-muted">DNI: 12345678X</small>
                        </div>
                        
                        <aside class="col-md-5 border-start">
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <p class="h4 mb-0 text-info">15</p>
                                    <small class="text-muted">Vacaciones</small>
                                </div>
                                <div>
                                    <p class="h4 mb-0 text-warning">4</p>
                                    <small class="text-muted">Moscosos</small>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-dark text-white text-center">
                    <p class="h5 mb-0">Formulario de Registro</p>
                </div>
                
                <div class="card-body">

<!-- AQUÍ! He creado la carpeta logic pero no hay nada dentro. Puedes poner el nombre que quieras a la carpeta y a los ficheros  -->
                    <form action="logic/guardar_vacaciones.php" method="POST">
<!-- AQUÍ! el value lo he puesto para el ejemplo   -->
                        <input type="hidden" name="id_trabajador" value="1">

                        <fieldset>
                            <div class="mb-3">
                                <label for="tipo_dia" class="form-label fw-bold">Tipo de día</label>
<!-- AQUÍ!    -->
                                <select id="tipo_dia" name="tipo_dia" class="form-select" required>
                                    <option value="vacaciones">Vacaciones</option>
                                    <option value="moscoso">Moscoso</option>
                                </select>
                                <div class="form-text text-danger">
                                    <small>* Los moscosos no pueden unirse a vacaciones.</small>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
<!-- AQUÍ! Este es el calendario y creo que habría que restar los sábados y los domingos pq creo que no cuentan   -->
                                    <label for="fecha_inicio" class="form-label fw-bold">Fecha Inicio</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_fin" class="form-label fw-bold">Fecha Fin</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required>
                                </div>
                            </div>
<!-- AQUÍ! Esto es 100% optativo, está puesto para ocupar hueco y pq me parece útil    -->
                            <div class="mb-3">
                                <label for="notas" class="form-label fw-bold">Observaciones</label>
                                <textarea id="notas" name="notas" class="form-control" rows="2" placeholder="Opcional..."></textarea>
                            </div>
                        </fieldset>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-dark">Confirmar y Guardar</button>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">Cancelar y volver</a>
                        </div>
                    </form>
                </div>
            </div>

        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>