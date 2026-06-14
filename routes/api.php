<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InformeComprasController;
use App\Http\Controllers\InformeGerencialComprasController;
use App\Http\Controllers\InformeGerencialServicioController;
use App\Http\Controllers\InformeGerencialVentasController;
use App\Http\Controllers\InformeGerencialReferencialController;
use App\Http\Controllers\InformeServicioController;
use App\Http\Controllers\InformeVentasController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\Seguridad\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\AccesosController;
use App\Http\Controllers\ModulosController;
use App\Http\Controllers\LoginIntentoController;
use App\Http\Controllers\AuditoriaTransaccionesController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\TipoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ItemMarcaController;
use App\Http\Controllers\ItemModeloController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\CiudadController;
use App\Http\Controllers\NacionalidadController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\TipoImpuestoController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\TipoDiagnosticoController;
use App\Http\Controllers\TipoPromocionesController;
use App\Http\Controllers\TipoDescuentosController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\TipoVehiculoDetController;
use App\Http\Controllers\EquipoTrabajoController;
use App\Http\Controllers\TipoContratoController;
use App\Http\Controllers\EntidadEmisoraController;
use App\Http\Controllers\MarcaTarjetaController;
use App\Http\Controllers\EntidadAdheridaController;
use App\Http\Controllers\FormaCobroController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\MotivoAjusteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidosDetalleController;
use App\Http\Controllers\OrdenCompraCabController;
use App\Http\Controllers\OrdenCompraDetController;
use App\Http\Controllers\CompraCabController;
use App\Http\Controllers\CompraDetController;
use App\Http\Controllers\NotaRemiCompController;
use App\Http\Controllers\NotaRemiComDetController;
use App\Http\Controllers\NotasComCabController;
use App\Http\Controllers\NotasComDetController;
use App\Http\Controllers\AjusteCabController;
use App\Http\Controllers\AjusteDetController;
use App\Http\Controllers\PresupuestoController;
use App\Http\Controllers\PresupuestosDetalleController;
use App\Http\Controllers\LibroComprasController;
use App\Http\Controllers\PedidoVentasController;
use App\Http\Controllers\PedidoVentasDetController;
use App\Http\Controllers\VentasCabController;
use App\Http\Controllers\VentasDetController;
use App\Http\Controllers\NotaRemiVentController;
use App\Http\Controllers\NotaRemiVentDetController;
use App\Http\Controllers\NotasVentCabController;
use App\Http\Controllers\NotasVentDetController;
use App\Http\Controllers\SolicitudCabController;
use App\Http\Controllers\SolicitudDetController;
use App\Http\Controllers\RecepcionCabController;
use App\Http\Controllers\RecepcionDetController;
use App\Http\Controllers\DiagnosticoCabController;
use App\Http\Controllers\DiagnosticoDetController;
use App\Http\Controllers\PresupuestoServCabController;
use App\Http\Controllers\PresupuestoServDetController;
use App\Http\Controllers\OrdenServCabController;
use App\Http\Controllers\InsumosCabController;
use App\Http\Controllers\InsumosDetController;
use App\Http\Controllers\OrdenServDetController;
use App\Http\Controllers\ContratoServCabController;
use App\Http\Controllers\ContratoServDetController;
use App\Http\Controllers\OrdenServVentaController;
use App\Http\Controllers\VentasPedidoController;
use App\Http\Controllers\ReclamoCliCabController;
use App\Http\Controllers\ReclamoCliDetController;
use App\Http\Controllers\PromocionesCabController;
use App\Http\Controllers\PromocionesDetController;
use App\Http\Controllers\DescuentosCabController;
use App\Http\Controllers\DescuentosDetController;
use App\Http\Controllers\CobrosCabController;
use App\Http\Controllers\CobrosDetController;
use App\Http\Controllers\CtasCobrarController;
use App\Http\Controllers\CobrosTarjetaController;
use App\Http\Controllers\CobrosChequeController;
use App\Http\Controllers\AperturaCierreCajaController;
use App\Http\Controllers\ArqueoCajaController;
use App\Http\Controllers\RecaudacionDepositarController;
use App\Http\Controllers\TipoComprobanteController;
use App\Http\Controllers\TimbradoController;

Route::post('registrarse',     [AuthController::class, 'register']);
Route::post('login',           [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('forgot-password', [ForgotPasswordController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('reset-password',  [ForgotPasswordController::class, 'resetPassword']);
Route::post('/2fa/email/validar', [TwoFactorController::class, 'validarCodigoEmail'])->middleware('throttle:5,1');

// Portal de seguimiento de reclamos (acceso público para clientes)
Route::get('reclamoclicab/seguimiento/{token}', [ReclamoCliCabController::class, 'seguimiento']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('logout',            [AuthController::class, 'logout']);
    Route::get('/user',             fn(Request $request) => $request->user());
    Route::get('/2fa/generar',      [TwoFactorController::class, 'generarQR']);
    Route::get('/2fa/email/enviar', [TwoFactorController::class, 'enviarCodigoEmail']);
});

Route::middleware(['auth:sanctum', 'permiso:seguridad'])->group(function () {
    Route::get('users/read',                [UserController::class, 'read']);
    Route::post('users/create',             [UserController::class, 'store']);
    Route::put('users/update/{id}',         [UserController::class, 'update']);
    Route::delete('users/delete/{id}',      [UserController::class, 'destroy']);
    Route::put('users/reset-password/{id}', [UserController::class, 'resetPassword']);

    Route::get('permisos/read',           [PermisoController::class, 'read']);
    Route::post('permisos/create',        [PermisoController::class, 'store']);
    Route::put('permisos/update/{id}',    [PermisoController::class, 'update']);
    Route::delete('permisos/delete/{id}', [PermisoController::class, 'destroy']);
    Route::get('permisos/buscar',         [PermisoController::class, 'buscar']);
    Route::get('permisos/arbol',          [PermisoController::class, 'arbol']);

    Route::get('accesos/read',               [AccesosController::class, 'read']);
    Route::post('accesos/create',            [AccesosController::class, 'store']);
    Route::post('accesos/create-masivo',     [AccesosController::class, 'storeMasivo']);
    Route::put('accesos/update/{id}',        [AccesosController::class, 'update']);
    Route::put('accesos/desactivar/{id}',    [AccesosController::class, 'desactivar']);
    Route::put('accesos/activar/{id}',       [AccesosController::class, 'activar']);

    Route::get('modulos/read',           [ModulosController::class, 'read']);
    Route::post('modulos/create',        [ModulosController::class, 'store']);
    Route::put('modulos/update/{id}',    [ModulosController::class, 'update']);
    Route::delete('modulos/delete/{id}', [ModulosController::class, 'destroy']);

    Route::get('perfiles/read',     [PerfilController::class, 'read']);
    Route::post('perfiles/create',  [PerfilController::class, 'store']);
    Route::get('/perfiles/buscar',  [PerfilController::class, 'buscar']);

    Route::get('login-intentos/read',    [LoginIntentoController::class, 'read']);
    Route::delete('login-intentos/limpiar', [LoginIntentoController::class, 'limpiar']);

    Route::get('auditoria/read',   [AuditoriaTransaccionesController::class, 'read']);
    Route::get('auditoria/tablas', [AuditoriaTransaccionesController::class, 'tablas']);
});

Route::middleware(['auth:sanctum', 'permiso:referenciales'])->group(function () {
    Route::get('tipo-comprobante/read',           [TipoComprobanteController::class, 'read']);
    Route::post('tipo-comprobante/create',        [TipoComprobanteController::class, 'store']);
    Route::put('tipo-comprobante/update/{id}',    [TipoComprobanteController::class, 'update']);
    Route::delete('tipo-comprobante/delete/{id}', [TipoComprobanteController::class, 'destroy']);

    Route::get('timbrado/read',             [TimbradoController::class, 'read']);
    Route::get('timbrado/siguiente',        [TimbradoController::class, 'siguiente']);
    Route::get('timbrado/para-ventas',      [TimbradoController::class, 'paraVentas']);
    Route::post('timbrado/create',          [TimbradoController::class, 'store']);
    Route::put('timbrado/update/{id}',      [TimbradoController::class, 'update']);
    Route::delete('timbrado/delete/{id}',   [TimbradoController::class, 'destroy']);

    Route::get('tipo/read',           [TipoController::class, 'read']);
    Route::post('tipo/create',        [TipoController::class, 'store']);
    Route::put('tipo/update/{id}',    [TipoController::class, 'update']);
    Route::patch('tipo/estado/{id}',  [TipoController::class, 'cambiarEstado']);

    Route::get('items/read',                        [ItemController::class, 'read']);
    Route::post('items/create',                     [ItemController::class, 'store']);
    Route::put('items/update/{id}',                 [ItemController::class, 'update']);
    Route::patch('items/estado/{id}',               [ItemController::class, 'cambiarEstado']);
    Route::post('items/buscar',                     [ItemController::class, 'buscar']);
    Route::post('items/buscarItem',                 [ItemController::class, 'buscarItem']);
    Route::get('items/{id}/marcas',                 [ItemController::class, 'getMarcas']);
    Route::get('items/{id}/modelos',                [ItemController::class, 'getModelos']);
    Route::post('marca/buscarPorTipoItem',          [MarcaController::class, 'buscarPorTipoItem']);
    Route::get('modelo/buscarModelosItem',          [ModeloController::class, 'buscarModelosItem']);

    Route::get('item-marca/read',                         [ItemMarcaController::class, 'read']);
    Route::post('item-marca/create',                      [ItemMarcaController::class, 'store']);
    Route::put('item-marca/update/{marca_id}/{item_id}',  [ItemMarcaController::class, 'update']);
    Route::delete('item-marca/delete/{marca_id}/{item_id}', [ItemMarcaController::class, 'destroy']);

    Route::get('item-modelo/read',                          [ItemModeloController::class, 'read']);
    Route::post('item-modelo/create',                       [ItemModeloController::class, 'store']);
    Route::put('item-modelo/update/{modelo_id}/{item_id}',  [ItemModeloController::class, 'update']);
    Route::delete('item-modelo/delete/{modelo_id}/{item_id}', [ItemModeloController::class, 'destroy']);

    Route::get('marca/read',               [MarcaController::class, 'read']);
    Route::post('marca/create',            [MarcaController::class, 'store']);
    Route::put('marca/update/{id}',        [MarcaController::class, 'update']);
    Route::patch('marca/estado/{id}',      [MarcaController::class, 'cambiarEstado']);
    Route::post('marca/buscar',            [MarcaController::class, 'buscar']);
    Route::post('marca/buscarPorTipo',     [MarcaController::class, 'buscarPorTipo']);
    Route::post('marca/buscarPorMarca',    [MarcaController::class, 'buscarPorMarca']);
    Route::post('marca/buscarVehiculo',    [MarcaController::class, 'buscarVehiculo']);

    Route::get('modelo/read',                [ModeloController::class, 'read']);
    Route::post('modelo/create',             [ModeloController::class, 'store']);
    Route::put('modelo/update/{id}',         [ModeloController::class, 'update']);
    Route::patch('modelo/estado/{id}',       [ModeloController::class, 'cambiarEstado']);
    Route::post('modelo/buscarPorMarca',     [ModeloController::class, 'buscarPorMarca']);

    Route::get('paises/read',           [PaisController::class, 'read']);
    Route::post('paises/create',        [PaisController::class, 'store']);
    Route::put('paises/update/{id}',    [PaisController::class, 'update']);
    Route::delete('paises/delete/{id}', [PaisController::class, 'destroy']);

    Route::get('ciudades/read',             [CiudadController::class, 'read']);
    Route::get('ciudades/por-pais/{id}',    [CiudadController::class, 'readPorPais']);
    Route::post('ciudades/create',          [CiudadController::class, 'store']);
    Route::put('ciudades/update/{id}',      [CiudadController::class, 'update']);
    Route::delete('ciudades/delete/{id}',   [CiudadController::class, 'destroy']);
    Route::post('ciudades/buscar',          [CiudadController::class, 'buscar']);

    Route::get('nacionalidad/read',             [NacionalidadController::class, 'read']);
    Route::get('nacionalidad/por-pais/{id}',    [NacionalidadController::class, 'readPorPais']);
    Route::post('nacionalidad/create',          [NacionalidadController::class, 'store']);
    Route::put('nacionalidad/update/{id}',      [NacionalidadController::class, 'update']);
    Route::delete('nacionalidad/delete/{id}',   [NacionalidadController::class, 'destroy']);

    Route::get('proveedores/read',              [ProveedorController::class, 'read']);
    Route::post('proveedores/create',           [ProveedorController::class, 'store']);
    Route::put('proveedores/update/{id}',       [ProveedorController::class, 'update']);
    Route::patch('proveedores/estado/{id}',     [ProveedorController::class, 'cambiarEstado']);
    Route::post('proveedores/buscar',           [ProveedorController::class, 'buscar']);

    Route::get('clientes/read',           [ClienteController::class, 'read']);
    Route::post('clientes/create',        [ClienteController::class, 'store']);
    Route::put('clientes/update/{id}',    [ClienteController::class, 'update']);
    Route::patch('clientes/estado/{id}',  [ClienteController::class, 'cambiarEstado']);
    Route::post('clientes/buscar',        [ClienteController::class, 'buscar']);

    Route::get('empresa/read',           [EmpresaController::class, 'read']);
    Route::post('empresa/create',        [EmpresaController::class, 'store']);
    Route::put('empresa/update/{id}',    [EmpresaController::class, 'update']);
    Route::patch('empresa/estado/{id}',  [EmpresaController::class, 'cambiarEstado']);

    Route::get('sucursal/read',                [SucursalController::class, 'read']);
    Route::post('sucursal/create',             [SucursalController::class, 'store']);
    Route::put('sucursal/update/{id}', [SucursalController::class, 'update']);
    Route::patch('sucursal/estado/{id}',  [SucursalController::class, 'cambiarEstado']);

    Route::get('deposito/read',                    [DepositoController::class, 'read']);
    Route::get('deposito/read-by-sucursal/{id}',   [DepositoController::class, 'readBySucursal']);
    Route::post('deposito/create',                 [DepositoController::class, 'store']);
    Route::put('deposito/update/{id}',             [DepositoController::class, 'update']);
    Route::patch('deposito/estado/{id}',            [DepositoController::class, 'cambiarEstado']);

    Route::get('funcionario/read',           [FuncionarioController::class, 'read']);
    Route::get('funcionario/buscar',         [FuncionarioController::class, 'buscar']);
    Route::post('funcionario/create',        [FuncionarioController::class, 'store']);
    Route::put('funcionario/update/{id}',    [FuncionarioController::class, 'update']);
    Route::patch('funcionario/estado/{id}',  [FuncionarioController::class, 'cambiarEstado']);

    Route::get('tipo-impuesto/read',           [TipoImpuestoController::class, 'read']);
    Route::post('tipo-impuesto/create',        [TipoImpuestoController::class, 'store']);
    Route::put('tipo-impuesto/update/{id}',    [TipoImpuestoController::class, 'update']);
    Route::patch('tipo-impuesto/estado/{id}',  [TipoImpuestoController::class, 'cambiarEstado']);
    Route::get('tipo-impuesto/buscar',         [TipoImpuestoController::class, 'buscar']);

    Route::get('tipo-servicio/read',           [TipoServicioController::class, 'read']);
    Route::post('tipo-servicio/create',        [TipoServicioController::class, 'store']);
    Route::put('tipo-servicio/update/{id}',    [TipoServicioController::class, 'update']);
    Route::patch('tipo-servicio/estado/{id}',  [TipoServicioController::class, 'cambiarEstado']);
    Route::get('tipo-servicio/buscar',         [TipoServicioController::class, 'buscar']);

    Route::get('tipo-diagnostico/read',           [TipoDiagnosticoController::class, 'read']);
    Route::post('tipo-diagnostico/create',        [TipoDiagnosticoController::class, 'store']);
    Route::put('tipo-diagnostico/update/{id}',    [TipoDiagnosticoController::class, 'update']);
    Route::patch('tipo-diagnostico/estado/{id}',  [TipoDiagnosticoController::class, 'cambiarEstado']);
    Route::get('tipo-diagnostico/buscar',         [TipoDiagnosticoController::class, 'buscar']);

    Route::get('tipo-promociones/read',           [TipoPromocionesController::class, 'read']);
    Route::post('tipo-promociones/create',        [TipoPromocionesController::class, 'store']);
    Route::put('tipo-promociones/update/{id}',    [TipoPromocionesController::class, 'update']);
    Route::patch('tipo-promociones/estado/{id}',  [TipoPromocionesController::class, 'cambiarEstado']);
    Route::get('tipo-promociones/buscar',         [TipoPromocionesController::class, 'buscar']);

    Route::get('tipo-descuentos/read',           [TipoDescuentosController::class, 'read']);
    Route::post('tipo-descuentos/create',        [TipoDescuentosController::class, 'store']);
    Route::put('tipo-descuentos/update/{id}',    [TipoDescuentosController::class, 'update']);
    Route::patch('tipo-descuentos/estado/{id}',  [TipoDescuentosController::class, 'cambiarEstado']);
    Route::get('tipo-descuentos/buscar',         [TipoDescuentosController::class, 'buscar']);

    Route::get('tipo-vehiculo/read',               [TipoVehiculoController::class, 'read']);
    Route::post('tipo-vehiculo/create',            [TipoVehiculoController::class, 'store']);
    Route::put('tipo-vehiculo/update/{id}',        [TipoVehiculoController::class, 'update']);
    Route::patch('tipo-vehiculo/estado/{id}',       [TipoVehiculoController::class, 'cambiarEstado']);
    Route::get('tipo-vehiculo/buscar',             [TipoVehiculoController::class, 'buscar']);
    Route::get('tipo-vehiculo/buscarPorMarca',     [TipoVehiculoController::class, 'buscarPorMarca']);
    Route::get('tipo-vehiculo-det/read/{tipo_vehiculo_id}', [TipoVehiculoDetController::class, 'read']);
    Route::post('tipo-vehiculo-det/create',                 [TipoVehiculoDetController::class, 'store']);
    Route::put('tipo-vehiculo-det/update/{id}',             [TipoVehiculoDetController::class, 'update']);
    Route::delete('tipo-vehiculo-det/delete/{id}',          [TipoVehiculoDetController::class, 'destroy']);

    Route::get('equipo_trabajo/read',           [EquipoTrabajoController::class, 'read']);
    Route::post('equipo_trabajo/create',        [EquipoTrabajoController::class, 'store']);
    Route::put('equipo_trabajo/update/{id}',    [EquipoTrabajoController::class, 'update']);
    Route::patch('equipo_trabajo/estado/{id}',  [EquipoTrabajoController::class, 'cambiarEstado']);
    Route::get('equipo_trabajo/buscar',         [EquipoTrabajoController::class, 'buscar']);

    Route::get('tipo_contrato/read',           [TipoContratoController::class, 'read']);
    Route::post('tipo_contrato/create',        [TipoContratoController::class, 'store']);
    Route::put('tipo_contrato/update/{id}',    [TipoContratoController::class, 'update']);
    Route::patch('tipo_contrato/estado/{id}',  [TipoContratoController::class, 'cambiarEstado']);

    Route::get('entidad_emisora/read',           [EntidadEmisoraController::class, 'read']);
    Route::post('entidad_emisora/create',        [EntidadEmisoraController::class, 'store']);
    Route::put('entidad_emisora/update/{id}',    [EntidadEmisoraController::class, 'update']);
    Route::patch('entidad_emisora/estado/{id}',  [EntidadEmisoraController::class, 'cambiarEstado']);
    Route::get('entidad_emisora/buscar',         [EntidadEmisoraController::class, 'buscarEntidadEmisora']);

    Route::get('marca_tarjeta/read',           [MarcaTarjetaController::class, 'read']);
    Route::post('marca_tarjeta/create',        [MarcaTarjetaController::class, 'store']);
    Route::put('marca_tarjeta/update/{id}',    [MarcaTarjetaController::class, 'update']);
    Route::patch('marca_tarjeta/estado/{id}',  [MarcaTarjetaController::class, 'cambiarEstado']);

    Route::get('entidad_adherida/read',           [EntidadAdheridaController::class, 'read']);
    Route::post('entidad_adherida/create',        [EntidadAdheridaController::class, 'store']);
    Route::put('entidad_adherida/update/{id}',    [EntidadAdheridaController::class, 'update']);
    Route::patch('entidad_adherida/estado/{id}',  [EntidadAdheridaController::class, 'cambiarEstado']);

    Route::get('forma_cobro/read',           [FormaCobroController::class, 'read']);
    Route::post('forma_cobro/create',        [FormaCobroController::class, 'store']);
    Route::put('forma_cobro/update/{id}',    [FormaCobroController::class, 'update']);
    Route::patch('forma_cobro/estado/{id}',  [FormaCobroController::class, 'cambiarEstado']);

    Route::get('caja/read',           [CajaController::class, 'read']);
    Route::post('caja/create',        [CajaController::class, 'store']);
    Route::put('caja/update/{id}',    [CajaController::class, 'update']);
    Route::patch('caja/estado/{id}',  [CajaController::class, 'cambiarEstado']);

    Route::get('motivo_ajuste/read',           [MotivoAjusteController::class, 'read']);
    Route::post('motivo_ajuste/create',        [MotivoAjusteController::class, 'store']);
    Route::put('motivo_ajuste/update/{id}',    [MotivoAjusteController::class, 'update']);
    Route::patch('motivo_ajuste/estado/{id}',  [MotivoAjusteController::class, 'cambiarEstado']);
});

Route::middleware(['auth:sanctum', 'permiso:compras'])->group(function () {
    Route::post('pedidos/create',              [PedidoController::class, 'store']);
    Route::get('pedidos/read',                 [PedidoController::class, 'read']);
    Route::put('pedidos/update/{id}',          [PedidoController::class, 'update']);
    Route::put('pedidos/anular/{id}',          [PedidoController::class, 'anular']);
    Route::delete('pedidos/delete/{id}',       [PedidoController::class, 'eliminar']);
    Route::put('pedidos/confirmar/{id}',       [PedidoController::class, 'confirmar']);
    Route::post('pedidos/buscar',              [PedidoController::class, 'buscar']);
    Route::get('pedidos/buscar-informe',       [PedidoController::class, 'buscarInforme']);
    Route::get('pedidos/imprimir/{id}',        [PedidoController::class, 'imprimir']);
    Route::get('pedidos/enviar-ticket/{id}',   [PedidoController::class, 'enviarTicket']);

    Route::post('pedidos-detalles/create',                     [PedidosDetalleController::class, 'store']);
    Route::get('pedidos-detalles/read/{id}',                   [PedidosDetalleController::class, 'read']);
    Route::put('pedidos-detalles/update/{pedido_id}',          [PedidosDetalleController::class, 'update']);
    Route::delete('pedidos-detalles/delete/{pedido_id}/{item_id}', [PedidosDetalleController::class, 'destroy']);

    Route::post('ordencompracab/create',            [OrdenCompraCabController::class, 'store']);
    Route::get('ordencompracab/read',               [OrdenCompraCabController::class, 'read']);
    Route::put('ordencompracab/update/{id}',        [OrdenCompraCabController::class, 'update']);
    Route::put('ordencompracab/anular/{id}',        [OrdenCompraCabController::class, 'anular']);
    Route::delete('ordencompracab/delete/{id}',     [OrdenCompraCabController::class, 'eliminar']);
    Route::put('ordencompracab/confirmar/{id}',     [OrdenCompraCabController::class, 'confirmar']);
    Route::post('ordencompracab/buscar',            [OrdenCompraCabController::class, 'buscar']);
    Route::get('ordenes_compras/buscar-informe',    [OrdenCompraCabController::class, 'buscarInforme']);
    Route::get('ordencompracab/imprimir/{id}',      [OrdenCompraCabController::class, 'imprimir']);
    Route::get('ordencompracab/enviar-ticket/{id}', [OrdenCompraCabController::class, 'enviarTicket']);

    Route::post('ordencompradet/create',                                   [OrdenCompraDetController::class, 'store']);
    Route::get('ordencompradet/read/{id}',                                 [OrdenCompraDetController::class, 'read']);
    Route::get('ordencompradet/depositos/{id}',                            [OrdenCompraDetController::class, 'depositosDeLaOrden']);
    Route::put('ordencompradet/update/{orden_compra_cab_id}/{item_id}',    [OrdenCompraDetController::class, 'update']);
    Route::delete('ordencompradet/delete/{orden_compra_cab_id}/{item_id}', [OrdenCompraDetController::class, 'destroy']);

    Route::post('compras/create',           [CompraCabController::class, 'store']);
    Route::get('compras/read',              [CompraCabController::class, 'read']);
    Route::put('compras/update/{id}',       [CompraCabController::class, 'update']);
    Route::put('compras/anular/{id}',       [CompraCabController::class, 'anular']);
    Route::put('compras/confirmar/{id}',    [CompraCabController::class, 'confirmar']);
    Route::post('compras/buscar',           [CompraCabController::class, 'buscar']);
    Route::get('compras/buscar-informe',    [CompraCabController::class, 'buscarInforme']);

    Route::post('compradet/create',                                   [CompraDetController::class, 'store']);
    Route::get('compradet/read/{id}',                                 [CompraDetController::class, 'read']);
    Route::put('compradet/update/{compra_cab_id}/{item_id}',          [CompraDetController::class, 'update']);
    Route::delete('compradet/delete/{compra_cab_id}/{item_id}',       [CompraDetController::class, 'destroy']);

    Route::get('notaremicomp/read',             [NotaRemiCompController::class, 'read']);
    Route::post('notaremicomp/create',          [NotaRemiCompController::class, 'store']);
    Route::put('notaremicomp/update/{id}',      [NotaRemiCompController::class, 'update']);
    Route::put('notaremicomp/anular/{id}',      [NotaRemiCompController::class, 'anular']);
    Route::put('notaremicomp/confirmar/{id}',   [NotaRemiCompController::class, 'confirmar']);
    Route::get('notaremicomp/buscar-informe',   [NotaRemiCompController::class, 'buscarInforme']);

    Route::post('notaremicomdet/create',                              [NotaRemiComDetController::class, 'store']);
    Route::get('notaremicomdet/read/{id}',                            [NotaRemiComDetController::class, 'read']);
    Route::put('notaremicomdet/update/{nota_remi_comp_id}',           [NotaRemiComDetController::class, 'update']);
    Route::delete('notaremicomdet/delete/{nota_remi_comp_id}/{item_id}', [NotaRemiComDetController::class, 'destroy']);

    Route::post('notacompcab/create',           [NotasComCabController::class, 'store']);
    Route::get('notacompcab/read',              [NotasComCabController::class, 'read']);
    Route::put('notacompcab/update/{id}',       [NotasComCabController::class, 'update']);
    Route::put('notacompcab/anular/{id}',       [NotasComCabController::class, 'anular']);
    Route::delete('notacompcab/delete/{id}',    [NotasComCabController::class, 'eliminar']);
    Route::put('notacompcab/confirmar/{id}',    [NotasComCabController::class, 'confirmar']);
    Route::get('notacompcab/buscar-informe',    [NotasComCabController::class, 'buscarInforme']);

    Route::post('notacompdet/create',                                    [NotasComDetController::class, 'store']);
    Route::get('notacompdet/read/{id}',                                  [NotasComDetController::class, 'read']);
    Route::put('notacompdet/update/{notas_comp_cab_id}/{item_id}',       [NotasComDetController::class, 'update']);
    Route::delete('notacompdet/delete/{notas_comp_cab_id}/{item_id}',    [NotasComDetController::class, 'destroy']);

    Route::post('ajus_cab/create',          [AjusteCabController::class, 'store']);
    Route::get('ajus_cab/read',             [AjusteCabController::class, 'read']);
    Route::put('ajus_cab/update/{id}',      [AjusteCabController::class, 'update']);
    Route::put('ajus_cab/anular/{id}',      [AjusteCabController::class, 'anular']);
    Route::put('ajus_cab/confirmar/{id}',   [AjusteCabController::class, 'confirmar']);
    Route::get('ajus_cab/buscar-informe',   [AjusteCabController::class, 'buscarInforme']);

    Route::post('ajus_det/create',                         [AjusteDetController::class, 'store']);
    Route::get('ajus_det/read/{id}',                       [AjusteDetController::class, 'read']);
    Route::put('ajus_det/update/{pedido_id}',              [AjusteDetController::class, 'update']);
    Route::delete('ajus_det/delete/{pedido_id}/{item_id}', [AjusteDetController::class, 'destroy']);

    Route::post('presupuesto/create',           [PresupuestoController::class, 'store']);
    Route::get('presupuesto/read',              [PresupuestoController::class, 'read']);
    Route::put('presupuesto/update/{id}',       [PresupuestoController::class, 'update']);
    Route::put('presupuesto/anular/{id}',       [PresupuestoController::class, 'anular']);
    Route::put('presupuesto/confirmar/{id}',    [PresupuestoController::class, 'confirmar']);
    Route::put('presupuesto/rechazar/{id}',     [PresupuestoController::class, 'rechazar']);
    Route::put('presupuesto/aprobar/{id}',      [PresupuestoController::class, 'aprobar']);
    Route::post('presupuesto/buscar',           [PresupuestoController::class, 'buscar']);
    Route::get('presupuestos/buscar-informe',   [PresupuestoController::class, 'buscarInforme']);

    Route::get('presupuesto-pedidos/read/{presupuesto_id}', [PresupuestoController::class, 'readPedidos']);
    Route::get('presupuestos-detalles/read/{id}',           [PresupuestosDetalleController::class, 'read']);
    Route::get('presupuestos-detalles/depositos/{id}',         [PresupuestosDetalleController::class, 'depositosDelPresupuesto']);
    Route::get('presupuestos/depositos-por-pedidos/{id}',      [PresupuestosDetalleController::class, 'depositosPorPedidos']);
    Route::post('presupuestos-detalles/create',                                  [PresupuestosDetalleController::class, 'store']);
    Route::put('presupuestos-detalles/update/{presupuesto_id}/{item_id}',        [PresupuestosDetalleController::class, 'update']);
    Route::delete('presupuestos-detalles/delete/{presupuesto_id}/{item_id}',     [PresupuestosDetalleController::class, 'destroy']);

    Route::get('libro_compras/buscar-informe', [LibroComprasController::class, 'buscarInforme']);
});

Route::middleware(['auth:sanctum', 'permiso:ventas'])->group(function () {
    Route::get('pedido_ventas/read',               [PedidoVentasController::class, 'read']);
    Route::post('pedido_ventas/create',            [PedidoVentasController::class, 'store']);
    Route::put('pedido_ventas/update/{id}',        [PedidoVentasController::class, 'update']);
    Route::put('pedido_ventas/anular/{id}',        [PedidoVentasController::class, 'anular']);
    Route::put('pedido_ventas/confirmar/{id}',     [PedidoVentasController::class, 'confirmar']);
    Route::delete('pedido_ventas/delete/{id}',     [PedidoVentasController::class, 'eliminar']);
    Route::post('pedido_ventas/buscar',            [PedidoVentasController::class, 'buscar']);
    Route::post('pedido_ventas/buscarInforme',     [PedidoVentasController::class, 'buscarInforme']);

    Route::post('pedido_ventas_det/create',                                    [PedidoVentasDetController::class, 'store']);
    Route::get('pedido_ventas_det/read/{pedidos_ventas_id}',                   [PedidoVentasDetController::class, 'read']);
    Route::put('pedido_ventas_det/update/{pedidos_ventas_id}',                 [PedidoVentasDetController::class, 'update']);
    Route::delete('pedido_ventas_det/delete/{pedidos_ventas_id}/{item_id}',    [PedidoVentasDetController::class, 'destroy']);

    Route::get('ventas_cab/read',                   [VentasCabController::class, 'read']);
    Route::post('ventas_cab/create',                [VentasCabController::class, 'store']);
    Route::put('ventas_cab/update/{id}',            [VentasCabController::class, 'update']);
    Route::put('ventas_cab/anular/{id}',            [VentasCabController::class, 'anular']);
    Route::put('ventas_cab/confirmar/{id}',         [VentasCabController::class, 'confirmar']);
    Route::get('ventas_cab/buscar',                 [VentasCabController::class, 'buscarVentas']);
    Route::get('ventas_cab/buscarVentasNota',       [VentasCabController::class, 'buscarVentasNota']);
    Route::get('ventas_cab/imprimir/{id}',          [VentasCabController::class, 'imprimir']);
    Route::get('ventas_cab/detalle/{id}',           [VentasCabController::class, 'detalle']);

    Route::get('ventas_det/read/{ventas_cab_id}',   [VentasDetController::class, 'read']);

    Route::get('notaremivent/read',             [NotaRemiVentController::class, 'read']);
    Route::post('notaremivent/create',          [NotaRemiVentController::class, 'store']);
    Route::put('notaremivent/update/{id}',      [NotaRemiVentController::class, 'update']);
    Route::put('notaremivent/anular/{id}',      [NotaRemiVentController::class, 'anular']);
    Route::put('notaremivent/confirmar/{id}',   [NotaRemiVentController::class, 'confirmar']);
    Route::get('notaremivent/imprimir/{id}',    [NotaRemiVentController::class, 'imprimir']);

    Route::get('notaremiventdet/read/{nota_remi_vent_id}', [NotaRemiVentDetController::class, 'read']);
    Route::get('tipo_vehiculo_det/buscar',                 [TipoVehiculoDetController::class, 'buscar']);

    Route::post('notaventcab/create',           [NotasVentCabController::class, 'store']);
    Route::get('notaventcab/read',              [NotasVentCabController::class, 'read']);
    Route::put('notaventcab/update/{id}',       [NotasVentCabController::class, 'update']);
    Route::put('notaventcab/anular/{id}',       [NotasVentCabController::class, 'anular']);
    Route::delete('notavemtcab/delete/{id}',    [NotasVentCabController::class, 'eliminar']);
    Route::put('notaventcab/confirmar/{id}',    [NotasVentCabController::class, 'confirmar']);

    Route::post('notaventdet/create',                                   [NotasVentDetController::class, 'store']);
    Route::get('notaventdet/read/{id}',                                 [NotasVentDetController::class, 'read']);
    Route::put('notaventdet/update/{notas_vent_cab_id}/{item_id}',      [NotasVentDetController::class, 'update']);
    Route::delete('notaventdet/delete/{notas_vent_cab_id}/{item_id}',   [NotasVentDetController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'permiso:servicios'])->group(function () {
    Route::post('solicitudcad/create',          [SolicitudCabController::class, 'store']);
    Route::get('solicitudcad/read',             [SolicitudCabController::class, 'read']);
    Route::put('solicitudcad/update/{id}',      [SolicitudCabController::class, 'update']);
    Route::put('solicitudcad/anular/{id}',      [SolicitudCabController::class, 'anular']);
    Route::put('solicitudcad/confirmar/{id}',   [SolicitudCabController::class, 'confirmar']);
    Route::post('solicitudcad/buscar',          [SolicitudCabController::class, 'buscar']);
    Route::get('solicitudcad/buscar-informe',   [SolicitudCabController::class, 'buscarInforme']);

    Route::post('solicitud_det/create',                              [SolicitudDetController::class, 'store']);
    Route::get('solicitud_det/read/{id}',                            [SolicitudDetController::class, 'read']);
    Route::put('solicitud_det/update/{solicitudes_cab_id}',          [SolicitudDetController::class, 'update']);
    Route::delete('solicitud_det/delete/{solicitudes_cab_id}/{item_id}', [SolicitudDetController::class, 'destroy']);

    Route::post('recepcab/create',          [RecepcionCabController::class, 'store']);
    Route::get('recepcab/read',             [RecepcionCabController::class, 'read']);
    Route::put('recepcab/update/{id}',      [RecepcionCabController::class, 'update']);
    Route::put('recepcab/anular/{id}',           [RecepcionCabController::class, 'anular']);
    Route::put('recepcab/confirmar/{id}',        [RecepcionCabController::class, 'confirmar']);
    Route::put('recepcab/registrar-salida/{id}', [RecepcionCabController::class, 'registrarSalida']);
    Route::get('recepcab/imprimir/{id}',         [RecepcionCabController::class, 'imprimir']);
    Route::get('recepcab/enviar-ticket/{id}',    [RecepcionCabController::class, 'enviarTicket']);
    Route::post('recepcab/buscar',          [RecepcionCabController::class, 'buscar']);
    Route::get('recepcab/buscar-informe',   [RecepcionCabController::class, 'buscarInforme']);

    Route::post('recepcion_det/create',                                  [RecepcionDetController::class, 'store']);
    Route::get('recepcion_det/read/{id}',                                [RecepcionDetController::class, 'read']);
    Route::put('recepcion_det/update/{solicitudes_cab_id}',              [RecepcionDetController::class, 'update']);
    Route::delete('recepcion_det/delete/{solicitudes_cab_id}/{item_id}', [RecepcionDetController::class, 'destroy']);

    Route::post('diagnosticocab/create',        [DiagnosticoCabController::class, 'store']);
    Route::get('diagnosticocab/read',           [DiagnosticoCabController::class, 'read']);
    Route::put('diagnosticocab/update/{id}',    [DiagnosticoCabController::class, 'update']);
    Route::put('diagnosticocab/anular/{id}',    [DiagnosticoCabController::class, 'anular']);
    Route::put('diagnosticocab/confirmar/{id}', [DiagnosticoCabController::class, 'confirmar']);
    Route::post('diagnosticocab/buscar',        [DiagnosticoCabController::class, 'buscar']);
    Route::get('diagnosticocab/buscar-informe', [DiagnosticoCabController::class, 'buscarInforme']);

    Route::post('diagnostico_det/create',                                    [DiagnosticoDetController::class, 'store']);
    Route::get('diagnostico_det/read/{id}',                                  [DiagnosticoDetController::class, 'read']);
    Route::put('diagnostico_det/update/{solicitudes_cab_id}',                [DiagnosticoDetController::class, 'update']);
    Route::delete('diagnostico_det/delete/{solicitudes_cab_id}/{item_id}',   [DiagnosticoDetController::class, 'destroy']);

    Route::post('presupuestoservcab/create',        [PresupuestoServCabController::class, 'store']);
    Route::get('presupuestoservcab/read',           [PresupuestoServCabController::class, 'read']);
    Route::put('presupuestoservcab/update/{id}',    [PresupuestoServCabController::class, 'update']);
    Route::put('presupuestoservcab/anular/{id}',    [PresupuestoServCabController::class, 'anular']);
    Route::put('presupuestoservcab/confirmar/{id}', [PresupuestoServCabController::class, 'confirmar']);
    Route::post('presupuestoservcab/buscar',        [PresupuestoServCabController::class, 'buscar']);
    Route::get('presupuestoservcab/buscar-informe', [PresupuestoServCabController::class, 'buscarInforme']);
    Route::get('presupuestoservcab/readById/{id}',  [PresupuestoServCabController::class, 'readById']);

    Route::post('presupuesto_serv_det/create',                                    [PresupuestoServDetController::class, 'store']);
    Route::get('presupuesto_serv_det/read/{id}',                                  [PresupuestoServDetController::class, 'read']);
    Route::put('presupuesto_serv_det/update/{presupuesto_serv_cab_id}',           [PresupuestoServDetController::class, 'update']);
    Route::delete('presupuesto_serv_det/delete/{presupuesto_serv_cab_id}/{item_id}', [PresupuestoServDetController::class, 'destroy']);

    Route::post('ordenserviciocab/create',          [OrdenServCabController::class, 'store']);
    Route::get('ordenserviciocab/read',             [OrdenServCabController::class, 'read']);
    Route::put('ordenserviciocab/update/{id}',      [OrdenServCabController::class, 'update']);
    Route::put('ordenserviciocab/anular/{id}',      [OrdenServCabController::class, 'anular']);
    Route::put('ordenserviciocab/confirmar/{id}',   [OrdenServCabController::class, 'confirmar']);
    Route::post('ordenserviciocab/buscar',                [OrdenServCabController::class, 'buscar']);
    Route::post('ordenserviciocab/buscar-para-contrato', [OrdenServCabController::class, 'buscarParaContrato']);
    Route::get('ordenserviciocab/buscar-informe',        [OrdenServCabController::class, 'buscarInforme']);

    Route::post('ordenservicodet/create',                                [OrdenServDetController::class, 'store']);
    Route::get('ordenserviciodet/read/{id}',                             [OrdenServDetController::class, 'read']);
    Route::put('ordenserviciodet/update/{orden_serv_cab_id}',            [OrdenServDetController::class, 'update']);
    Route::delete('ordenserviciodet/delete/{orden_serv_cab_id}/{item_id}', [OrdenServDetController::class, 'destroy']);

    // Insumos Utilizados — Cabecera
    Route::get('insumos-cab/read',              [InsumosCabController::class, 'read']);
    Route::get('insumos-cab/read/{id}',         [InsumosCabController::class, 'readById']);
    Route::get('insumos-cab/buscar-os',         [InsumosCabController::class, 'buscarOS']);
    Route::post('insumos-cab/create',           [InsumosCabController::class, 'store']);
    Route::put('insumos-cab/update/{id}',       [InsumosCabController::class, 'update']);
    Route::put('insumos-cab/confirmar/{id}',    [InsumosCabController::class, 'confirmar']);
    Route::put('insumos-cab/anular/{id}',       [InsumosCabController::class, 'anular']);

    // Insumos Utilizados — Detalle
    Route::get('insumos-det/read/{cab_id}',     [InsumosDetController::class, 'readByCab']);
    Route::post('insumos-det/create',           [InsumosDetController::class, 'store']);
    Route::put('insumos-det/update/{id}',       [InsumosDetController::class, 'update']);
    Route::delete('insumos-det/delete/{id}',    [InsumosDetController::class, 'destroy']);

    Route::post('contratoservcab/create',           [ContratoServCabController::class, 'store']);
    Route::get('contratoservcab/read',              [ContratoServCabController::class, 'read']);
    Route::put('contratoservcab/update/{id}',       [ContratoServCabController::class, 'update']);
    Route::put('contratoservcab/anular/{id}',       [ContratoServCabController::class, 'anular']);
    Route::put('contratoservcab/confirmar/{id}',    [ContratoServCabController::class, 'confirmar']);
    Route::put('contratoservcab/renovar/{id}',      [ContratoServCabController::class, 'renovar']);
    Route::post('contratoservcab/buscar',           [ContratoServCabController::class, 'buscar']);
    Route::get('contratoservcab/buscar-informe',    [ContratoServCabController::class, 'buscarInforme']);
    Route::get('contratoservcab/imprimir/{id}',     [ContratoServCabController::class, 'imprimir']);

    Route::post('contratoservdet/create',                                       [ContratoServDetController::class, 'store']);
    Route::get('contratoservdet/read/{id}',                                     [ContratoServDetController::class, 'read']);
    Route::put('contratoservdet/update/{contrato_serv_cab_id}',                 [ContratoServDetController::class, 'update']);
    Route::delete('contratoservdet/delete/{contrato_serv_cab_id}/{item_id}',    [ContratoServDetController::class, 'destroy']);

    Route::post('ordenservventa/create',            [OrdenServVentaController::class, 'store']);
    Route::get('ordenservventa/by-venta/{id}',      [OrdenServVentaController::class, 'readByVenta']);
    Route::delete('ordenservventa/delete/{id}',     [OrdenServVentaController::class, 'destroy']);
    Route::get('ordenservventa/buscar-ordenes',     [OrdenServVentaController::class, 'buscarOrdenes']);

    Route::post('ventas-pedidos/create',            [VentasPedidoController::class, 'store']);
    Route::get('ventas-pedidos/by-venta/{id}',      [VentasPedidoController::class, 'readByVenta']);
    Route::delete('ventas-pedidos/delete/{id}',     [VentasPedidoController::class, 'destroy']);

    Route::post('reclamoclicab/create',         [ReclamoCliCabController::class, 'store']);
    Route::get('reclamoclicab/read',            [ReclamoCliCabController::class, 'read']);
    Route::put('reclamoclicab/update/{id}',     [ReclamoCliCabController::class, 'update']);
    Route::put('reclamoclicab/anular/{id}',     [ReclamoCliCabController::class, 'anular']);
    Route::put('reclamoclicab/procesar/{id}',   [ReclamoCliCabController::class, 'procesar']);
    Route::put('reclamoclicab/resolver/{id}',   [ReclamoCliCabController::class, 'resolver']);
    Route::get('reclamoclicab/buscar-informe',  [ReclamoCliCabController::class, 'buscarInforme']);

    Route::post('reclamoclidet/create',                                     [ReclamoCliDetController::class, 'store']);
    Route::get('reclamoclidet/read/{id}',                                   [ReclamoCliDetController::class, 'read']);
    Route::put('reclamoclidet/update/{reclamo_cli_cab_id}',                 [ReclamoCliDetController::class, 'update']);
    Route::delete('reclamoclidet/delete/{reclamo_cli_cab_id}/{item_id}',    [ReclamoCliDetController::class, 'destroy']);

    Route::post('promocionescab/create',        [PromocionesCabController::class, 'store']);
    Route::get('promocionescab/read',           [PromocionesCabController::class, 'read']);
    Route::put('promocionescab/update/{id}',    [PromocionesCabController::class, 'update']);
    Route::put('promocionescab/anular/{id}',    [PromocionesCabController::class, 'anular']);
    Route::put('promocionescab/confirmar/{id}', [PromocionesCabController::class, 'confirmar']);
    Route::post('promocionescab/buscar',        [PromocionesCabController::class, 'buscar']);
    Route::get('promocionescab/buscar-informe', [PromocionesCabController::class, 'buscarInforme']);

    Route::post('promociones_det/create',                                   [PromocionesDetController::class, 'store']);
    Route::get('promociones_det/read/{id}',                                 [PromocionesDetController::class, 'read']);
    Route::put('promociones_det/update/{promociones_cab_id}',               [PromocionesDetController::class, 'update']);
    Route::delete('promociones_det/delete/{promociones_cab_id}/{item_id}',  [PromocionesDetController::class, 'destroy']);

    Route::post('descuentoscab/create',         [DescuentosCabController::class, 'store']);
    Route::get('descuentoscab/read',            [DescuentosCabController::class, 'read']);
    Route::put('descuentoscab/update/{id}',     [DescuentosCabController::class, 'update']);
    Route::put('descuentoscab/anular/{id}',     [DescuentosCabController::class, 'anular']);
    Route::put('descuentoscab/confirmar/{id}',  [DescuentosCabController::class, 'confirmar']);
    Route::post('descuentoscab/buscar',         [DescuentosCabController::class, 'buscar']);
    Route::get('descuentoscab/buscar-informe',  [DescuentosCabController::class, 'buscarInforme']);

    Route::post('descuentos_det/create',                                    [DescuentosDetController::class, 'store']);
    Route::get('descuentos_det/read/{id}',                                  [DescuentosDetController::class, 'read']);
    Route::put('descuentos_det/update/{promociones_cab_id}',                [DescuentosDetController::class, 'update']);
    Route::delete('descuentos_det/delete/{promociones_cab_id}/{item_id}',   [DescuentosDetController::class, 'destroy']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('informes/compras',   [InformeComprasController::class,  'buscar']);
    Route::get('informes/servicio',  [InformeServicioController::class, 'buscar']);
    Route::get('informes/ventas',    [InformeVentasController::class,   'buscar']);

    Route::get('informes/gerencial/compras/catalogos',              [InformeGerencialComprasController::class, 'catalogos']);
    Route::get('informes/gerencial/compras/estadisticas/seccion',  [InformeGerencialComprasController::class, 'seccion']);
    Route::get('informes/gerencial/compras/estadisticas',          [InformeGerencialComprasController::class, 'estadisticas']);
    Route::get('informes/gerencial/compras/cuentas-pagar',        [InformeGerencialComprasController::class, 'cuentasAPagar']);
    Route::get('informes/gerencial/compras/items-comprados',       [InformeGerencialComprasController::class, 'itemsMasComprados']);
    Route::get('informes/gerencial/compras/items-transferidos',    [InformeGerencialComprasController::class, 'itemsMasTransferidos']);
    Route::get('informes/gerencial/compras/libro-impuesto',        [InformeGerencialComprasController::class, 'libroComprasPorImpuesto']);
    Route::get('informes/gerencial/compras/presupuestos-mes',      [InformeGerencialComprasController::class, 'presupuestosPorMes']);
    Route::get('informes/gerencial/compras/proveedor-presupuesto', [InformeGerencialComprasController::class, 'proveedorMasPresupuesto']);
    Route::get('informes/gerencial/compras/ajustes',               [InformeGerencialComprasController::class, 'ajustesInventario']);
    Route::get('informes/gerencial/servicio/estadisticas/seccion', [InformeGerencialServicioController::class, 'seccion']);
    Route::get('informes/gerencial/servicio/estadisticas',         [InformeGerencialServicioController::class, 'estadisticas']);
    Route::get('informes/gerencial/ventas/estadisticas/seccion',        [InformeGerencialVentasController::class, 'seccion']);
    Route::get('informes/gerencial/ventas/estadisticas',               [InformeGerencialVentasController::class, 'estadisticas']);
    Route::get('informes/gerencial/referencial/estadisticas',          [InformeGerencialReferencialController::class, 'estadisticas']);
    Route::get('informes/referencial',                                 [InformeGerencialReferencialController::class, 'index']);
    Route::get('dashboard/resumen',             [DashboardController::class, 'resumen']);
    Route::get('dashboard/ventas-por-mes',      [DashboardController::class, 'ventasPorMes']);
    Route::get('dashboard/top-productos',       [DashboardController::class, 'topProductos']);
    Route::get('dashboard/ventas-por-sucursal',  [DashboardController::class, 'ventasPorSucursal']);
    Route::get('dashboard/presupuestos-detalle', [DashboardController::class, 'presupuestosDetalle']);
    Route::get('dashboard/ventas-vs-compras',    [DashboardController::class, 'ventasVsCompras']);
});

Route::middleware(['auth:sanctum', 'permiso:cobros'])->group(function () {
    Route::get('cobros_cab/read',           [CobrosCabController::class, 'read']);
    Route::post('cobros_cab/create',        [CobrosCabController::class, 'store']);
    Route::put('cobros_cab/update/{id}',    [CobrosCabController::class, 'update']);
    Route::put('cobros_cab/anular/{id}',    [CobrosCabController::class, 'anular']);
    Route::put('cobros_cab/confirmar/{id}', [CobrosCabController::class, 'confirmar']);
    Route::get('cobros_cab/ctas/{id}',      [CobrosCabController::class, 'ctas']);
    Route::get('cobros_cab/detalle/{id}',   [CobrosCabController::class, 'detalle']);
    Route::get('cobros_cab/imprimir/{id}',      [CobrosCabController::class, 'imprimir']);
    Route::get('cobros_cab/enviar-recibo/{id}', [CobrosCabController::class, 'enviarRecibo']);

    Route::get('cobros_det/read/{cobros_cab_id}',   [CobrosDetController::class, 'read']);

    Route::get('ctas_cobrar/cliente/{cliente_id}',  [CtasCobrarController::class, 'buscarPorCliente']);

    Route::get('cobros_tarjeta/readByCobro/{id}',   [CobrosTarjetaController::class, 'readByCobro']);

    Route::get('cobros_cheque/readByCobro/{id}',    [CobrosChequeController::class, 'readByCobro']);

    Route::get('apertura_cierre_caja/read',             [AperturaCierreCajaController::class, 'read']);
    Route::post('apertura_cierre_caja/create',          [AperturaCierreCajaController::class, 'store']);
    Route::post('apertura_cierre_caja/anular',          [AperturaCierreCajaController::class, 'anular']);
    Route::put('apertura_cierre_caja/cerrarCaja',       [AperturaCierreCajaController::class, 'cerrarCaja']);
    Route::get('apertura_cierre_caja/abiertas',         [AperturaCierreCajaController::class, 'buscarAbiertas']);
    Route::get('apertura_cierre_caja/abiertas_arqueo',  [AperturaCierreCajaController::class, 'buscarAbiertasArqueo']);

    Route::get('recaudaciones_depositar/read',           [RecaudacionDepositarController::class, 'read']);
    Route::put('recaudaciones_depositar/depositar/{id}', [RecaudacionDepositarController::class, 'depositar']);
    Route::put('recaudaciones_depositar/anular/{id}',    [RecaudacionDepositarController::class, 'anular']);

    Route::get('arqueo_caja/read',           [ArqueoCajaController::class, 'read']);
    Route::post('arqueo_caja/create',        [ArqueoCajaController::class, 'store']);
    Route::put('arqueo_caja/anular/{id}',    [ArqueoCajaController::class, 'anular']);
    Route::put('arqueo_caja/confirmar/{id}', [ArqueoCajaController::class, 'confirmar']);
});
