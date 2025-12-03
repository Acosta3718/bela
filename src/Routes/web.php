<?php

use App\Controllers\AuthController;
use App\Controllers\CitasController;
use App\Controllers\ClientesController;
use App\Controllers\DashboardController;
use App\Controllers\FuncionariosController;
use App\Controllers\GastosController;
use App\Controllers\PagosController;
use App\Controllers\ReportesController;
use App\Controllers\ServiciosController;
use App\Controllers\ProveedoresController;
use App\Controllers\CuentasController;
use App\Controllers\VentasController;
use App\Controllers\TransferenciasController;

$router->get('/', [DashboardController::class, 'index']);

$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'authenticate']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/funcionarios', [FuncionariosController::class, 'index']);
$router->get('/funcionarios/crear', [FuncionariosController::class, 'create']);
$router->post('/funcionarios', [FuncionariosController::class, 'store']);
$router->get('/funcionarios/editar', [FuncionariosController::class, 'edit']);
$router->post('/funcionarios/actualizar', [FuncionariosController::class, 'update']);
$router->post('/funcionarios/eliminar', [FuncionariosController::class, 'destroy']);

$router->get('/servicios', [ServiciosController::class, 'index']);
$router->get('/servicios/crear', [ServiciosController::class, 'create']);
$router->post('/servicios', [ServiciosController::class, 'store']);
$router->get('/servicios/editar', [ServiciosController::class, 'edit']);
$router->post('/servicios/actualizar', [ServiciosController::class, 'update']);
$router->post('/servicios/eliminar', [ServiciosController::class, 'destroy']);

$router->get('/clientes', [ClientesController::class, 'index']);
$router->get('/clientes/buscar', [ClientesController::class, 'buscar']);
$router->get('/clientes/crear', [ClientesController::class, 'create']);
$router->post('/clientes', [ClientesController::class, 'store']);
$router->post('/clientes/inline', [ClientesController::class, 'storeInline']);
$router->get('/clientes/editar', [ClientesController::class, 'edit']);
$router->post('/clientes/actualizar', [ClientesController::class, 'update']);
$router->post('/clientes/eliminar', [ClientesController::class, 'destroy']);

$router->get('/proveedores', [ProveedoresController::class, 'index']);
$router->get('/proveedores/buscar', [ProveedoresController::class, 'buscar']);
$router->get('/proveedores/crear', [ProveedoresController::class, 'create']);
$router->post('/proveedores', [ProveedoresController::class, 'store']);
$router->get('/proveedores/editar', [ProveedoresController::class, 'edit']);
$router->post('/proveedores/actualizar', [ProveedoresController::class, 'update']);
$router->post('/proveedores/eliminar', [ProveedoresController::class, 'destroy']);

$router->get('/cuentas', [CuentasController::class, 'index']);
$router->get('/cuentas/crear', [CuentasController::class, 'create']);
$router->post('/cuentas', [CuentasController::class, 'store']);
$router->get('/cuentas/editar', [CuentasController::class, 'edit']);
$router->post('/cuentas/actualizar', [CuentasController::class, 'update']);
$router->post('/cuentas/eliminar', [CuentasController::class, 'destroy']);

$router->get('/citas', [CitasController::class, 'index']);
$router->get('/citas/crear', [CitasController::class, 'create']);
$router->post('/citas', [CitasController::class, 'store']);
$router->get('/citas/editar', [CitasController::class, 'edit']);
$router->post('/citas/actualizar', [CitasController::class, 'update']);
$router->post('/citas/eliminar', [CitasController::class, 'destroy']);
$router->get('/citas/disponibilidad', [CitasController::class, 'disponibilidad']);

$router->get('/ventas', [VentasController::class, 'index']);
$router->get('/ventas/crear', [VentasController::class, 'create']);
$router->post('/ventas', [VentasController::class, 'store']);
$router->get('/ventas/editar', [VentasController::class, 'edit']);
$router->post('/ventas/actualizar', [VentasController::class, 'update']);
$router->post('/ventas/eliminar', [VentasController::class, 'destroy']);
$router->post('/ventas/cobro', [VentasController::class, 'registrarCobro']);

$router->get('/gastos', [GastosController::class, 'index']);
$router->get('/gastos/crear', [GastosController::class, 'create']);
$router->post('/gastos', [GastosController::class, 'store']);
$router->get('/gastos/editar', [GastosController::class, 'edit']);
$router->post('/gastos/actualizar', [GastosController::class, 'update']);
$router->post('/gastos/eliminar', [GastosController::class, 'destroy']);

$router->get('/pagos', [PagosController::class, 'index']);
$router->get('/pagos/crear', [PagosController::class, 'create']);
$router->get('/pagos/ventas', [PagosController::class, 'ventas']);
$router->post('/pagos', [PagosController::class, 'store']);
$router->post('/pagos/eliminar', [PagosController::class, 'destroy']);

$router->get('/transferencias', [TransferenciasController::class, 'index']);
$router->get('/transferencias/crear', [TransferenciasController::class, 'create']);
$router->post('/transferencias', [TransferenciasController::class, 'store']);
$router->get('/transferencias/ver', [TransferenciasController::class, 'show']);
$router->get('/transferencias/editar', [TransferenciasController::class, 'edit']);
$router->post('/transferencias/actualizar', [TransferenciasController::class, 'update']);
$router->post('/transferencias/eliminar', [TransferenciasController::class, 'destroy']);

$router->get('/reportes/ganancias', [ReportesController::class, 'ganancias']);
$router->get('/reportes/pagos', [ReportesController::class, 'pagosFuncionarios']);
$router->get('/reportes/extracto-cuentas', [ReportesController::class, 'extractoCuentas']);
$router->get('/reportes/disponibilidad', [ReportesController::class, 'disponibilidadPorDias']);