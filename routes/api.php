<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Crud_basic\Users\ClienteController;
use App\Http\Controllers\Crud_basic\Users\AsistenteVentasController;
use App\Http\Controllers\Crud_basic\Users\RecepcionistaController;
use App\Http\Controllers\Crud_basic\Users\EspecialistaController;

// Rutas de autenticación para Clientes
Route::post('/cliente/register', [ClienteController::class, 'register']);
Route::post('/cliente/login', [ClienteController::class, 'login']);

// Rutas protegidas por autenticación de Sanctum para Clientes
Route::middleware('auth:cliente_api')->group(function () {

    Route::get('/cliente/perfil', [ClienteController::class, 'getAuthClientProfile']); // <-- NUEVA: Obtener perfil del cliente autenticado
    Route::post('/cliente/update-perfil', [ClienteController::class, 'updateAuthClientProfile']); // <-- NUEVA: Actualizar perfil del cliente autenticado
    
    Route::delete('/cliente/eliminar-cuenta', [ClienteController::class, 'deleteAuthClientAccount']); // <-- NUEVA: Eliminar cuenta del cliente autenticado

    Route::get('/cliente/logout', [ClienteController::class, 'logout']);
    Route::get('/cliente/autenticado', [ClienteController::class, 'userAuth']);
    Route::put('/cliente/update', [ClienteController::class, 'update']);

    Route::delete('/cliente/delete', [ClienteController::class, 'delete']);
});

// Rutas para eliminar, actualizar y obtener información del cliente (el recepcionista y el cliente pueden hacer esto)

Route::get('/cliente/search-cedula', [ClienteController::class, 'getByCedula']);;

Route::get('/cliente/search-nombre', [ClienteController::class, 'getByNombre']);


Route::get('/cliente/all', [ClienteController::class, 'getAll']);




// Rutas de autenticación para Asistentes de Ventas
Route::post('/asistente/login', [AsistenteVentasController::class, 'login']);
Route::post('/asistente/register', [AsistenteVentasController::class, 'register']);

// Rutas protegidas por autenticación de Sanctum para Asistentes de Ventas
Route::middleware('auth:asistenteVentas_api')->group(function () {
    // Puedes especificar el guard directamente si lo prefieres: 'auth:asistente_api'
    Route::get('/asistente/logout', [AsistenteVentasController::class, 'logout']);
    Route::get('/asistente/autenticado', [AsistenteVentasController::class, 'getAutenticado']);
});

// Rutas para eliminar, actualizar y obtener información del asistente de ventas (el asistente de ventas y una cuenta de administrador pueden hacer esto)
Route::get('/asistente/search-cedula', [AsistenteVentasController::class, 'getByCedula']);

Route::get('/asistente/search-email', [AsistenteVentasController::class, 'getByEmail']);

Route::get('/asistente/search-nombre', [AsistenteVentasController::class, 'getByNombre']);

Route::put('/asistente/update', [AsistenteVentasController::class, 'update']);

Route::delete('/asistente/delete', [AsistenteVentasController::class, 'delete']);

Route::get('/asistente/all', [AsistenteVentasController::class, 'getAll']);




// Rutas de autenticación para Recepcionistas
Route::post('/recepcionista/register', [RecepcionistaController::class, 'register']);
Route::post('/recepcionista/login', [RecepcionistaController::class, 'login']);

// Rutas protegidas por autenticación de Sanctum para Recepcionistas
Route::middleware('auth:recepcionista_api')->group(function () {
    Route::get('/recepcionista/logout', [RecepcionistaController::class, 'logout']);
    Route::get('/recepcionista/autenticado', [RecepcionistaController::class, 'userAuth']);
    Route::get('/recepcionista/perfil', [RecepcionistaController::class, 'getAuthRecepcionistaProfile']); // <-- NUEVA: Obtener perfil
    Route::post('/recepcionista/update-perfil', [RecepcionistaController::class, 'updateAuthRecepcionistaProfile']); // <-- NUEVA: Actualizar perfil
    Route::delete('/recepcionista/eliminar-cuenta', [RecepcionistaController::class, 'deleteAuthRecepcionistaAccount']); // <-- NUEVA: Eliminar cuenta
});

// Rutas para eliminar, actualizar y obtener información del recepcionista
Route::get('/recepcionista/search-cedula', [RecepcionistaController::class, 'getByCedula']);
Route::get('/recepcionista/search-email', [RecepcionistaController::class, 'getByEmail'])
    ->middleware('auth:recepcionista_api');
Route::get('/recepcionista/search-nombre', [RecepcionistaController::class, 'getByNombre'])
    ->middleware('auth:recepcionista_api');
Route::put('/recepcionista/update', [RecepcionistaController::class, 'update'])
    ->middleware('auth:recepcionista_api');
Route::delete('/recepcionista/delete', [RecepcionistaController::class, 'delete'])
    ->middleware('auth:recepcionista_api');
Route::get('/recepcionista/all', [RecepcionistaController::class, 'getAll']);







// Rutas de autenticación para Especialistas
Route::post('/especialista/register', [EspecialistaController::class, 'register']);
Route::post('/especialista/login', [EspecialistaController::class, 'login']);

// Rutas protegidas por autenticación de Sanctum para Especialistas
Route::middleware('auth:especialista_api')->group(function () {
    Route::get('/especialista/logout', [EspecialistaController::class, 'logout']);
    Route::get('/especialista/autenticado', [EspecialistaController::class, 'user']);
});

// Rutas para eliminar, actualizar y obtener información del especialista
Route::get('/especialista/search-cedula', [EspecialistaController::class, 'getByCedula']);
Route::get('/especialista/search-email', [EspecialistaController::class, 'getByEmail']);
Route::get('/especialista/search-nombre', [EspecialistaController::class, 'getByNombre']);
Route::put('/especialista/update', [EspecialistaController::class, 'update'])
    ->middleware('auth:especialista_api');
Route::delete('/especialista/delete', [EspecialistaController::class, 'delete'])
    ->middleware('auth:especialista_api');
Route::get('/especialista/all', [EspecialistaController::class, 'getAll']);



// Rutas para Citas
use App\Http\Controllers\Crud_basic\Elements\CitaController;

Route::middleware(['auth.client_recep'])->group(function () {
    Route::post('/cita/registrar-propia', [CitaController::class, 'createClientCita']); 
    Route::get('/cita/mis-citas', [CitaController::class, 'getMisCitas']); // <-- NUEVA RUTA PARA CLIENTE
    Route::post('/cita/create', [CitaController::class, 'create']);
    Route::put('/cita/update', [CitaController::class, 'update']);
    Route::delete('/cita/delete', [CitaController::class, 'destroy']);
    Route::get('/cita/all', [CitaController::class, 'getAll']);
    Route::post('/cita/get', [CitaController::class, 'getByCodigo']);
});


// Rutas para Pedidos
use App\Http\Controllers\Crud_basic\Elements\PedidoController;

Route::get('/pedido/mis-pedidos', [PedidoController::class, 'getMisPedidos']);
Route::post('/pedido/registrar-propio', [PedidoController::class, 'createClientPedido']);

Route::post('/pedido/create', [PedidoController::class, 'create']);
Route::put('/pedido/update', [PedidoController::class, 'update']);
Route::delete('/pedido/delete', [PedidoController::class, 'destroy']);
Route::get('/pedido/all', [PedidoController::class, 'getAll']);
Route::post('/pedido/get', [PedidoController::class, 'getByCodigo']);

// Rutas para Informes
use App\Http\Controllers\Crud_basic\Elements\InformeController;

Route::middleware(['auth:recepcionista_api'])->group(function () {
    Route::get('/informe/all', [InformeController::class, 'getAll']);
    Route::post('/informe/get', [InformeController::class, 'getByCodigo']);
    Route::post('/informe/create', [InformeController::class, 'create']);
    Route::put('/informe/update', [InformeController::class, 'update']);
    Route::delete('/informe/delete', [InformeController::class, 'destroy']);
});

// Rutas para Servicios
use App\Http\Controllers\Crud_basic\Elements\ServicioController;

Route::middleware(['auth:recepcionista_api'])->group(function () {
    Route::post('/servicio/create', [ServicioController::class, 'create']);
    Route::post('/servicio/update', [ServicioController::class, 'update']);
    Route::delete('/servicio/delete', [ServicioController::class, 'destroy']);
});

Route::post('/servicio/get', [ServicioController::class, 'getByCodigo']);
Route::get('/servicio/all', [ServicioController::class, 'getAll']);

// Rutas para Productos
use App\Http\Controllers\Crud_basic\Elements\ProductoController;

Route::middleware(['auth:recepcionista_api'])->group(function () {
    Route::post('/producto/create', [ProductoController::class, 'create']);
    Route::put('/producto/update', [ProductoController::class, 'update']);
    Route::delete('/producto/delete', [ProductoController::class, 'destroy']);
});

Route::get('/producto/all', [ProductoController::class, 'getAll']);
Route::post('/producto/get', [ProductoController::class, 'getByCodigo']);

// Rutas para registrar, actualizar, eliminar y obtener información de pedidos
