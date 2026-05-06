<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- He usado el AQUÍ! para que sea fácil encontrar los comentarios. -->

<main class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Listado de Trabajadores</h2>
        <a href="alta-trabajador.php" class="btn btn-success">+ Nuevo Trabajador</a>
    </div>

   <div class="card mb-4">
    <div class="card-body">
        <!-- Esta es la parte del BUSCADOR  --> 
        <form action="index.php" method="GET" class="row g-2">
            <div class="col-md-10">
                <input type="text" name="busqueda" class="form-control" placeholder="Buscar por nombre o DNI...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Buscar</button>
            </div>
        </form>

    </div>
</div>

    <div class="table-responsive shadow-sm">
<!-- AQUÍ! esto es solo la estructura de la tabla -->
        <table class="table table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>DNI</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Vacaciones (Restantes)</th>
                    <th>Días Libres (Restantes)</th>
                    <th>Acciones</th>
                </tr>
            </thead>



<tbody id="tabla-trabajadores">
<!-- AQUÍ! esto está planteado por si se busca alguien en la BBDD pero no existe, sería quitar la clase d-none y el mensaje aparece. Lo he integrado en la tabla para que no rompa el diseño, no es perfecto pero sí funcional.  -->
    <tr class="d-none">
    <td colspan="6" class="text-center py-4 text-muted">
        <i class="bi bi-search mb-2"></i>
        <p class="mb-0">No se han encontrado trabajadores con ese nombre o DNI.</p>
    </td>
</tr>
<!-- AQUÍ! Aquí habría que inyectar dinámicamente. He hecho una estructura de ejemplo para que la puedas usar cuando se conecte con la BBDD  -->
    <tr>
        <td>12345678X</td>

        <td>Juan Ejemplo García</td>

        <td>juan@empresa.com</td>

        <td>
            <span class="badge bg-info text-dark">15 / 22</span>
        </td>

        <td>
            <span class="badge bg-warning text-dark">2 / 6</span>
        </td>

        <td class="text-center" style="min-width: 180px;">
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
<!-- AQUÍ esto es lo que hace que vayamos a registro-vacaciones con el método get por el ? -->
                <a href="registro-vacaciones.php?id=1" class="btn btn-sm btn-primary text-nowrap">Asignar Días</a>
<!--AQUÍ yo dejaría este botón solo para la maquetación para no complicarnos al menos de momento. A futuro se puede hacer que corrijan datos de la BBDD por si se pone un apellido mal -->
                <button class="btn btn-sm btn-outline-secondary">Editar</button>
            </div>
        </td>
    </tr>
</tbody>

        </table>
    </div>
</main>

<?php include 'includes/footer.php'; ?>