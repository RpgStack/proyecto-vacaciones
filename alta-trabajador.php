<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<!-- He usado el AQUÍ! para que sea fácil encontrar los comentarios. -->
<!-- El formulario tiene todos los name y he puesto required menos en el segundo apellido, tlf y fin de contrato -->
<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
<!--AQUÍ! Este alerta sirve para cuando se gestione un alta de trabajador aparezca un msj durante 5 segundos. Lo puedes activar quitando la clase d-none  --> 
            <div id="alerta-exito" class="alert alert-success py-2 px-3 mb-4 border-0 border-start border-4 border-success shadow-sm d-none" role="alert">
                <small class="fw-bold">Éxito:</small> <small>Trabajador registrado correctamente.</small>
            </div>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Alta de Nuevo Trabajador</h4>
                </div>
                <div class="card-body">
<!-- AQUÍ! He creado la carpeta logic y el nombre de la carpeta guardar-trabajador es ejemplo y solo quería dejar el método post -->
                    <form action="logic/guardar_trabajador.php" method="POST">
                        
                        <h5 class="text-muted border-bottom pb-2">Datos Personales</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Nombre</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Primer Apellido</label>
                                <input type="text" name="apellido1" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Segundo Apellido</label>
                                <input type="text" name="apellido2" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">DNI / NIE</label>
                                <input type="text" name="dni" class="form-control" placeholder="12345678X" required>
                            </div>
                            <div class="col-md-8">
<!-- AQUÍ! Para seguir con la lógica de la BBDD que es un boleano, he dado valor 1 para hombre y 0 para mujer.-->
                                <label class="form-label d-block">Género</label>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="radio" name="genero" id="generoH" value="0" required>
                                    <label class="form-check-label" for="generoH">Hombre</label>
                                </div>
                                <div class="form-check form-check-inline mt-2">
                                    <input class="form-check-input" type="radio" name="genero" id="generoM" value="1" required>
                                    <label class="form-check-label" for="generoM">Mujer</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" name="telefono" class="form-control">
                            </div>
                        </div>

                        <h5 class="text-muted border-bottom pb-2">Domicilio</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">Dirección Completa</label>
                                <input type="text" name="direccion" class="form-control" required placeholder="Calle, número, piso, puerta...">
                            </div>
                        </div>

                        <h5 class="text-muted border-bottom pb-2">Configuración de Vacaciones</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Inicio Contrato</label>
                                <input type="date" name="fecha_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fin Contrato (Si aplica)</label>
                                <input type="date" name="fecha_fin" class="form-control">
                            </div>
<!-- AQUÍ! Estos serían los días por defecto -->
                            <div class="col-md-3">
                                <label class="form-label">Días Vacaciones</label>
                                <input type="number" name="dias_vacaciones" class="form-control" value="22" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Días Moscosos</label>
                                <input type="number" name="dias_moscosos" class="form-control" value="6" required>
                            </div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="reset" class="btn btn-secondary">Limpiar</button>
                            <button type="submit" class="btn btn-primary">Guardar Trabajador</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="assets/js/main.js"></script>

<?php include 'includes/footer.php'; ?>