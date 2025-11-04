<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdenCompraCabController;
use App\Http\Controllers\OrdenCompraDetController;
use App\Http\Controllers\CompraCabController;
use App\Http\Controllers\CompraDetController;
use App\Http\Controllers\LibroComprasController;
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
use App\Http\Controllers\NotaRemiCompController;
use App\Http\Controllers\NotaRemiComDetController;
use App\Http\Controllers\MotivoAjusteController;
use App\Http\Controllers\AjusteCabController;
use App\Http\Controllers\AjusteDetController;
use App\Http\Controllers\NotasComCabController;
use App\Http\Controllers\NotasComDetController;
use App\Http\Controllers\TipoServicioController;
use App\Http\Controllers\TipoDiagnosticoController;
use App\Http\Controllers\TipoPromocionesController;
use App\Http\Controllers\TipoDescuentosController;
use App\Http\Controllers\SolicitudCabController;
use App\Http\Controllers\SolicitudDetController;
use App\Http\Controllers\RecepcionCabController;
use App\Http\Controllers\RecepcionDetController;
use App\Http\Controllers\DiagnosticoCabController;
use App\Http\Controllers\DiagnosticoDetController;
use App\Http\Controllers\PromocionesCabController;
use App\Http\Controllers\PromocionesDetController;
use App\Http\Controllers\DescuentosCabController;
use App\Http\Controllers\DescuentosDetController;
use App\Http\Controllers\PresupuestoServCabController;
use App\Http\Controllers\PresupuestoServDetController;
use App\Http\Controllers\OrdenServCabController;
use App\Http\Controllers\OrdenServDetController;
use App\Http\Controllers\ContratoServCabController;
use App\Http\Controllers\ContratoServDetController;


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
Route::get("pedidos/buscar-informe", [PedidoController::class, "buscarInforme"]);

Route::post("pedidos-detalles/create",[PedidosDetalleController::class,"store"]);
Route::get("pedidos-detalles/read/{id}",[PedidosDetalleController::class,"read"]);
Route::put("pedidos-detalles/update/{pedido_id}",[PedidosDetalleController::class,"update"]);
Route::delete("pedidos-detalles/delete/{pedido_id}/{item_id}",[PedidosDetalleController::class,"destroy"]);

Route::post("ordencompracab/create",[OrdenCompraCabController::class,"store"]);
Route::get("ordencompracab/read",[OrdenCompraCabController::class,"read"]);
Route::put("ordencompracab/update/{id}",[OrdenCompraCabController::class,"update"]);
Route::put("ordencompracab/anular/{id}",[OrdenCompraCabController::class,"anular"]);
Route::delete("ordencompracab/delete/{id}",[OrdenCompraCabController::class,"eliminar"]);
Route::put("ordencompracab/confirmar/{id}",[OrdenCompraCabController::class,"confirmar"]);
Route::post("ordencompracab/buscar",[OrdenCompraCabController::class,"buscar"]);
Route::get("ordenes_compras/buscar-informe", [OrdenCompraCabController::class, "buscarInforme"]);



Route::post("compras/create", [CompraCabController::class, "store"]);
Route::get("compras/read", [CompraCabController::class, "read"]);
Route::put("compras/update/{id}", [CompraCabController::class, "update"]);
Route::put("compras/anular/{id}", [CompraCabController::class, "anular"]);
Route::put("compras/confirmar/{id}", [CompraCabController::class, "confirmar"]);
Route::post("compras/buscar", [CompraCabController::class, "buscar"]);
Route::get("compras/buscar-informe", [CompraCabController::class, "buscarInforme"]);

Route::post("compradet/create",[CompraDetController::class,"store"]);
Route::get("compradet/read/{id}",[CompraDetController::class,"read"]);
Route::put("compradet/update/{compra_cab_id}/{item_id}",[CompraDetController::class,"update"]);
Route::delete("compradet/delete/{compra_cab_id}/{item_id}",[CompraDetController::class,"destroy"]);

Route::get("notaremicomp/read",[NotaRemiCompController::class,"read"]);
Route::post("notaremicomp/create",[NotaRemiCompController::class,"store"]);
Route::put("notaremicomp/update/{id}",[NotaRemiCompController::class,"update"]);
Route::put("notaremicomp/anular/{id}",[NotaRemiCompController::class,"anular"]);
Route::put("notaremicomp/confirmar/{id}", [NotaRemiCompController::class, "confirmar"]);
Route::get("notaremicomp/buscar-informe", [NotaRemiCompController::class, "buscarInforme"]);

Route::post("notaremicomdet/create",[NotaRemiComDetController::class,"store"]);
Route::get("notaremicomdet/read/{id}",[NotaRemiComDetController::class,"read"]);
Route::put("notaremicomdet/update/{nota_remi_comp_id}",[NotaRemiComDetController::class,"update"]);
Route::delete("notaremicomdet/delete/{nota_remi_comp_id}/{item_id}",[NotaRemiComDetController::class,"destroy"]);

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
Route::get("presupuestos/buscar-informe", [PresupuestoController::class, "buscarInforme"]);


Route::get("presupuestos-detalles/read/{id}",[PresupuestosDetalleController::class,"read"]);
Route::post("presupuestos-detalles/create",[PresupuestosDetalleController::class,"store"]);
Route::put("presupuestos-detalles/update/{presupuesto_id}/{item_id}",[PresupuestosDetalleController::class,"update"]);
Route::delete("presupuestos-detalles/delete/{presupuesto_id}/{item_id}",[PresupuestosDetalleController::class,"destroy"]);

Route::post("ajus_cab/create",[AjusteCabController::class,"store"]);
Route::get("ajus_cab/read",[AjusteCabController::class,"read"]);
Route::put("ajus_cab/update/{id}",[AjusteCabController::class,"update"]);
Route::put("ajus_cab/anular/{id}",[AjusteCabController::class,"anular"]);
Route::put("ajus_cab/confirmar/{id}",[AjusteCabController::class,"confirmar"]);
Route::get("ajus_cab/buscar-informe", [AjusteCabController::class, "buscarInforme"]);

Route::post("ajus_det/create",[AjusteDetController::class,"store"]);
Route::get("ajus_det/read/{id}",[AjusteDetController::class,"read"]);
Route::put("ajus_det/update/{pedido_id}",[AjusteDetController::class,"update"]);
Route::delete("ajus_det/delete/{pedido_id}/{item_id}",[AjusteDetController::class,"destroy"]);

Route::post("notacompcab/create",[NotasComCabController::class,"store"]);
Route::get("notacompcab/read",[NotasComCabController::class,"read"]);
Route::put("notacompcab/update/{id}",[NotasComCabController::class,"update"]);
Route::put("notacompcab/anular/{id}",[NotasComCabController::class,"anular"]);
Route::delete("notacompcab/delete/{id}",[NotasComCabController::class,"eliminar"]);
Route::put("notacompcab/confirmar/{id}",[NotasComCabController::class,"confirmar"]);
Route::get("notacompcab/buscar-informe", [NotasComCabController::class, "buscarInforme"]);

Route::post("notacompdet/create",[NotasComDetController::class,"store"]);
Route::get("notacompdet/read/{id}",[NotasComDetController::class,"read"]);
Route::put("notacompdet/update/{notas_comp_cab_id}/{item_id}",[NotasComDetController::class,"update"]);
Route::delete("notacompdet/delete/{notas_comp_cab_id}/{item_id}",[NotasComDetController::class,"destroy"]);

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

Route::get("motivo_ajuste/read",[MotivoAjusteController::class,"read"]);
Route::post("motivo_ajuste/create",[MotivoAjusteController::class,"store"]);
Route::put("motivo_ajuste/update/{id}",[MotivoAjusteController::class,"update"]);
Route::delete("motivo_ajuste/delete/{id}",[MotivoAjusteController::class,"destroy"]);

Route::get("tipo-servicio/read",[TipoServicioController::class,"read"]);
Route::post("tipo-servicio/create",[TipoServicioController::class,"store"]);
Route::put("tipo-servicio/update/{id}",[TipoServicioController::class,"update"]);
Route::delete("tipo-servicio/delete/{id}",[TipoServicioController::class,"destroy"]);
Route::get("tipo-servicio/buscar",[TipoServicioController::class,"buscar"]);

Route::get("tipo-diagnostico/read",[TipoDiagnosticoController::class,"read"]);
Route::post("tipo-diagnostico/create",[TipoDiagnosticoController::class,"store"]);
Route::put("tipo-diagnostico/update/{id}",[TipoDiagnosticoController::class,"update"]);
Route::delete("tipo-diagnostico/delete/{id}",[TipoDiagnosticoController::class,"destroy"]);
Route::get("tipo-diagnostico/buscar",[TipoDiagnosticoController::class,"buscar"]);

Route::get("tipo-promociones/read",[TipoPromocionesController::class,"read"]);
Route::post("tipo-promociones/create",[TipoPromocionesController::class,"store"]);
Route::put("tipo-promociones/update/{id}",[TipoPromocionesController::class,"update"]);
Route::delete("tipo-promociones/delete/{id}",[TipoPromocionesController::class,"destroy"]);
Route::get("tipo-promociones/buscar",[TipoPromocionesController::class,"buscar"]);

Route::get("tipo-descuentos/read",[TipoDescuentosController::class,"read"]);
Route::post("tipo-descuentos/create",[TipoDescuentosController::class,"store"]);
Route::put("tipo-descuentos/update/{id}",[TipoDescuentosController::class,"update"]);
Route::delete("tipo-descuentos/delete/{id}",[TipoDescuentosController::class,"destroy"]);
Route::get("tipo-descuentos/buscar",[TipoDescuentosController::class,"buscar"]);

Route::post("solicitudcad/create",[SolicitudCabController::class,"store"]);
Route::get("solicitudcad/read",[SolicitudCabController::class,"read"]);
Route::put("solicitudcad/update/{id}",[SolicitudCabController::class,"update"]);
Route::put("solicitudcad/anular/{id}",[SolicitudCabController::class,"anular"]);
Route::put("solicitudcad/confirmar/{id}",[SolicitudCabController::class,"confirmar"]);
Route::post("solicitudcad/buscar",[SolicitudCabController::class,"buscar"]);
Route::get("solicitudcad/buscar-informe", [SolicitudCabController::class, "buscarInforme"]);

Route::post("solicitud_det/create",[SolicitudDetController::class,"store"]);
Route::get("solicitud_det/read/{id}",[SolicitudDetController::class,"read"]);
Route::put("solicitud_det/update/{solicitudes_cab_id}",[SolicitudDetController::class,"update"]);
Route::delete("solicitud_det/delete/{solicitudes_cab_id}/{item_id}",[SolicitudDetController::class,"destroy"]);

Route::post("recepcab/create",[RecepcionCabController::class,"store"]);
Route::get("recepcab/read",[RecepcionCabController::class,"read"]);
Route::put("recepcab/update/{id}",[RecepcionCabController::class,"update"]);
Route::put("recepcab/anular/{id}",[RecepcionCabController::class,"anular"]);
Route::put("recepcab/confirmar/{id}",[RecepcionCabController::class,"confirmar"]);
Route::post("recepcab/buscar",[RecepcionCabController::class,"buscar"]);
Route::get("recepcab/buscar-informe", [RecepcionCabController::class, "buscarInforme"]);

Route::post("recepcion_det/create",[RecepcionDetController::class,"store"]);
Route::get("recepcion_det/read/{id}",[RecepcionDetController::class,"read"]);
Route::put("recepcion_det/update/{solicitudes_cab_id}",[RecepcionDetController::class,"update"]);
Route::delete("recepcion_det/delete/{solicitudes_cab_id}/{item_id}",[RecepcionDetController::class,"destroy"]);

Route::post("diagnosticocab/create",[DiagnosticoCabController::class,"store"]);
Route::get("diagnosticocab/read",[DiagnosticoCabController::class,"read"]);
Route::put("diagnosticocab/update/{id}",[DiagnosticoCabController::class,"update"]);
Route::put("diagnosticocab/anular/{id}",[DiagnosticoCabController::class,"anular"]);
Route::put("diagnosticocab/confirmar/{id}",[DiagnosticoCabController::class,"confirmar"]);
Route::post("diagnosticocab/buscar",[DiagnosticoCabController::class,"buscar"]);
Route::get("diagnosticocab/buscar-informe", [DiagnosticoCabController::class, "buscarInforme"]);

Route::post("diagnostico_det/create",[DiagnosticoDetController::class,"store"]);
Route::get("diagnostico_det/read/{id}",[DiagnosticoDetController::class,"read"]);
Route::put("diagnostico_det/update/{solicitudes_cab_id}",[DiagnosticoDetController::class,"update"]);
Route::delete("diagnostico_det/delete/{solicitudes_cab_id}/{item_id}",[DiagnosticoDetController::class,"destroy"]);

Route::post("promocionescab/create",[PromocionesCabController::class,"store"]);
Route::get("promocionescab/read",[PromocionesCabController::class,"read"]);
Route::put("promocionescab/update/{id}",[PromocionesCabController::class,"update"]);
Route::put("promocionescab/anular/{id}",[PromocionesCabController::class,"anular"]);
Route::put("promocionescab/confirmar/{id}",[PromocionesCabController::class,"confirmar"]);
Route::post("promocionescab/buscar",[PromocionesCabController::class,"buscar"]);
Route::get("promocionescab/buscar-informe", [PromocionesCabController::class, "buscarInforme"]);


Route::post("promociones_det/create",[PromocionesDetController::class,"store"]);
Route::get("promociones_det/read/{id}",[PromocionesDetController::class,"read"]);
Route::put("promociones_det/update/{promociones_cab_id}",[PromocionesDetController::class,"update"]);
Route::delete("promociones_det/delete/{promociones_cab_id}/{item_id}",[PromocionesDetController::class,"destroy"]);

Route::post("descuentoscab/create",[DescuentosCabController::class,"store"]);
Route::get("descuentoscab/read",[DescuentosCabController::class,"read"]);
Route::put("descuentoscab/update/{id}",[DescuentosCabController::class,"update"]);
Route::put("descuentoscab/anular/{id}",[DescuentosCabController::class,"anular"]);
Route::put("descuentoscab/confirmar/{id}",[DescuentosCabController::class,"confirmar"]);
Route::post("descuentoscab/buscar",[DescuentosCabController::class,"buscar"]);
Route::get("descuentoscab/buscar-informe", [DescuentosCabController::class, "buscarInforme"]);

Route::post("descuentos_det/create",[DescuentosDetController::class,"store"]);
Route::get("descuentos_det/read/{id}",[DescuentosDetController::class,"read"]);
Route::put("descuentos_det/update/{promociones_cab_id}",[DescuentosDetController::class,"update"]);
Route::delete("descuentos_det/delete/{promociones_cab_id}/{item_id}",[DescuentosDetController::class,"destroy"]);

Route::post("presupuestoservcab/create",[PresupuestoServCabController::class,"store"]);
Route::get("presupuestoservcab/read",[PresupuestoServCabController::class,"read"]);
Route::put("presupuestoservcab/update/{id}",[PresupuestoServCabController::class,"update"]);
Route::put("presupuestoservcab/anular/{id}",[PresupuestoServCabController::class,"anular"]);
Route::put("presupuestoservcab/confirmar/{id}",[PresupuestoServCabController::class,"confirmar"]);
Route::post("presupuestoservcab/buscar",[PresupuestoServCabController::class,"buscar"]);
Route::get("presupuestoservcab/buscar-informe", [PresupuestoServCabController::class, "buscarInforme"]);

Route::post("presupuesto_serv_det/create",[PresupuestoServDetController::class,"store"]);
Route::get("presupuesto_serv_det/read/{id}",[PresupuestoServDetController::class,"read"]);
Route::put("presupuesto_serv_det/update/{presupuesto_serv_cab_id}",[PresupuestoServDetController::class,"update"]);
Route::delete("presupuesto_serv_det/delete/{presupuesto_serv_cab_id}/{item_id}",[PresupuestoServDetController::class,"destroy"]);

Route::post("ordenserviciocab/create",[OrdenServCabController::class,"store"]);
Route::get("ordenserviciocab/read",[OrdenServCabController::class,"read"]);
Route::put("ordenserviciocab/update/{id}",[OrdenServCabController::class,"update"]);
Route::put("presupuestosordenserviciocabervcab/anular/{id}",[OrdenServCabController::class,"anular"]);
Route::put("ordenserviciocab/confirmar/{id}",[OrdenServCabController::class,"confirmar"]);
Route::post("ordenserviciocab/buscar",[OrdenServCabController::class,"buscar"]);
Route::get("ordenserviciocab/buscar-informe", [OrdenServCabController::class, "buscarInforme"]);

Route::post("ordenservicodet/create",[OrdenServDetController::class,"store"]);
Route::get("ordenserviciodet/read/{id}",[OrdenServDetController::class,"read"]);
Route::put("ordenserviciodet/update/{orden_serv_cab_id}",[OrdenServDetController::class,"update"]);
Route::delete("ordenserviciodet/delete/{orden_serv_cab_id}/{item_id}",[OrdenServDetController::class,"destroy"]);

Route::post("contratoservcab/create",[ContratoServCabController::class,"store"]);
Route::get("contratoservcab/read",[ContratoServCabController::class,"read"]);
Route::put("contratoservcab/update/{id}",[ContratoServCabController::class,"update"]);
Route::put("contratoservcab/anular/{id}",[ContratoServCabController::class,"anular"]);
Route::put("contratoservcab/confirmar/{id}",[ContratoServCabController::class,"confirmar"]);
Route::post("contratoservcab/buscar",[ContratoServCabController::class,"buscar"]);
Route::get("contratoservcab/buscar-informe", [ContratoServCabController::class, "buscarInforme"]);

Route::post("contratoservdet/create",[ContratoServDetController::class,"store"]);
Route::get("contratoservdet/read/{id}",[ContratoServDetController::class,"read"]);
Route::put("contratoservdet/update/{contrato_serv_cab_id}",[ContratoServDetController::class,"update"]);
Route::delete("contratoservdet/delete/{contrato_serv_cab_id}/{item_id}",[ContratoServDetController::class,"destroy"]);

Route::get("libro_compras/buscar-informe", [LibroComprasController::class, "buscarInforme"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('registrarse',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('logout',[AuthController::class, 'logout']);
});