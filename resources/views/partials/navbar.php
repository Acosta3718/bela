<?php use App\Core\Auth; $roles = array_map('strtolower', Auth::roles()); $esAdmin = in_array('administrador', $roles) || in_array('admin', $roles); ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/bela/public">SalonPro</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/bela/public/funcionarios">Funcionarios</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/servicios">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/clientes">Clientes</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/proveedores">Proveedores</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/citas">Citas</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/ventas">Ventas</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/gastos">Gastos</a></li>
                <li class="nav-item"><a class="nav-link" href="/bela/public/pagos">Pagos</a></li>
                <?php if ($esAdmin): ?>
                    <li class="nav-item"><a class="nav-link" href="/bela/public/cuentas">Cuentas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/bela/public/transferencias">Transferencias</a></li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Reportes</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/bela/public/reportes/ganancias">Ganancias</a></li>
                        <li><a class="dropdown-item" href="/bela/public/reportes/pagos">Pagos Funcionarios</a></li>
                        <li><a class="dropdown-item" href="/bela/public/reportes/extracto-cuentas">Extracto de cuentas</a></li>
                        <li><a class="dropdown-item" href="/bela/public/reportes/disponibilidad">Disponibilidad por días</a></li>
                    </ul>
                </li>
            </ul>
            <div class="d-flex">
                <a class="btn btn-outline-light" href="/bela/public/logout">Salir</a>
            </div>
        </div>
    </div>
</nav>