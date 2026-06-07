<changeSet author="CHANGEME" id="1">

<!-- ================================================================== -->
<!-- NORMALIZACIÓN A 3FN                                                  -->
<!-- Cambios principales:                                                  -->
<!-- 1. Se elimina emp_id de todas las tablas que tienen suc_id           -->
<!--    (SUCURSAL ya tiene emp_id → dependencia transitiva)               -->
<!-- 2. Se elimina pais_id de PROVEEDOR y FUNCIONARIO                    -->
<!--    (CIUDAD ya tiene pais_id → dependencia transitiva)                -->
<!-- 3. Se elimina cli_cod de LIB_VENTA y ORDEN_SERV_CAB                 -->
<!-- 4. Se elimina prov_id de LIBRO_COMPRA                                -->
<!-- 5. Se eliminan emp_id, suc_id de ARQUEO_CTROL                        -->
<!-- 6. ENTIDAD_ADHERIDA queda como tabla de relación pura (2FN)          -->
<!-- 7. Se agregan PKs faltantes a todas las tablas                       -->
<!-- ================================================================== -->

<!-- CATÁLOGOS INDEPENDIENTES -->

<createTable tableName="TIPO_CONTRATO">
  <column name="tipo_con_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_con_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_con_objeto" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_alcance" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_garantia" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_responsabilidad" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_limitacion" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_fuerza_mayor" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_jurisdiccion" type="VARCHAR(2000)"><constraints nullable="false"/></column>
  <column name="tip_con_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_CONTRATO" constraintName="TIPO_CONTRATO_pk" columnNames="tipo_con_id"/>

<createTable tableName="TIPO_COMPROBANTE">
  <column name="tip_comp_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_comp_descrip" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_COMPROBANTE" constraintName="TIPO_COMPROBANTE_pk" columnNames="tip_comp_cod"/>

<createTable tableName="PERMISOS">
  <column name="per_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="per_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="per_descripcion" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="per_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="per_fecha" type="DATE"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PERMISOS" constraintName="PERMISOS_pk" columnNames="per_id"/>

<createTable tableName="MODULOS">
  <column name="mod_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="mod_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="mod_descripcion" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="mod_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="mod_fecha" type="DATE"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="MODULOS" constraintName="MODULOS_pk" columnNames="mod_id"/>

<createTable tableName="ROLES">
  <column name="rol_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="rol_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="rol_descripcion" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="rol_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="rol_fecha" type="DATE"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ROLES" constraintName="ROLES_pk" columnNames="rol_id"/>

<createTable tableName="ACCESOS">
  <column name="acc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="rol_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="mod_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="per_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="acc_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="acc_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ACCESOS" constraintName="ACCESOS_pk" columnNames="acc_id"/>

<createTable tableName="TIPO_DESCUENTOS">
  <column name="tip_desc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_desc_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_desc_descrip" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="tip_desc_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="tip_desc_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_DESCUENTOS" constraintName="TIPO_DESCUENTOS_pk" columnNames="tip_desc_id"/>

<createTable tableName="TIPO_PROMOCIONES">
  <column name="tip_prom_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_prom_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_prom_descrip" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="tip_prom_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="tip_prom_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="tip_prom_modo" type="VARCHAR(50)"><constraints nullable="false"/></column>
  <column name="tip_prom_valor" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_PROMOCIONES" constraintName="TIPO_PROMOCIONES_pk" columnNames="tip_prom_id"/>

<createTable tableName="TIPO_SERVICIO">
  <column name="tip_serv_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_serv_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_serv_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_SERVICIO" constraintName="TIPO_SERVICIO_pk" columnNames="tip_serv_id"/>

<createTable tableName="TIPO_DIAGNOSTICO">
  <column name="tip_diag_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_diag_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_diag_descri" type="VARCHAR(255)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_DIAGNOSTICO" constraintName="TIPO_DIAGNOSTICO_pk" columnNames="tip_diag_id"/>

<createTable tableName="TIPO_IMPUESTO">
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tipo_imp_nom" type="VARCHAR(50)"><constraints nullable="false"/></column>
  <column name="tipo_imp_tasa" type="NUMERIC(5,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_IMPUESTO" constraintName="TIPO_IMPUESTO_pk" columnNames="tipo_imp_id"/>

<createTable tableName="TIPO_ITEMS">
  <column name="tipo_item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tipo_item_descri" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tipo_item_objeto" type="VARCHAR(255)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_ITEMS" constraintName="TIPO_ITEMS_pk" columnNames="tipo_item_id"/>

<createTable tableName="MOTIVO_AJUSTE">
  <column name="mot_ajus_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="mot_ajus_decri" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="mot_ajus_tipo" type="VARCHAR(50)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="MOTIVO_AJUSTE" constraintName="MOTIVO_AJUSTE_pk" columnNames="mot_ajus_id"/>

<createTable tableName="FORMA_COBRO">
  <column name="forma_cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="forma_pago_descri" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="FORMA_COBRO" constraintName="FORMA_COBRO_pk" columnNames="forma_cobro_cod"/>

<createTable tableName="CAJA">
  <column name="caja_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="caja_descrip" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="CAJA" constraintName="CAJA_pk" columnNames="caja_cod"/>

<createTable tableName="MARCA_TARJETA">
  <column name="marc_tarj_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marc_tarj_descri" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="MARCA_TARJETA" constraintName="MARCA_TARJETA_pk" columnNames="marc_tarj_cod"/>

<!-- GEOGRAFÍA -->
<createTable tableName="PAIS">
  <column name="pais_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pais_descripcion" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="pais_siglas" type="VARCHAR(10)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PAIS" constraintName="PAIS_pk" columnNames="pais_id"/>

<createTable tableName="NACIONALIDAD">
  <column name="nacionalidad_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pais_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nacionalidad_descripcion" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NACIONALIDAD" constraintName="NACIONALIDAD_pk" columnNames="nacionalidad_id"/>

<!-- CIUDAD: PK compuesta (ciu_id, pais_id) para mantener integridad geográfica -->
<createTable tableName="CIUDAD">
  <column name="ciu_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pais_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ciu_descrip" type="VARCHAR(100)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="CIUDAD" constraintName="CIUDAD_pk" columnNames="ciu_id, pais_id"/>

<!-- MARCA / MODELO (items y vehículos) -->
<createTable tableName="MODELO">
  <column name="modelo_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="modelo_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="modelo_tipo" type="VARCHAR(50)"><constraints nullable="false"/></column>
  <column name="modelo_año" type="VARCHAR(4)"/>
</createTable>
<addPrimaryKey tableName="MODELO" constraintName="MODELO_pk" columnNames="modelo_id"/>

<createTable tableName="MARCA">
  <column name="marca_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="marca_tipo" type="VARCHAR(50)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="MARCA" constraintName="MARCA_pk" columnNames="marca_id"/>

<createTable tableName="TIPO_VEHICULO">
  <column name="tip_veh_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_veh_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="tip_veh_capacidad" type="INTEGER"/>
  <column name="tip_veh_combustible" type="VARCHAR(30)"/>
  <column name="tip_veh_categoria" type="VARCHAR(30)"/>
  <column name="tip_veh_observacion" type="VARCHAR(200)"/>
  <column name="marca_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="modelo_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIPO_VEHICULO" constraintName="TIPO_VEHICULO_pk" columnNames="tip_veh_id"/>

<!-- ENTIDAD EMISORA: ya contiene la info de contacto de la entidad -->
<createTable tableName="ENTIDAD_EMISORA">
  <column name="entidad_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="entidad_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="entidad_nro" type="INTEGER"/>
  <column name="entidad_direc" type="VARCHAR(200)"/>
  <column name="entidad_telef" type="VARCHAR(20)"/>
  <column name="entidad_correo" type="VARCHAR(100)"/>
</createTable>
<addPrimaryKey tableName="ENTIDAD_EMISORA" constraintName="ENTIDAD_EMISORA_pk" columnNames="entidad_cod"/>

<!-- ENTIDAD_ADHERIDA: tabla de relación pura (2FN corregida).             -->
<!-- Se eliminaron enti_adhe_nro/direc/telef/correo porque dependían       -->
<!-- solo de entidad_cod, no del compuesto (marc_tarj_cod, entidad_cod).   -->
<createTable tableName="ENTIDAD_ADHERIDA">
  <column name="marc_tarj_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="entidad_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ENTIDAD_ADHERIDA" constraintName="ENTIDAD_ADHERIDA_pk" columnNames="marc_tarj_cod, entidad_cod"/>


<!-- ================================================================== -->
<!-- PERSONAS: FUNCIONARIO, CLIENTE, PROVEEDOR                           -->
<!-- Corrección 3FN: se elimina pais_id de FUNCIONARIO y PROVEEDOR       -->
<!-- porque CIUDAD(ciu_id, pais_id) ya determina el país transitivamente  -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="2">

<createTable tableName="FUNCIONARIO">
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="fun_apellido" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="fun_correo" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="fun_direcc" type="VARCHAR(200)"/>
  <column name="fun_telefono" type="VARCHAR(20)"/>
  <column name="fun_CI" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ciu_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- pais_id ELIMINADO: derivable vía ciu_id → CIUDAD.pais_id (3FN) -->
  <column name="nacionalidad_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="FUNCIONARIO" constraintName="FUNCIONARIO_pk" columnNames="fun_id"/>

<createTable tableName="CLIENTE">
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cli_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="cli_apellido" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="cli_telef" type="VARCHAR(20)"/>
  <column name="cli_direcc" type="VARCHAR(200)"/>
  <column name="cli_correo" type="VARCHAR(100)"/>
  <column name="cli_ruc" type="VARCHAR(20)"/>
</createTable>
<addPrimaryKey tableName="CLIENTE" constraintName="CLIENTE_pk" columnNames="cli_cod"/>

<createTable tableName="PROVEEDOR">
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prov_razonsocial" type="VARCHAR(200)"><constraints nullable="false"/></column>
  <column name="prov_direc" type="VARCHAR(200)"/>
  <column name="prov_nro_documento" type="VARCHAR(30)"/>
  <column name="prov_telef" type="VARCHAR(20)"/>
  <column name="prov_correo" type="VARCHAR(100)"/>
  <column name="prov_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ciu_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- pais_id ELIMINADO: derivable vía ciu_id → CIUDAD.pais_id (3FN) -->
  <column name="nacionalidad_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PROVEEDOR" constraintName="PROVEEDOR_pk" columnNames="prov_id"/>

<!-- EMPRESA / SUCURSAL / USUARIOS / SEGURIDAD -->

<createTable tableName="EMPRESA">
  <column name="emp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="emp_razonsocial" type="VARCHAR(200)"><constraints nullable="false"/></column>
  <column name="emp_direcc" type="VARCHAR(200)"/>
  <column name="emp_ruc" type="VARCHAR(20)"/>
  <column name="emp_correo" type="VARCHAR(100)"/>
  <column name="emp_telefono" type="VARCHAR(20)"/>
</createTable>
<addPrimaryKey tableName="EMPRESA" constraintName="EMPRESA_pk" columnNames="emp_id"/>

<createTable tableName="SUCURSAL">
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="emp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_razonsocial" type="VARCHAR(200)"><constraints nullable="false"/></column>
  <column name="suc_direcc" type="VARCHAR(200)"/>
  <column name="suc_correo" type="VARCHAR(100)"/>
  <column name="suc_telefono" type="VARCHAR(20)"/>
</createTable>
<addPrimaryKey tableName="SUCURSAL" constraintName="SUCURSAL_pk" columnNames="suc_id"/>

<createTable tableName="USUARIOS">
  <column name="user_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="user_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="user_apellido" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="user_login" type="VARCHAR(50)"><constraints nullable="false"/></column>
  <column name="user_clave" type="VARCHAR(255)"><constraints nullable="false"/></column>
  <column name="user_correo" type="VARCHAR(100)"/>
  <column name="rol_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="user_fecha" type="DATE"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="USUARIOS" constraintName="USUARIOS_pk" columnNames="user_id"/>

<createTable tableName="AUDITORIA">
  <column name="aud_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="user_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="aud_tabla" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="aud_registro_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="aud_accion" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="aud_valor_anterior" type="VARCHAR(1000)"/>
  <column name="aud_valor_nuevo" type="VARCHAR(1000)"/>
  <column name="aud_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="aud_ip" type="VARCHAR(45)"/>
  <column name="aud_descripcion" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="AUDITORIA" constraintName="AUDITORIA_pk" columnNames="aud_id"/>

<!-- EQUIPO_TRABAJO: se elimina fun_id (un equipo no pertenece a un solo funcionario) -->
<createTable tableName="EQUIPO_TRABAJO">
  <column name="equi_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="equi_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="equi_descri" type="VARCHAR(255)"/>
  <column name="equi_categoria" type="VARCHAR(50)"/>
</createTable>
<addPrimaryKey tableName="EQUIPO_TRABAJO" constraintName="EQUIPO_TRABAJO_pk" columnNames="equi_cod"/>

<!-- ITEMS Y STOCK -->
<createTable tableName="ITEMS">
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tipo_item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_descri" type="VARCHAR(200)"><constraints nullable="false"/></column>
  <column name="item_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="item_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ITEMS" constraintName="ITEMS_pk" columnNames="item_id"/>

<createTable tableName="DEPOSITO">
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_descrip" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="DEPOSITO" constraintName="DEPOSITO_pk" columnNames="deposito_id"/>

<createTable tableName="STOCK">
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="stock_cantidad" type="INTEGER"><constraints nullable="false"/></column>
  <column name="stock_minimo" type="INTEGER"/>
  <column name="stock_maximo" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="STOCK" constraintName="STOCK_pk" columnNames="item_id, deposito_id"/>

<!-- Tablas de relación ITEM-MARCA e ITEM-MODELO -->
<createTable tableName="ITEM_MARCA">
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ITEM_MARCA" constraintName="ITEM_MARCA_pk" columnNames="item_id, marca_id"/>

<createTable tableName="ITEM_MODELO">
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="modelo_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ITEM_MODELO" constraintName="ITEM_MODELO_pk" columnNames="item_id, modelo_id"/>

<!-- TIMBRADO -->
<!-- Corrección 3FN: se elimina emp_id (derivable vía suc_id → SUCURSAL.emp_id) -->
<createTable tableName="TIMBRADO">
  <column name="timb_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="timb_nro" type="NUMERIC(15,0)"><constraints nullable="false"/></column>
  <column name="timb_nro_desde" type="NUMERIC(15,0)"><constraints nullable="false"/></column>
  <column name="timb_nro_hasta" type="NUMERIC(15,0)"><constraints nullable="false"/></column>
  <column name="timb_nro_actual" type="NUMERIC(15,0)"><constraints nullable="false"/></column>
  <column name="timb_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="timb_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="timb_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="tip_comp_cod" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="TIMBRADO" constraintName="TIMBRADO_pk" columnNames="timb_cod"/>

</changeSet>

<!-- ================================================================== -->
<!-- MÓDULO SERVICIO                                                      -->
<!-- Corrección 3FN: se elimina emp_id de todas las tablas               -->
<!-- que tienen suc_id (suc_id → SUCURSAL.emp_id)                        -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="3">

<createTable tableName="SOLICITUD_CAB">
  <column name="soli_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="soli_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="soli_fecha_estimada" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="soli_prioridad" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="soli_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="soli_observaciones" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_serv_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="SOLICITUD_CAB" constraintName="SOLICITUD_CAB_pk" columnNames="soli_id"/>

<createTable tableName="SOLICITUD_DET">
  <column name="soli_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="soli_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="soli_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="SOLICITUD_DET" constraintName="SOLICITUD_DET_pk" columnNames="soli_id, item_id"/>

<createTable tableName="RECEP_CAB">
  <column name="recep_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="recep_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="recep_fecha_estimada" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="recep_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="recep_prioridad" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="recep_kilometraje" type="NUMERIC(10,2)"/>
  <column name="recep_nivel_combustible" type="VARCHAR(50)"/>
  <column name="recep_observaciones" type="VARCHAR(500)"/>
  <column name="soli_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- cli_cod ELIMINADO: derivable vía soli_id → SOLICITUD_CAB.cli_cod (3FN) -->
  <column name="tip_veh_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="RECEP_CAB" constraintName="RECEP_CAB_pk" columnNames="recep_id"/>

<createTable tableName="RECP_DET">
  <column name="recep_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="recep_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="recep_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="RECP_DET" constraintName="RECP_DET_pk" columnNames="recep_id, item_id"/>

<createTable tableName="DIAGNOSTICO_CAB">
  <column name="diag_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="diag_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="diag_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="diag_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="recep_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tip_diag_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="DIAGNOSTICO_CAB" constraintName="DIAGNOSTICO_CAB_pk" columnNames="diag_id"/>

<createTable tableName="DIAGNOSTICO_DET">
  <column name="diag_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="diag_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="diag_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="DIAGNOSTICO_DET" constraintName="DIAGNOSTICO_DET_pk" columnNames="diag_id, item_id"/>

<createTable tableName="PRES_SER_CAB">
  <column name="pres_serv_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <!-- cli_cod derivable vía diag_id → RECEP_CAB → SOLICITUD_CAB.cli_cod -->
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="diag_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_serv_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="pres_serv_fecha_vence" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="pres_ser_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="pres_serv_obs" type="VARCHAR(500)"/>
  <column name="prom_id" type="INTEGER"/>
  <column name="desc_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="PRES_SER_CAB" constraintName="PRES_SER_CAB_pk" columnNames="pres_serv_id"/>

<createTable tableName="PRES_SER_DET">
  <column name="pres_serv_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_serv_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="pres_serv_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="PRES_SER_DET" constraintName="PRES_SER_DET_pk" columnNames="pres_serv_id, item_id"/>

<!-- ORDEN_SERV_CAB: se elimina cli_cod (derivable vía pres_serv_id → PRES_SER_CAB.cli_cod) -->
<createTable tableName="ORDEN_SERV_CAB">
  <column name="ord_ser_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ord_ser_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ord_ser_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ord_ser_obs" type="VARCHAR(500)"/>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <!-- cli_cod ELIMINADO: derivable vía pres_serv_id → PRES_SER_CAB.cli_cod (3FN) -->
  <column name="pres_serv_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="equi_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="ORDEN_SERV_CAB" constraintName="ORDEN_SERV_CAB_pk" columnNames="ord_ser_id"/>

<createTable tableName="ORDEN_SERV_DET">
  <column name="ord_ser_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ord_ser_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="ord_ser_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="ORDEN_SERV_DET" constraintName="ORDEN_SERV_DET_pk" columnNames="ord_ser_id, item_id"/>

<createTable tableName="INSUM_UTILI">
  <column name="ord_ser_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="insum_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="insum_precio_uni" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="INSUM_UTILI" constraintName="INSUM_UTILI_pk" columnNames="ord_ser_id, item_id"/>

<createTable tableName="RECLAMO_CAB">
  <column name="recla_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="recla_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="recla_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="recla_fecha_inicio" type="DATE"><constraints nullable="false"/></column>
  <column name="recla_fecha_fin" type="DATE"><constraints nullable="false"/></column>
  <column name="recla_obs" type="VARCHAR(500)"/>
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
</createTable>
<addPrimaryKey tableName="RECLAMO_CAB" constraintName="RECLAMO_CAB_pk" columnNames="recla_id"/>

<createTable tableName="RECLAMO_DET">
  <column name="recla_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="recla_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="RECLAMO_DET" constraintName="RECLAMO_DET_pk" columnNames="recla_id, item_id"/>

<createTable tableName="CONTRATO_CAB">
  <column name="cont_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cont_fecha" type="DATE"><constraints nullable="false"/></column>
  <column name="cont_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cont_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cont_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="cont_clausas_especiales" type="VARCHAR(2000)"/>
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="tipo_con_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="CONTRATO_CAB" constraintName="CONTRATO_CAB_pk" columnNames="cont_id"/>

<createTable tableName="CONTRATO_DET">
  <column name="cont_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cont_descrip" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="CONTRATO_DET" constraintName="CONTRATO_DET_pk" columnNames="cont_id, item_id"/>

<createTable tableName="DESC_CAB">
  <column name="desc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="desc_nombre" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="desc_porcentaje" type="DECIMAL(5,2)"><constraints nullable="false"/></column>
  <column name="desc_fecha_registro" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="desc_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="desc_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="desc_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="tip_desc_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="DESC_CAB" constraintName="DESC_CAB_pk" columnNames="desc_id"/>

<createTable tableName="DESC_DET">
  <column name="desc_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="desc_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="desc_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="DESC_DET" constraintName="DESC_DET_pk" columnNames="desc_id, item_id"/>

<createTable tableName="PROMOCION_CAB">
  <column name="prom_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prom_nom" type="VARCHAR(100)"><constraints nullable="false"/></column>
  <column name="prom_fecha_registro" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="prom_fecha_inicio" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="prom_fecha_fin" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="prom_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="tip_prom_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PROMOCION_CAB" constraintName="PROMOCION_CAB_pk" columnNames="prom_id"/>

<createTable tableName="PROMOCION_DET">
  <column name="prom_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prom_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="prom_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PROMOCION_DET" constraintName="PROMOCION_DET_pk" columnNames="prom_id, item_id"/>

</changeSet>

<!-- ================================================================== -->
<!-- MÓDULO VENTAS                                                        -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="4">

<createTable tableName="PED_VENT_CAB">
  <column name="ped_vent_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ped_vent_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ped_vent_plazo_entrega" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ped_vent_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ped_vent_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
</createTable>
<addPrimaryKey tableName="PED_VENT_CAB" constraintName="PED_VENT_CAB_pk" columnNames="ped_vent_cod"/>

<createTable tableName="PEDIDO_VENT_DET">
  <column name="ped_vent_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ped_vent_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="ped_vent_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PEDIDO_VENT_DET" constraintName="PEDIDO_VENT_DET_pk" columnNames="ped_vent_cod, item_id"/>

<createTable tableName="VENTA_CAB">
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="vent_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="vent_tipo_fact" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="vent_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="vent_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="vent_cuota" type="INTEGER"/>
  <column name="vent_ifv" type="TIMESTAMP"/>
  <column name="vent_obs" type="VARCHAR(500)"/>
  <column name="cli_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ped_vent_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="timb_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="VENTA_CAB" constraintName="VENTA_CAB_pk" columnNames="vent_nro_fact"/>

<createTable tableName="VENTA_DET">
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="vent_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="vent_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="VENTA_DET" constraintName="VENTA_DET_pk" columnNames="vent_nro_fact, item_id"/>

<!-- ORDEN_VENTA: relación entre VENTA_CAB y ORDEN_SERV_CAB -->
<createTable tableName="ORDEN_VENTA">
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ord_ser_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ord_vent_descri" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="ORDEN_VENTA" constraintName="ORDEN_VENTA_pk" columnNames="vent_nro_fact, ord_ser_id"/>

<!-- CTA_COBRAR: cuenta a cobrar por venta -->
<createTable tableName="CTA_COBRAR">
  <column name="cta_con_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cta_cob_cuota" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cta_cob_monto" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="cta_cob_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cta_cob_fecha_vencimiento" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cta_cob_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="CTA_COBRAR" constraintName="CTA_COBRAR_pk" columnNames="cta_con_nro"/>

<!-- LIB_VENTA: Libro de Ventas (registro contable).                      -->
<!-- Corrección 3FN: se elimina cli_cod                                    -->
<!-- (derivable vía vent_nro_fact → VENTA_CAB.cli_cod)                    -->
<createTable tableName="LIB_VENTA">
  <column name="lib_vent_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="lib_vent_iva10" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_vent_iva5" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_vent_exenta" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_vent_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="lib_vent_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="lib_vent_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="lib_vent_tipo_nota" type="VARCHAR(50)"/>
  <!-- cli_cod ELIMINADO: derivable vía vent_nro_fact → VENTA_CAB.cli_cod (3FN) -->
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="LIB_VENTA" constraintName="LIB_VENTA_pk" columnNames="lib_vent_nro"/>

<!-- NOTAS DE VENTA Y REMISIÓN -->
<createTable tableName="NOTA_VENT_CAB">
  <column name="nota_ven_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_ven_fecha" type="DATE"><constraints nullable="false"/></column>
  <column name="nota_ven_tipo" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_ven_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_ven_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="timb_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NOTA_VENT_CAB" constraintName="NOTA_VENT_CAB_pk" columnNames="nota_ven_nro"/>

<createTable tableName="NOTA_VENT_DET">
  <column name="nota_ven_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_ven_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="nota_ven_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NOTA_VENT_DET" constraintName="NOTA_VENT_DET_pk" columnNames="nota_ven_nro, item_id"/>

<createTable tableName="NOTA_REMI_VENTA_CAB">
  <column name="nota_remv_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <!-- cli_cod ELIMINADO: derivable vía vent_nro_fact → VENTA_CAB.cli_cod (3FN) -->
  <column name="nota_remiv_fecha" type="DATE"><constraints nullable="false"/></column>
  <column name="nota_remiv_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_remiv_obs" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="NOTA_REMI_VENTA_CAB" constraintName="NOTA_REMI_VENTA_CAB_pk" columnNames="nota_remv_nro"/>

<createTable tableName="NOTA_REMI_VENTA_DET">
  <column name="nota_remv_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_remiv_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="nota_remiv_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NOTA_REMI_VENTA_DET" constraintName="NOTA_REMI_VENTA_DET_pk" columnNames="nota_remv_nro, item_id"/>

<createTable tableName="RECLAMO_VENTA">
  <column name="vent_nro_fact" type="INTEGER"><constraints nullable="false"/></column>
  <column name="recla_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="recla_vent_descri" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="RECLAMO_VENTA" constraintName="RECLAMO_VENTA_pk" columnNames="vent_nro_fact, recla_id"/>

</changeSet>

<!-- ================================================================== -->
<!-- MÓDULO COBROS Y CAJA                                                 -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="5">

<!-- APERTURA_CIERRE: se elimina emp_id (derivable vía suc_id) -->
<createTable tableName="APERTURA_CIERRE">
  <column name="num_apertura" type="INTEGER"><constraints nullable="false"/></column>
  <column name="caja_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fecha_apertura" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="fecha_cierre" type="TIMESTAMP"/>
  <column name="aper_cierre_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="APERTURA_CIERRE" constraintName="APERTURA_CIERRE_pk" columnNames="num_apertura, caja_cod"/>

<!-- COBRO_CAB: se elimina emp_id (derivable vía suc_id) -->
<createTable tableName="COBRO_CAB">
  <column name="cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="num_apertura" type="INTEGER"><constraints nullable="false"/></column>
  <column name="caja_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="forma_cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cobro_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cobro_importe" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="cobro_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="cobro_obs" type="VARCHAR(500)"/>
  <column name="cta_con_nro" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COBRO_CAB" constraintName="COBRO_CAB_pk" columnNames="cobro_cod"/>

<createTable tableName="COBRO_DET">
  <column name="cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cobro_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="cobro_precio" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COBRO_DET" constraintName="COBRO_DET_pk" columnNames="cobro_cod, item_id"/>

<createTable tableName="COBRO_TARJETA">
  <column name="cobro_tarj_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="tarj_num" type="VARCHAR(20)"/>
  <column name="tarj_fecha_vence" type="DATE"/>
  <column name="tarj_monto" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="nro_voucher" type="VARCHAR(50)"/>
  <column name="marc_tarj_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="entidad_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COBRO_TARJETA" constraintName="COBRO_TARJETA_pk" columnNames="cobro_tarj_id"/>

<createTable tableName="COBRO_CHEQ">
  <column name="cobro_cheq_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cheq_nro" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="cheq_monto" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="cheq_fecha_vence" type="DATE"/>
  <column name="cheq_portador" type="VARCHAR(100)"/>
  <column name="entidad_cod" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COBRO_CHEQ" constraintName="COBRO_CHEQ_pk" columnNames="cobro_cheq_id"/>

<createTable tableName="COBRO_EFECTIVO">
  <column name="cobro_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="monto_efectivo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COBRO_EFECTIVO" constraintName="COBRO_EFECTIVO_pk" columnNames="cobro_cod"/>

<!-- ARQUEO_CTROL: se eliminan emp_id y suc_id                            -->
<!-- (derivables vía num_apertura, caja_cod → APERTURA_CIERRE → suc_id)  -->
<createTable tableName="ARQUEO_CTROL">
  <column name="arqueo_num" type="INTEGER"><constraints nullable="false"/></column>
  <column name="arqueo_fecha" type="DATE"><constraints nullable="false"/></column>
  <column name="arqueo_hora" type="TIME"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="num_apertura" type="INTEGER"><constraints nullable="false"/></column>
  <column name="caja_cod" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id, suc_id ELIMINADOS: derivables vía APERTURA_CIERRE (3FN) -->
</createTable>
<addPrimaryKey tableName="ARQUEO_CTROL" constraintName="ARQUEO_CTROL_pk" columnNames="arqueo_num"/>

<createTable tableName="RECAUDACIONES_DEPOSITAR">
  <column name="reca_dep_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="num_apertura" type="INTEGER"><constraints nullable="false"/></column>
  <column name="caja_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="reca_dep_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="reca_dep_obs" type="VARCHAR(500)"/>
  <column name="reca_dep_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="reca_dep_met_pago" type="VARCHAR(50)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="RECAUDACIONES_DEPOSITAR" constraintName="RECAUDACIONES_DEPOSITAR_pk" columnNames="reca_dep_cod"/>

</changeSet>

<!-- ================================================================== -->
<!-- MÓDULO COMPRAS                                                        -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="6">

<createTable tableName="PEDIDO_COMP_CAB">
  <column name="ped_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="ped_comp_cab_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ped_comp_cab_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ped_comp_cab_plazo_entrega" type="DATE"/>
  <column name="ped_comp_cab_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PEDIDO_COMP_CAB" constraintName="PEDIDO_COMP_CAB_pk" columnNames="ped_comp_cab_id"/>

<createTable tableName="PEDIDO_COMP_DET">
  <column name="ped_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ped_comp_det_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="ped_comp_det_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="PEDIDO_COMP_DET" constraintName="PEDIDO_COMP_DET_pk" columnNames="ped_comp_cab_id, item_id"/>

<createTable tableName="PRES_PROV_CAB">
  <column name="pres_prov_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_prov_cab_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="pres_prov_cab_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="pres_prov_cab_plazo_entrega" type="DATE"/>
  <column name="pres_prov_cab_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="ped_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PRES_PROV_CAB" constraintName="PRES_PROV_CAB_pk" columnNames="pres_prov_cab_id"/>

<createTable tableName="PRES_PROV_DET">
  <column name="pres_prov_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_prov_cant" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="pres_prov_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="PRES_PROV_DET" constraintName="PRES_PROV_DET_pk" columnNames="pres_prov_cab_id, item_id"/>

<createTable tableName="PRES_PREV_PED">
  <column name="pres_prov_ped_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ped_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_prov_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_prev_ped_fecha_registro" type="TIMESTAMP"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="PRES_PREV_PED" constraintName="PRES_PREV_PED_pk" columnNames="pres_prov_ped_id"/>

<createTable tableName="ORDEN_COMP_CAB">
  <column name="ord_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="pres_prov_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="ord_comp_cab_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ord_comp_cab_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ord_comp_cab_ifv" type="TIMESTAMP"/>
  <column name="ord_comp_cab_canti_cuota" type="INTEGER"/>
  <column name="ord_comp_cab_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ord_comp_cab_obs" type="VARCHAR(500)"/>
</createTable>
<addPrimaryKey tableName="ORDEN_COMP_CAB" constraintName="ORDEN_COMP_CAB_pk" columnNames="ord_comp_cab_id"/>

<createTable tableName="ORDEN_COMP_DET">
  <column name="ord_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ord_comp_det_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="ord_comp_det_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="ORDEN_COMP_DET" constraintName="ORDEN_COMP_DET_pk" columnNames="ord_comp_cab_id, item_id"/>

<createTable tableName="COMPRA_CAB">
  <column name="comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="comp_cab_nro_fact" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="comp_cab_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="comp_cab_tipo_fact" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="comp_cab_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="comp_cab_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="comp_cab_ifv" type="DATE"/>
  <column name="comp_cab_cuota" type="INTEGER"/>
  <column name="comp_cab_timbrado" type="VARCHAR(20)"/>
  <column name="comp_cab_obs" type="VARCHAR(500)"/>
  <column name="ord_comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="COMPRA_CAB" constraintName="COMPRA_CAB_pk" columnNames="comp_cab_id"/>

<createTable tableName="COMPRA_DET">
  <column name="comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="comp_det_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="comp_det_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="COMPRA_DET" constraintName="COMPRA_DET_pk" columnNames="comp_cab_id, item_id"/>

<createTable tableName="CTA_PAGAR">
  <column name="cta_pag_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cta_pag_cuota" type="INTEGER"><constraints nullable="false"/></column>
  <column name="cta_pag_monto" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="cta_pag_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cta_pag_fecha_vencimiento" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="cta_pag_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="CTA_PAGAR" constraintName="CTA_PAGAR_pk" columnNames="cta_pag_id"/>

<!-- LIBRO_COMPRA: se elimina prov_id (derivable vía comp_cab_id → COMPRA_CAB.prov_id) -->
<createTable tableName="LIBRO_COMPRA">
  <column name="lib_comp_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="lib_comp_iva10" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_comp_iva5" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_comp_exenta" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="lib_comp_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="lib_comp_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="lib_comp_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="lib_comp_tipo_nota" type="VARCHAR(50)"/>
  <!-- prov_id ELIMINADO: derivable vía comp_cab_id → COMPRA_CAB.prov_id (3FN) -->
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="LIBRO_COMPRA" constraintName="LIBRO_COMPRA_pk" columnNames="lib_comp_nro"/>

<createTable tableName="NOTA_COMP_CAB">
  <column name="nota_comp_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_comp_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="nota_comp_tipo" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_comp_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_comp_condicion_pago" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="nota_comp_ifv" type="TIMESTAMP"/>
  <column name="nota_comp_cuota" type="INTEGER"/>
  <column name="nota_comp_obs" type="VARCHAR(500)"/>
  <column name="timb_cod" type="INTEGER"><constraints nullable="false"/></column>
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="comp_cab_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NOTA_COMP_CAB" constraintName="NOTA_COMP_CAB_pk" columnNames="nota_comp_nro"/>

<createTable tableName="NOTA_COMP_DET">
  <column name="nota_comp_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_comp_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="nota_comp_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="NOTA_COMP_DET" constraintName="NOTA_COMP_DET_pk" columnNames="nota_comp_nro, item_id"/>

<createTable tableName="NOTA_REMIC_CAB">
  <column name="not_remic_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="not_remic_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="not_remic_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="not_remi_obs" type="VARCHAR(500)"/>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="prov_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="NOTA_REMIC_CAB" constraintName="NOTA_REMIC_CAB_pk" columnNames="not_remic_nro"/>

<createTable tableName="NOTA_REMIC_DET">
  <column name="not_remic_nro" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="nota_remic_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="nota_remic_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="NOTA_REMIC_DET" constraintName="NOTA_REMIC_DET_pk" columnNames="not_remic_nro, item_id"/>

<createTable tableName="AJUSTE_COMP_CAB">
  <column name="ajus_com_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="fun_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="suc_id" type="INTEGER"><constraints nullable="false"/></column>
  <!-- emp_id ELIMINADO: derivable vía suc_id → SUCURSAL.emp_id (3FN) -->
  <column name="ajus_com_estado" type="VARCHAR(20)"><constraints nullable="false"/></column>
  <column name="ajus_com_fecha" type="TIMESTAMP"><constraints nullable="false"/></column>
  <column name="ajus_comp_obs" type="VARCHAR(500)"/>
  <column name="mot_ajus_id" type="INTEGER"><constraints nullable="false"/></column>
</createTable>
<addPrimaryKey tableName="AJUSTE_COMP_CAB" constraintName="AJUSTE_COMP_CAB_pk" columnNames="ajus_com_id"/>

<createTable tableName="AJUSTE_COMP_DET">
  <column name="ajus_comp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="item_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="ajus_comp_cantidad" type="NUMERIC(10,2)"><constraints nullable="false"/></column>
  <column name="ajus_comp_costo" type="NUMERIC(18,2)"><constraints nullable="false"/></column>
  <column name="tipo_imp_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="deposito_id" type="INTEGER"><constraints nullable="false"/></column>
  <column name="marca_id" type="INTEGER"/>
  <column name="modelo_id" type="INTEGER"/>
</createTable>
<addPrimaryKey tableName="AJUSTE_COMP_DET" constraintName="AJUSTE_COMP_DET_pk" columnNames="ajus_comp_id, item_id"/>

</changeSet>

<!-- ================================================================== -->
<!-- FOREIGN KEYS                                                          -->
<!-- ================================================================== -->

<changeSet author="CHANGEME" id="7">

<!-- Catálogos y seguridad -->
<addForeignKeyConstraint baseTableName="ACCESOS" constraintName="PERMISOS_ACCESOS_fk" baseColumnNames="per_id" referencedTableName="PERMISOS" referencedColumnNames="per_id"/>
<addForeignKeyConstraint baseTableName="ACCESOS" constraintName="MODULOS_ACCESOS_fk" baseColumnNames="mod_id" referencedTableName="MODULOS" referencedColumnNames="mod_id"/>
<addForeignKeyConstraint baseTableName="ACCESOS" constraintName="ROLES_ACCESOS_fk" baseColumnNames="rol_id" referencedTableName="ROLES" referencedColumnNames="rol_id"/>
<addForeignKeyConstraint baseTableName="USUARIOS" constraintName="ROLES_USUARIOS_fk" baseColumnNames="rol_id" referencedTableName="ROLES" referencedColumnNames="rol_id"/>
<addForeignKeyConstraint baseTableName="USUARIOS" constraintName="FUNCIONARIO_USUARIOS_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="AUDITORIA" constraintName="USUARIOS_AUDITORIA_fk" baseColumnNames="user_id" referencedTableName="USUARIOS" referencedColumnNames="user_id"/>

<!-- Geografía -->
<addForeignKeyConstraint baseTableName="CIUDAD" constraintName="PAIS_CIUDAD_fk" baseColumnNames="pais_id" referencedTableName="PAIS" referencedColumnNames="pais_id"/>
<addForeignKeyConstraint baseTableName="NACIONALIDAD" constraintName="PAIS_NACIONALIDAD_fk" baseColumnNames="pais_id" referencedTableName="PAIS" referencedColumnNames="pais_id"/>
<!-- FK corregida: solo ciu_id (pais_id era transitivo) -->
<addForeignKeyConstraint baseTableName="PROVEEDOR" constraintName="CIUDAD_PROVEEDOR_fk" baseColumnNames="ciu_id" referencedTableName="CIUDAD" referencedColumnNames="ciu_id"/>
<addForeignKeyConstraint baseTableName="FUNCIONARIO" constraintName="CIUDAD_FUNCIONARIO_fk" baseColumnNames="ciu_id" referencedTableName="CIUDAD" referencedColumnNames="ciu_id"/>
<addForeignKeyConstraint baseTableName="PROVEEDOR" constraintName="NACIONALIDAD_PROVEEDOR_fk" baseColumnNames="nacionalidad_id" referencedTableName="NACIONALIDAD" referencedColumnNames="nacionalidad_id"/>
<addForeignKeyConstraint baseTableName="FUNCIONARIO" constraintName="NACIONALIDAD_FUNCIONARIO_fk" baseColumnNames="nacionalidad_id" referencedTableName="NACIONALIDAD" referencedColumnNames="nacionalidad_id"/>

<!-- Empresa / Sucursal -->
<addForeignKeyConstraint baseTableName="SUCURSAL" constraintName="EMPRESA_SUCURSAL_fk" baseColumnNames="emp_id" referencedTableName="EMPRESA" referencedColumnNames="emp_id"/>
<addForeignKeyConstraint baseTableName="DEPOSITO" constraintName="SUCURSAL_DEPOSITO_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="TIMBRADO" constraintName="SUCURSAL_TIMBRADO_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="TIMBRADO" constraintName="TIPO_COMPROBANTE_TIMBRADO_fk" baseColumnNames="tip_comp_cod" referencedTableName="TIPO_COMPROBANTE" referencedColumnNames="tip_comp_cod"/>

<!-- Vehículo / Marca / Modelo -->
<addForeignKeyConstraint baseTableName="TIPO_VEHICULO" constraintName="MARCA_TIPO_VEHICULO_fk" baseColumnNames="marca_id" referencedTableName="MARCA" referencedColumnNames="marca_id"/>
<addForeignKeyConstraint baseTableName="TIPO_VEHICULO" constraintName="MODELO_TIPO_VEHICULO_fk" baseColumnNames="modelo_id" referencedTableName="MODELO" referencedColumnNames="modelo_id"/>
<addForeignKeyConstraint baseTableName="ITEM_MARCA" constraintName="MARCA_ITEM_MARCA_fk" baseColumnNames="marca_id" referencedTableName="MARCA" referencedColumnNames="marca_id"/>
<addForeignKeyConstraint baseTableName="ITEM_MARCA" constraintName="ITEMS_ITEM_MARCA_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="ITEM_MODELO" constraintName="MODELO_ITEM_MODELO_fk" baseColumnNames="modelo_id" referencedTableName="MODELO" referencedColumnNames="modelo_id"/>
<addForeignKeyConstraint baseTableName="ITEM_MODELO" constraintName="ITEMS_ITEM_MODELO_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>

<!-- Entidades de tarjetas -->
<addForeignKeyConstraint baseTableName="ENTIDAD_ADHERIDA" constraintName="ENTIDAD_EMISORA_ADHERIDA_fk" baseColumnNames="entidad_cod" referencedTableName="ENTIDAD_EMISORA" referencedColumnNames="entidad_cod"/>
<addForeignKeyConstraint baseTableName="ENTIDAD_ADHERIDA" constraintName="MARCA_TARJETA_ADHERIDA_fk" baseColumnNames="marc_tarj_cod" referencedTableName="MARCA_TARJETA" referencedColumnNames="marc_tarj_cod"/>

<!-- Items y Stock -->
<addForeignKeyConstraint baseTableName="ITEMS" constraintName="TIPO_ITEMS_ITEMS_fk" baseColumnNames="tipo_item_id" referencedTableName="TIPO_ITEMS" referencedColumnNames="tipo_item_id"/>
<addForeignKeyConstraint baseTableName="ITEMS" constraintName="TIPO_IMPUESTO_ITEMS_fk" baseColumnNames="tipo_imp_id" referencedTableName="TIPO_IMPUESTO" referencedColumnNames="tipo_imp_id"/>
<addForeignKeyConstraint baseTableName="STOCK" constraintName="ITEMS_STOCK_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="STOCK" constraintName="DEPOSITO_STOCK_fk" baseColumnNames="deposito_id" referencedTableName="DEPOSITO" referencedColumnNames="deposito_id"/>

<!-- Módulo Servicio -->
<addForeignKeyConstraint baseTableName="SOLICITUD_CAB" constraintName="TIPO_SERVICIO_SOLICITUD_CAB_fk" baseColumnNames="tip_serv_id" referencedTableName="TIPO_SERVICIO" referencedColumnNames="tip_serv_id"/>
<addForeignKeyConstraint baseTableName="SOLICITUD_CAB" constraintName="CLIENTE_SOLICITUD_CAB_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="SOLICITUD_CAB" constraintName="FUNCIONARIO_SOLICITUD_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="SOLICITUD_CAB" constraintName="SUCURSAL_SOLICITUD_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="SOLICITUD_DET" constraintName="SOLICITUD_CAB_SOLICITUD_DET_fk" baseColumnNames="soli_id" referencedTableName="SOLICITUD_CAB" referencedColumnNames="soli_id"/>
<addForeignKeyConstraint baseTableName="SOLICITUD_DET" constraintName="ITEMS_SOLICITUD_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="RECEP_CAB" constraintName="SOLICITUD_CAB_RECEP_CAB_fk" baseColumnNames="soli_id" referencedTableName="SOLICITUD_CAB" referencedColumnNames="soli_id"/>
<addForeignKeyConstraint baseTableName="RECEP_CAB" constraintName="TIPO_VEHICULO_RECEP_CAB_fk" baseColumnNames="tip_veh_id" referencedTableName="TIPO_VEHICULO" referencedColumnNames="tip_veh_id"/>
<addForeignKeyConstraint baseTableName="RECEP_CAB" constraintName="FUNCIONARIO_RECEP_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="RECEP_CAB" constraintName="SUCURSAL_RECEP_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="RECP_DET" constraintName="RECEP_CAB_RECP_DET_fk" baseColumnNames="recep_id" referencedTableName="RECEP_CAB" referencedColumnNames="recep_id"/>
<addForeignKeyConstraint baseTableName="RECP_DET" constraintName="ITEMS_RECP_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_CAB" constraintName="RECEP_CAB_DIAGNOSTICO_CAB_fk" baseColumnNames="recep_id" referencedTableName="RECEP_CAB" referencedColumnNames="recep_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_CAB" constraintName="TIPO_DIAGNOSTICO_DIAGNOSTICO_CAB_fk" baseColumnNames="tip_diag_id" referencedTableName="TIPO_DIAGNOSTICO" referencedColumnNames="tip_diag_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_CAB" constraintName="FUNCIONARIO_DIAGNOSTICO_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_CAB" constraintName="SUCURSAL_DIAGNOSTICO_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_DET" constraintName="DIAGNOSTICO_CAB_DIAGNOSTICO_DET_fk" baseColumnNames="diag_id" referencedTableName="DIAGNOSTICO_CAB" referencedColumnNames="diag_id"/>
<addForeignKeyConstraint baseTableName="DIAGNOSTICO_DET" constraintName="ITEMS_DIAGNOSTICO_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="DIAGNOSTICO_CAB_PRES_SER_CAB_fk" baseColumnNames="diag_id" referencedTableName="DIAGNOSTICO_CAB" referencedColumnNames="diag_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="CLIENTE_PRES_SER_CAB_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="FUNCIONARIO_PRES_SER_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="SUCURSAL_PRES_SER_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="DESC_CAB_PRES_SER_CAB_fk" baseColumnNames="desc_id" referencedTableName="DESC_CAB" referencedColumnNames="desc_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_CAB" constraintName="PROMOCION_CAB_PRES_SER_CAB_fk" baseColumnNames="prom_id" referencedTableName="PROMOCION_CAB" referencedColumnNames="prom_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_DET" constraintName="PRES_SER_CAB_PRES_SER_DET_fk" baseColumnNames="pres_serv_id" referencedTableName="PRES_SER_CAB" referencedColumnNames="pres_serv_id"/>
<addForeignKeyConstraint baseTableName="PRES_SER_DET" constraintName="ITEMS_PRES_SER_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_CAB" constraintName="PRES_SER_CAB_ORDEN_SERV_CAB_fk" baseColumnNames="pres_serv_id" referencedTableName="PRES_SER_CAB" referencedColumnNames="pres_serv_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_CAB" constraintName="EQUIPO_TRABAJO_ORDEN_SERV_CAB_fk" baseColumnNames="equi_cod" referencedTableName="EQUIPO_TRABAJO" referencedColumnNames="equi_cod"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_CAB" constraintName="FUNCIONARIO_ORDEN_SERV_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_CAB" constraintName="SUCURSAL_ORDEN_SERV_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_DET" constraintName="ORDEN_SERV_CAB_ORDEN_SERV_DET_fk" baseColumnNames="ord_ser_id" referencedTableName="ORDEN_SERV_CAB" referencedColumnNames="ord_ser_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_SERV_DET" constraintName="ITEMS_ORDEN_SERV_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="INSUM_UTILI" constraintName="ORDEN_SERV_CAB_INSUM_UTILI_fk" baseColumnNames="ord_ser_id" referencedTableName="ORDEN_SERV_CAB" referencedColumnNames="ord_ser_id"/>
<addForeignKeyConstraint baseTableName="INSUM_UTILI" constraintName="ITEMS_INSUM_UTILI_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="CONTRATO_CAB" constraintName="TIPO_CONTRATO_CONTRATO_CAB_fk" baseColumnNames="tipo_con_id" referencedTableName="TIPO_CONTRATO" referencedColumnNames="tipo_con_id"/>
<addForeignKeyConstraint baseTableName="CONTRATO_CAB" constraintName="CLIENTE_CONTRATO_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="CONTRATO_CAB" constraintName="FUNCIONARIO_CONTRATO_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="CONTRATO_CAB" constraintName="SUCURSAL_CONTRATO_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="CONTRATO_DET" constraintName="CONTRATO_CAB_CONTRATO_DET_fk" baseColumnNames="cont_id" referencedTableName="CONTRATO_CAB" referencedColumnNames="cont_id"/>
<addForeignKeyConstraint baseTableName="CONTRATO_DET" constraintName="ITEMS_CONTRATO_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="RECLAMO_CAB" constraintName="CLIENTE_RECLAMO_CAB_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="RECLAMO_CAB" constraintName="FUNCIONARIO_RECLAMO_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="RECLAMO_CAB" constraintName="SUCURSAL_RECLAMO_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="RECLAMO_DET" constraintName="RECLAMO_CAB_RECLAMO_DET_fk" baseColumnNames="recla_id" referencedTableName="RECLAMO_CAB" referencedColumnNames="recla_id"/>
<addForeignKeyConstraint baseTableName="RECLAMO_DET" constraintName="ITEMS_RECLAMO_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="DESC_CAB" constraintName="TIPO_DESCUENTOS_DESC_CAB_fk" baseColumnNames="tip_desc_id" referencedTableName="TIPO_DESCUENTOS" referencedColumnNames="tip_desc_id"/>
<addForeignKeyConstraint baseTableName="DESC_CAB" constraintName="FUNCIONARIO_DESC_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="DESC_CAB" constraintName="SUCURSAL_DESC_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="DESC_DET" constraintName="DESC_CAB_DESC_DET_fk" baseColumnNames="desc_id" referencedTableName="DESC_CAB" referencedColumnNames="desc_id"/>
<addForeignKeyConstraint baseTableName="DESC_DET" constraintName="ITEMS_DESC_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="PROMOCION_CAB" constraintName="TIPO_PROMOCIONES_PROMOCION_CAB_fk" baseColumnNames="tip_prom_id" referencedTableName="TIPO_PROMOCIONES" referencedColumnNames="tip_prom_id"/>
<addForeignKeyConstraint baseTableName="PROMOCION_CAB" constraintName="FUNCIONARIO_PROMOCION_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="PROMOCION_CAB" constraintName="SUCURSAL_PROMOCION_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="PROMOCION_DET" constraintName="PROMOCION_CAB_PROMOCION_DET_fk" baseColumnNames="prom_id" referencedTableName="PROMOCION_CAB" referencedColumnNames="prom_id"/>
<addForeignKeyConstraint baseTableName="PROMOCION_DET" constraintName="ITEMS_PROMOCION_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>

<!-- Módulo Ventas -->
<addForeignKeyConstraint baseTableName="PED_VENT_CAB" constraintName="CLIENTE_PED_VENT_CAB_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="PED_VENT_CAB" constraintName="FUNCIONARIO_PED_VENT_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="PED_VENT_CAB" constraintName="SUCURSAL_PED_VENT_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="PEDIDO_VENT_DET" constraintName="PED_VENT_CAB_PEDIDO_VENT_DET_fk" baseColumnNames="ped_vent_cod" referencedTableName="PED_VENT_CAB" referencedColumnNames="ped_vent_cod"/>
<addForeignKeyConstraint baseTableName="PEDIDO_VENT_DET" constraintName="ITEMS_PEDIDO_VENT_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="VENTA_CAB" constraintName="CLIENTE_VENTA_CAB_fk" baseColumnNames="cli_cod" referencedTableName="CLIENTE" referencedColumnNames="cli_cod"/>
<addForeignKeyConstraint baseTableName="VENTA_CAB" constraintName="PED_VENT_CAB_VENTA_CAB_fk" baseColumnNames="ped_vent_cod" referencedTableName="PED_VENT_CAB" referencedColumnNames="ped_vent_cod"/>
<addForeignKeyConstraint baseTableName="VENTA_CAB" constraintName="FUNCIONARIO_VENTA_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="VENTA_CAB" constraintName="SUCURSAL_VENTA_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="VENTA_CAB" constraintName="TIMBRADO_VENTA_CAB_fk" baseColumnNames="timb_cod" referencedTableName="TIMBRADO" referencedColumnNames="timb_cod"/>
<addForeignKeyConstraint baseTableName="VENTA_DET" constraintName="VENTA_CAB_VENTA_DET_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="VENTA_DET" constraintName="ITEMS_VENTA_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="CTA_COBRAR" constraintName="VENTA_CAB_CTA_COBRAR_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="LIB_VENTA" constraintName="VENTA_CAB_LIB_VENTA_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="LIB_VENTA" constraintName="TIPO_IMPUESTO_LIB_VENTA_fk" baseColumnNames="tipo_imp_id" referencedTableName="TIPO_IMPUESTO" referencedColumnNames="tipo_imp_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_VENTA" constraintName="VENTA_CAB_ORDEN_VENTA_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="ORDEN_VENTA" constraintName="ORDEN_SERV_CAB_ORDEN_VENTA_fk" baseColumnNames="ord_ser_id" referencedTableName="ORDEN_SERV_CAB" referencedColumnNames="ord_ser_id"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_CAB" constraintName="VENTA_CAB_NOTA_VENT_CAB_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_CAB" constraintName="FUNCIONARIO_NOTA_VENT_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_CAB" constraintName="SUCURSAL_NOTA_VENT_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_CAB" constraintName="TIMBRADO_NOTA_VENT_CAB_fk" baseColumnNames="timb_cod" referencedTableName="TIMBRADO" referencedColumnNames="timb_cod"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_DET" constraintName="NOTA_VENT_CAB_NOTA_VENT_DET_fk" baseColumnNames="nota_ven_nro" referencedTableName="NOTA_VENT_CAB" referencedColumnNames="nota_ven_nro"/>
<addForeignKeyConstraint baseTableName="NOTA_VENT_DET" constraintName="ITEMS_NOTA_VENT_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="NOTA_REMI_VENTA_CAB" constraintName="VENTA_CAB_NOTA_REMI_VENTA_CAB_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="NOTA_REMI_VENTA_DET" constraintName="NOTA_REMI_VENTA_CAB_DET_fk" baseColumnNames="nota_remv_nro" referencedTableName="NOTA_REMI_VENTA_CAB" referencedColumnNames="nota_remv_nro"/>
<addForeignKeyConstraint baseTableName="NOTA_REMI_VENTA_DET" constraintName="ITEMS_NOTA_REMI_VENTA_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="RECLAMO_VENTA" constraintName="VENTA_CAB_RECLAMO_VENTA_fk" baseColumnNames="vent_nro_fact" referencedTableName="VENTA_CAB" referencedColumnNames="vent_nro_fact"/>
<addForeignKeyConstraint baseTableName="RECLAMO_VENTA" constraintName="RECLAMO_CAB_RECLAMO_VENTA_fk" baseColumnNames="recla_id" referencedTableName="RECLAMO_CAB" referencedColumnNames="recla_id"/>

<!-- Módulo Cobros y Caja -->
<addForeignKeyConstraint baseTableName="APERTURA_CIERRE" constraintName="CAJA_APERTURA_CIERRE_fk" baseColumnNames="caja_cod" referencedTableName="CAJA" referencedColumnNames="caja_cod"/>
<addForeignKeyConstraint baseTableName="APERTURA_CIERRE" constraintName="SUCURSAL_APERTURA_CIERRE_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="APERTURA_CIERRE" constraintName="FUNCIONARIO_APERTURA_CIERRE_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="COBRO_CAB" constraintName="APERTURA_CIERRE_COBRO_CAB_fk" baseColumnNames="num_apertura, caja_cod" referencedTableName="APERTURA_CIERRE" referencedColumnNames="num_apertura, caja_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_CAB" constraintName="FORMA_COBRO_COBRO_CAB_fk" baseColumnNames="forma_cobro_cod" referencedTableName="FORMA_COBRO" referencedColumnNames="forma_cobro_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_CAB" constraintName="CTA_COBRAR_COBRO_CAB_fk" baseColumnNames="cta_con_nro" referencedTableName="CTA_COBRAR" referencedColumnNames="cta_con_nro"/>
<addForeignKeyConstraint baseTableName="COBRO_CAB" constraintName="FUNCIONARIO_COBRO_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="COBRO_CAB" constraintName="SUCURSAL_COBRO_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="COBRO_DET" constraintName="COBRO_CAB_COBRO_DET_fk" baseColumnNames="cobro_cod" referencedTableName="COBRO_CAB" referencedColumnNames="cobro_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_DET" constraintName="ITEMS_COBRO_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="COBRO_TARJETA" constraintName="COBRO_CAB_COBRO_TARJETA_fk" baseColumnNames="cobro_cod" referencedTableName="COBRO_CAB" referencedColumnNames="cobro_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_TARJETA" constraintName="ENTIDAD_ADHERIDA_COBRO_TARJETA_fk" baseColumnNames="marc_tarj_cod, entidad_cod" referencedTableName="ENTIDAD_ADHERIDA" referencedColumnNames="marc_tarj_cod, entidad_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_CHEQ" constraintName="COBRO_CAB_COBRO_CHEQ_fk" baseColumnNames="cobro_cod" referencedTableName="COBRO_CAB" referencedColumnNames="cobro_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_CHEQ" constraintName="ENTIDAD_EMISORA_COBRO_CHEQ_fk" baseColumnNames="entidad_cod" referencedTableName="ENTIDAD_EMISORA" referencedColumnNames="entidad_cod"/>
<addForeignKeyConstraint baseTableName="COBRO_EFECTIVO" constraintName="COBRO_CAB_COBRO_EFECTIVO_fk" baseColumnNames="cobro_cod" referencedTableName="COBRO_CAB" referencedColumnNames="cobro_cod"/>
<addForeignKeyConstraint baseTableName="ARQUEO_CTROL" constraintName="APERTURA_CIERRE_ARQUEO_CTROL_fk" baseColumnNames="num_apertura, caja_cod" referencedTableName="APERTURA_CIERRE" referencedColumnNames="num_apertura, caja_cod"/>
<addForeignKeyConstraint baseTableName="ARQUEO_CTROL" constraintName="FUNCIONARIO_ARQUEO_CTROL_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="RECAUDACIONES_DEPOSITAR" constraintName="APERTURA_CIERRE_RECAUD_fk" baseColumnNames="num_apertura, caja_cod" referencedTableName="APERTURA_CIERRE" referencedColumnNames="num_apertura, caja_cod"/>

<!-- Módulo Compras -->
<addForeignKeyConstraint baseTableName="PEDIDO_COMP_CAB" constraintName="SUCURSAL_PEDIDO_COMP_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="PEDIDO_COMP_CAB" constraintName="FUNCIONARIO_PEDIDO_COMP_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="PEDIDO_COMP_DET" constraintName="PEDIDO_COMP_CAB_PEDIDO_COMP_DET_fk" baseColumnNames="ped_comp_cab_id" referencedTableName="PEDIDO_COMP_CAB" referencedColumnNames="ped_comp_cab_id"/>
<addForeignKeyConstraint baseTableName="PEDIDO_COMP_DET" constraintName="ITEMS_PEDIDO_COMP_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_CAB" constraintName="PEDIDO_COMP_CAB_PRES_PROV_CAB_fk" baseColumnNames="ped_comp_cab_id" referencedTableName="PEDIDO_COMP_CAB" referencedColumnNames="ped_comp_cab_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_CAB" constraintName="PROVEEDOR_PRES_PROV_CAB_fk" baseColumnNames="prov_id" referencedTableName="PROVEEDOR" referencedColumnNames="prov_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_CAB" constraintName="SUCURSAL_PRES_PROV_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_CAB" constraintName="FUNCIONARIO_PRES_PROV_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_DET" constraintName="PRES_PROV_CAB_PRE_PROV_DET_fk" baseColumnNames="pres_prov_cab_id" referencedTableName="PRES_PROV_CAB" referencedColumnNames="pres_prov_cab_id"/>
<addForeignKeyConstraint baseTableName="PRES_PROV_DET" constraintName="ITEMS_PRE_PROV_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="PRES_PREV_PED" constraintName="PEDIDO_COMP_CAB_PRES_PREV_PED_fk" baseColumnNames="ped_comp_cab_id" referencedTableName="PEDIDO_COMP_CAB" referencedColumnNames="ped_comp_cab_id"/>
<addForeignKeyConstraint baseTableName="PRES_PREV_PED" constraintName="PRES_PROV_CAB_PRES_PREV_PED_fk" baseColumnNames="pres_prov_cab_id" referencedTableName="PRES_PROV_CAB" referencedColumnNames="pres_prov_cab_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_COMP_CAB" constraintName="PRES_PROV_CAB_ORDEN_COMP_CAB_fk" baseColumnNames="pres_prov_cab_id" referencedTableName="PRES_PROV_CAB" referencedColumnNames="pres_prov_cab_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_COMP_CAB" constraintName="PROVEEDOR_ORDEN_COMP_CAB_fk" baseColumnNames="prov_id" referencedTableName="PROVEEDOR" referencedColumnNames="prov_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_COMP_CAB" constraintName="SUCURSAL_ORDEN_COMP_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_COMP_DET" constraintName="ORDEN_COMP_CAB_ORDEN_COM_DET_fk" baseColumnNames="ord_comp_cab_id" referencedTableName="ORDEN_COMP_CAB" referencedColumnNames="ord_comp_cab_id"/>
<addForeignKeyConstraint baseTableName="ORDEN_COMP_DET" constraintName="ITEMS_ORDEN_COM_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_CAB" constraintName="ORDEN_COMP_CAB_COMPRA_CAB_fk" baseColumnNames="ord_comp_cab_id" referencedTableName="ORDEN_COMP_CAB" referencedColumnNames="ord_comp_cab_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_CAB" constraintName="PROVEEDOR_COMPRA_CAB_fk" baseColumnNames="prov_id" referencedTableName="PROVEEDOR" referencedColumnNames="prov_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_CAB" constraintName="FUNCIONARIO_COMPRA_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_CAB" constraintName="SUCURSAL_COMPRA_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_DET" constraintName="COMPRA_CAB_COMPRA_DET_fk" baseColumnNames="comp_cab_id" referencedTableName="COMPRA_CAB" referencedColumnNames="comp_cab_id"/>
<addForeignKeyConstraint baseTableName="COMPRA_DET" constraintName="ITEMS_COMPRA_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="CTA_PAGAR" constraintName="COMPRA_CAB_CTA_PAGAR_fk" baseColumnNames="comp_cab_id" referencedTableName="COMPRA_CAB" referencedColumnNames="comp_cab_id"/>
<addForeignKeyConstraint baseTableName="LIBRO_COMPRA" constraintName="COMPRA_CAB_LIBRO_COMPRA_fk" baseColumnNames="comp_cab_id" referencedTableName="COMPRA_CAB" referencedColumnNames="comp_cab_id"/>
<addForeignKeyConstraint baseTableName="LIBRO_COMPRA" constraintName="TIPO_IMPUESTO_LIBRO_COMPRA_fk" baseColumnNames="tipo_imp_id" referencedTableName="TIPO_IMPUESTO" referencedColumnNames="tipo_imp_id"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_CAB" constraintName="COMPRA_CAB_NOTA_COMP_CAB_fk" baseColumnNames="comp_cab_id" referencedTableName="COMPRA_CAB" referencedColumnNames="comp_cab_id"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_CAB" constraintName="PROVEEDOR_NOTA_COMP_CAB_fk" baseColumnNames="prov_id" referencedTableName="PROVEEDOR" referencedColumnNames="prov_id"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_CAB" constraintName="FUNCIONARIO_NOTA_COMP_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_CAB" constraintName="SUCURSAL_NOTA_COMP_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_CAB" constraintName="TIMBRADO_NOTA_COMP_CAB_fk" baseColumnNames="timb_cod" referencedTableName="TIMBRADO" referencedColumnNames="timb_cod"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_DET" constraintName="NOTA_COMP_CAB_NOTA_COMP_DET_fk" baseColumnNames="nota_comp_nro" referencedTableName="NOTA_COMP_CAB" referencedColumnNames="nota_comp_nro"/>
<addForeignKeyConstraint baseTableName="NOTA_COMP_DET" constraintName="ITEMS_NOTA_COMP_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="NOTA_REMIC_CAB" constraintName="PROVEEDOR_NOTA_REMIC_CAB_fk" baseColumnNames="prov_id" referencedTableName="PROVEEDOR" referencedColumnNames="prov_id"/>
<addForeignKeyConstraint baseTableName="NOTA_REMIC_CAB" constraintName="FUNCIONARIO_NOTA_REMIC_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="NOTA_REMIC_CAB" constraintName="SUCURSAL_NOTA_REMIC_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="NOTA_REMIC_DET" constraintName="NOTA_REMIC_CAB_NOTA_REMIC_DET_fk" baseColumnNames="not_remic_nro" referencedTableName="NOTA_REMIC_CAB" referencedColumnNames="not_remic_nro"/>
<addForeignKeyConstraint baseTableName="NOTA_REMIC_DET" constraintName="ITEMS_NOTA_REMIC_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>
<addForeignKeyConstraint baseTableName="AJUSTE_COMP_CAB" constraintName="MOTIVO_AJUSTE_AJUSTE_COMP_CAB_fk" baseColumnNames="mot_ajus_id" referencedTableName="MOTIVO_AJUSTE" referencedColumnNames="mot_ajus_id"/>
<addForeignKeyConstraint baseTableName="AJUSTE_COMP_CAB" constraintName="FUNCIONARIO_AJUSTE_COMP_CAB_fk" baseColumnNames="fun_id" referencedTableName="FUNCIONARIO" referencedColumnNames="fun_id"/>
<addForeignKeyConstraint baseTableName="AJUSTE_COMP_CAB" constraintName="SUCURSAL_AJUSTE_COMP_CAB_fk" baseColumnNames="suc_id" referencedTableName="SUCURSAL" referencedColumnNames="suc_id"/>
<addForeignKeyConstraint baseTableName="AJUSTE_COMP_DET" constraintName="AJUSTE_COMP_CAB_AJUSTE_COMP_DET_fk" baseColumnNames="ajus_comp_id" referencedTableName="AJUSTE_COMP_CAB" referencedColumnNames="ajus_com_id"/>
<addForeignKeyConstraint baseTableName="AJUSTE_COMP_DET" constraintName="ITEMS_AJUSTE_COMP_DET_fk" baseColumnNames="item_id" referencedTableName="ITEMS" referencedColumnNames="item_id"/>

</changeSet>
