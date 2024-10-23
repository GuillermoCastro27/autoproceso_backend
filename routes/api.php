<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenCompraCabController;
use App\Http\Controllers\OrdenCompraDetController;
use App\Http\Controllers\CompraCabController;
use App\Http\Controllers\CompraDetController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ItemMarcaController;
use App\Http\Controllers\ItemModeloController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\NacionalidadController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\TipoImpuestoController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PedidosDetalleController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PresupuestosDetalleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get("tipo/read",[TipoController::class,"read"]);
Route::post("tipo/create",[TipoController::class,"store"]);
Route::put("tipo/update/{id}",[TipoController::class,"update"]);
Route::delete("tipo/delete/{id}",[TipoController::class,"destroy"]);

Route::get("items/read",[ItemController::class,"read"]);
Route::post("items/create",[ItemController::class,"store"]);
Route::put("items/update/{id}",[ItemController::class,"update"]);
Route::delete("items/delete/{id}",[ItemController::class,"destroy"]);
Route::post("items/buscar",[ItemController::class,"buscar"]);

Route::post("pedidos/create",[PedidoController::class,"store"]);
Route::get("pedidos/read",[PedidoController::class,"read"]);
Route::put("pedidos/update/{id}",[PedidoController::class,"update"]);
Route::put("pedidos/anular/{id}",[PedidoController::class,"anular"]);
Route::delete("pedidos/delete/{id}",[PedidoController::class,"eliminar"]);
Route::put("pedidos/confirmar/{id}",[PedidoController::class,"confirmar"]);
Route::post("pedidos/buscar",[PedidoController::class,"buscar"]);

Route::post("pedidos-detalles/create",[PedidosDetalleController::class,"store"]);
Route::get("pedidos-detalles/read/{id}",[PedidosDetalleController::class,"read"]);
Route::put("pedidos-detalles/update/{pedido_id}/{item_id}",[PedidosDetalleController::class,"update"]);
Route::delete("pedidos-detalles/delete/{pedido_id}/{item_id}",[PedidosDetalleController::class,"destroy"]);

Route::post("ordencompracab/create",[OrdenCompraCabController::class,"store"]);
Route::get("ordencompracab/read",[OrdenCompraCabController::class,"read"]);
Route::put("ordencompracab/update/{id}",[OrdenCompraCabController::class,"update"]);
Route::put("ordencompracab/anular/{id}",[OrdenCompraCabController::class,"anular"]);
Route::delete("ordencompracab/delete/{id}",[OrdenCompraCabController::class,"eliminar"]);
Route::put("ordencompracab/confirmar/{id}",[OrdenCompraCabController::class,"confirmar"]);
Route::put("ordencompracab/rechazar/{id}",[OrdenCompraCabController::class,"rechazar"]);
Route::put("ordencompracab/aprobar/{id}",[OrdenCompraCabController::class,"aprobar"]);
Route::post("ordencompracab/buscar",[OrdenCompraCabController::class,"buscar"]);


Route::post("compras/create", [CompraCabController::class, "store"]);
Route::get("compras/read", [CompraCabController::class, "read"]);
Route::put("compras/update/{id}", [CompraCabController::class, "update"]);
Route::put("compras/anular/{id}", [CompraCabController::class, "anular"]);
Route::put("compras/rechazar/{id}",[CompraCabController::class,"rechazar"]);
Route::put("compras/aprobar/{id}",[CompraCabController::class,"aprobar"]);
Route::put("compras/confirmar/{id}", [CompraCabController::class, "confirmar"]);

Route::post("compradet/create",[CompraDetController::class,"store"]);
Route::get("compradet/read/{id}",[CompraDetController::class,"read"]);
Route::put("compradet/update/{compra_cab_id}/{item_id}",[CompraDetController::class,"update"]);
Route::delete("compradet/delete/{compra_cab_id}/{item_id}",[CompraDetController::class,"destroy"]);


Route::post("ordencompradet/create",[OrdenCompraDetController::class,"store"]);
Route::get("ordencompradet/read/{id}",[OrdenCompraDetController::class,"read"]);
Route::put("ordencompradet/update/{orden_compra_cab_id}/{item_id}",[OrdenCompraDetController::class,"update"]);
Route::delete("ordencompradet/delete/{orden_compra_cab_id}/{item_id}",[OrdenCompraDetController::class,"destroy"]);

Route::get("paises/read",[PaisController::class,"read"]);
Route::post("paises/create",[PaisController::class,"store"]);
Route::put("paises/update/{id}",[PaisController::class,"update"]);
Route::delete("paises/delete/{id}",[PaisController::class,"destroy"]);


Route::get("ciudades/read",[CiudadController::class,"read"]);
Route::post("ciudades/create",[CiudadController::class,"store"]);
Route::put("ciudades/update/{id}",[CiudadController::class,"update"]);
Route::delete("ciudades/delete/{id}",[CiudadController::class,"destroy"]);
Route::post("ciudades/buscar",[CiudadController::class,"buscar"]);

Route::get("nacionalidad/read",[NacionalidadController::class,"read"]);
Route::post("nacionalidad/create",[NacionalidadController::class,"store"]);
Route::put("nacionalidad/update/{id}",[NacionalidadController::class,"update"]);
Route::delete("nacionalidad/delete/{id}",[NacionalidadController::class,"destroy"]);


Route::get("proveedores/read",[ProveedorController::class,"read"]);
Route::post("proveedores/create",[ProveedorController::class,"store"]);
Route::put("proveedores/update/{id}",[ProveedorController::class,"update"]);
Route::delete("proveedores/delete/{id}",[ProveedorController::class,"destroy"]);
Route::post("proveedores/buscar",[ProveedorController::class,"buscar"]);

Route::get("clientes/read",[ClienteController::class,"read"]);
Route::post("clientes/create",[ClienteController::class,"store"]);
Route::put("clientes/update/{id}",[ClienteController::class,"update"]);
Route::delete("clientes/delete/{id}",[ClienteController::class,"destroy"]);
Route::post("clientes/buscar",[ClienteController::class,"buscar"]);


Route::post("presupuesto/create",[PresupuestoController::class,"store"]);
Route::get("presupuesto/read",[PresupuestoController::class,"read"]);
Route::put("presupuesto/update/{id}",[PresupuestoController::class,"update"]);
Route::put("presupuesto/anular/{id}",[PresupuestoController::class,"anular"]);
Route::put("presupuesto/confirmar/{id}",[PresupuestoController::class,"confirmar"]);
Route::put("presupuesto/rechazar/{id}",[PresupuestoController::class,"rechazar"]);
Route::put("presupuesto/aprobar/{id}",[PresupuestoController::class,"aprobar"]);
Route::post("presupuesto/buscar",[PresupuestoController::class,"buscar"]);


Route::get("presupuestos-detalles/read/{id}",[PresupuestosDetalleController::class,"read"]);
Route::post("presupuestos-detalles/create",[PresupuestosDetalleController::class,"store"]);
Route::put("presupuestos-detalles/update/{presupuesto_id}/{item_id}",[PresupuestosDetalleController::class,"update"]);
Route::delete("presupuestos-detalles/delete/{presupuesto_id}/{item_id}",[PresupuestosDetalleController::class,"destroy"]);

Route::get("perfiles/read",[PerfilController::class,"read"]);
Route::post("perfiles/create",[PerfilController::class,"store"]);

Route::get("tipo-impuesto/read",[TipoImpuestoController::class,"read"]);
Route::post("tipo-impuesto/create",[TipoImpuestoController::class,"store"]);
Route::put("tipo-impuesto/update/{id}",[TipoImpuestoController::class,"update"]);
Route::delete("tipo-impuesto/delete/{id}",[TipoImpuestoController::class,"destroy"]);
Route::get("tipo-impuesto/buscar",[TipoImpuestoController::class,"buscar"]);

Route::get("marca/read",[MarcaController::class,"read"]);
Route::post("marca/create",[MarcaController::class,"store"]);
Route::put("marca/update/{id}",[MarcaController::class,"update"]);
Route::delete("marca/delete/{id}",[MarcaController::class,"destroy"]);
Route::post("marca/buscar",[MarcaController::class,"buscar"]);

Route::get("item-marca/read",[ItemMarcaController::class,"read"]);
Route::post("item-marca/create",[ItemMarcaController::class,"store"]);
Route::put("item-marca/update/{marca_id}/{item_id}",[ItemMarcaController::class,"update"]);
Route::delete('item-marca/delete/{marca_id}/{item_id}', [ItemMarcaController::class, 'destroy']);

Route::get("modelo/read",[ModeloController::class,"read"]);
Route::post("modelo/create",[ModeloController::class,"store"]);
Route::put("modelo/update/{id}",[ModeloController::class,"update"]);
Route::delete("modelo/delete/{id}",[ModeloController::class,"destroy"]);

Route::get("item-modelo/read",[ItemModeloController::class,"read"]);
Route::post("item-modelo/create",[ItemModeloController::class,"store"]);
Route::put("item-modelo/update/{modelo_id}/{item_id}",[ItemModeloController::class,"update"]);
Route::delete("item-modelo/delete/{modelo_id}/{item_id}",[ItemModeloController::class,"destroy"]);

Route::get("funcionario/read",[FuncionarioController::class,"read"]);
Route::post("funcionario/create",[FuncionarioController::class,"store"]);
Route::put("funcionario/update/{id}",[FuncionarioController::class,"update"]);
Route::delete("funcionario/delete/{id}",[FuncionarioController::class,"destroy"]);

Route::get("empresa/read",[EmpresaController::class,"read"]);
Route::post("empresa/create",[EmpresaController::class,"store"]);
Route::put("empresa/update/{id}",[EmpresaController::class,"update"]);
Route::delete("empresa/delete/{id}",[EmpresaController::class,"destroy"]);

Route::get("sucursal/read",[SucursalController::class,"read"]);
Route::post("sucursal/create",[SucursalController::class,"store"]);
Route::put("sucursal/update/{empresa_id}",[SucursalController::class,"update"]);
Route::delete("sucursal/delete/{empresa_id}",[SucursalController::class,"destroy"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('registrarse',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('logout',[AuthController::class, 'logout']);
});