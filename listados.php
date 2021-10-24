<? include("header.php"); ?><br />
<?
if ($_SESSION["NumeroLiquidacion"] != ''){
	print "<b>N&uacute;mero de Liquidaci&oacute;n Activa:" . $_SESSION["NumeroLiquidacion"];
	print "</b><br>Todos los listados seleccionar&aacute;n por defecto esta liquidaci&oacute;n";
	print "<br>Si lo desea puede cambiarla desde el men&uacute; per&iacute;odos<br>";
}
?>
<table border="0" cellspacing="20" cellpadding="1">
  <tr>
    <td><a href=listadoBanco.php>Listado Banco</a></td>
  </tr>
  <tr>
    <td><a href=listadoIPS.php>Listado IPS</a></td>
  </tr>
  <tr>
    <td><a href=listadoRafam.php>Listado Rafam</a></td>
  </tr>
  <tr>
    <td><a href=listadoRAFAMGastosPorCargo.php>Listado Rafam de Recursos humanos por categoria programatica y cargo</a></td>
  </tr>
  <tr>
    <td><a href=listadoEstConcLiq.php>Listado Estadistica de Conceptos Liquidados</a></td>
  </tr>
  <tr>
    <td><a href=listadoRetenciones.php>Listado Retenciones</a></td>
  </tr>
  <tr>
    <td><a href=listadoART.php>Listado ART</a></td>
  </tr>
  <tr>
    <td><a href=listadoLiquiAte.php>Listado de Liquidaciones de A.T.E.</a></td>
  </tr>
  <!--tr>
    <td><a href=listadoCNAS.php>Listado C.N.A.S.</a></td>
  </tr-->
  <tr>
    <td><a href=listadoCNAS.php>Listado MAPFRE</a></td>
  </tr>
  <tr>
    <td><a href=listadoUDF1.php>Listado de Personal con Basico y Categoria</a></td>
  </tr>
  <tr>
    <td><a href=listadoUDF2.php>Listado de Personal para Jubilacion</a></td>
  </tr>
  <tr>
    <td><a href=listadoMoneteo.php>Listado Moneteo Por Lugar De Pago</a></td>
  </tr>
  <tr>
    <td><a href=listadoConcLiq.php>Listado de Conceptos Liquidados</a></td>
  </tr>
  <tr>
    <td><a href=listadoIOMA.php>Listado IOMA (Altas, Bajas, Personal en Actividad)</a></td>
  </tr>
  <tr>
    <td><a href=listadoIOMA2017.php>Listado IOMA Archivos</a></td>
  </tr>
  <tr>
    <td><a href=listadoPersonalLiq.php>Listado de Personal Liquidado</a></td>
  </tr>
  <tr>
  	<td><a href=listadoPersonalLiquidado.php>Listado de Personal Liquidados Anual (NUEVO)</a></td>
  </tr>
  <tr>
    <td><a href=listadoUDF3.php>Listado de Personal Liquidado + CUIL y Documento</a></td>
  </tr>
  <tr>
    <td><a href=listadoVacaciones.php>Listado de Vacaciones</a></td>
  </tr>
  <tr>
    <td><a href=listadoCumples.php>Listado de Cumplea&ntilde;os</a></td>
  </tr>
  <tr>
    <td><a href=listadoAltasCajasAhorro.php>Altas de Cajas de Ahorro</a></td>
  </tr>
  <tr>
    <td><a href=informeSector.php>Informe Por Sector</a></td>
  </tr>
  <tr>
    <td><a href=informeSectorExtendido.php>Informe Por Sector (versi&oacute;n extendida)</a></td>
  </tr>
  <tr>
    <td><a href=listadoAltasBajas.php>Listado de Altas y Bajas</a></td>
  </tr>
  <tr>
    <td><a href=listado_altas_bajas.php>Listado Registro Personal</a></td>
  </tr>
  <tr>
    <td><a href=listadoHijos.php>Listado de Hijos e Hijos Discapacitados</a></td>
  </tr>
  <tr>
    <td><a href=listadoEstudios.php>Listado de Nivel de Estudios</a></td>
  </tr>
  <tr>
    <td><a href=listadoConceptosSinRAFAM.php>Listado de Conceptos no asociados a RAFAM</a></td>
  </tr>
  <tr>
    <td><a href=listadoRafam2.php>Listado de RAFAM completo</a></td>
  </tr>
  <tr>
    <td><a href=listadoSICOSS.php>Listado SICOSS</a></td>
  </tr>
  <tr>
    <td><a href=listadoTribCuentas.php>Listado de personal para tribunal de cuentas</a></td>
  </tr>
  <tr>
    <td><a href=listadoDatosIncorrectosRafam.php>Listado de empleados con datos de RAFAM incorrectos</a></td>
  </tr>

  <tr>
    <td><a href=listadoUPCN.php>Listado de Liquidaciones de UPCN</a></td>
  </tr>
  <tr>
    <td><a href="listadoART2.php">Listado ART (Nuevo)</a></td>
  </tr>
  <tr>
    <td><a href="listadoART3.php">Listado Seguro de Vida (3)</a></td>
  </tr>
   <tr>
    <td><a href=ListadoLiquidacionSeguros.php>Listado de Liquidacion Poliza Seguro (Nuevo)</a></td>
  </tr>
  
  <tr>
    <td><a href=fileLegajosRafam.php>Archivo Rafam LEGAJOS</a></td>
  </tr>
    
  <tr>
    <td><a href=fileAgentesRafam.php>Archivo Rafam AGENTES</a></td>
  </tr>
  
  <tr>
    <td><a href=fileMovimientoagentesRafam.php>Archivo Rafam MOVIMIENTO AGENTES</a></td>
  </tr>
  
  <tr>
    <td><a href=listadoJubilarse.php>Listado de Empleados Proximos a Jubilarse</a></td>
  </tr>
  
  <tr>
    <td><a href=listadoRetencionGanancias.php>Listado de Retenci�n de Ganancias</a></td>
  </tr>
  
  <tr>
    <td><a href=listadoRetencionGanancias2.php>Listado de Retenci�n de Ganancias 2013 > $15000</a></td>
  </tr>
  
  <tr>
    <td><a href=listadoRetencionGanancias4.php>Listado de Retenci�n de Ganancias 2016</a></td>
  </tr>

  <tr>
    <td><a href=listadoRetencionGanancias5.php>Listado de Retenci�n de Ganancias 2017</a></td>
  </tr>

  <tr>
    <td><a href=listadoRetencionGanancias6.php>Listado de Retenci�n de Ganancias 2018</a></td>
  </tr>

  <tr>
    <td><a href=listadoRetencionGanancias7.php>Listado de Retenci�n de Ganancias 2019</a></td>
  </tr>
  <tr>
	<td><a href=listadoRetencionGanancias8.php>Listado de Retenci�n de Ganancias 2020</a></td>
  </tr>
  <tr>
	<td><a href=listadoRetencionGanancias9.php>Listado de Retenci�n de Ganancias 2021</a></td>
  </tr>
  <tr>
  	<td><a href=listadoSeguroVida.php>Listado Seguro de Vida</a></td>
  </tr>
  <tr>
    <td><a href=listadoSIPA.php> AFIP - N�mina Salarial Empleados P�blicos No Adheridos al SIPA</a></td>
  </tr>
  <tr>
  	<td><a href=listadoJubilacion.php>Listado de Personal para Jubilaci�n (NUEVO)</a></td>
  </tr>
  <tr>
  	<td><a href=listadoCategorias.php>Listado de Personal con categorias</a></td>
  </tr>
  <tr>
  	<td><a href=informeSectorExtendido2.php>Informe Presupuesto</a></td>
  </tr>
  <tr>
        <td><a href="listadoART4.php">Listado ART (Modif. Octubre 2017)</a></td>
  </tr>
  <tr>
        <td><a href="listadoIPS2017.php">Listado IPS Modificado</a></td>
  </tr>
  <tr>
        <td><a href="listadoHistoLeg.php">Listado Hist&oacute;rico de Legajo</a></td>
  </tr>
<!--
<tr>
		    <td><a href="listadoHistoLegCompleto.php">Listado Hist&oacute;rico de Legajo Completo</a></td>
	</tr>
-->
  <tr>
	<td><a href="listadoModHoras.php">Listado Historico de Modulo de horas</a></td>
  </tr>
  <tr>
	<td><a href="listadoBonifEspeciales2004.php">Listado Historico de Bonificaciones Especiales 2004</a></td>
  </tr>
  <tr>
        <td><a href="listadoAntecedentes.php">Listado Antecedentes</a></td>
  </tr>
  <tr>
	<td><a href="listadoEmpleadosRafam.php">Listado Empleados datos Rafam</a></td>
  </tr>
  <tr>
	<td><a href="listadoPSE.php">Listado Personal sin estabilidad</a></td>
  </tr>
  <tr>
	<td><a href="listadoRetencionesLegajo.php">Listado de Retenciones por Legajo</a></td>
  </tr>
  <tr>
	  <td><a href="listadoHCG.php">Listado HCG</a></td>
  </tr>

</table>
<? include("footer.php"); ?>
