<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$LegajoNumerico = $_SESSION["LegajoNumerico"];

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

?>
<script>
	function ActualizarProgreso(iWidth){
		document.getElementById('tbPorc').width = iWidth;
	}
</script>
<H1><img src="images/icon64_liquidacion.gif" width="64" height="64" align="absmiddle" /> Liquidaci&oacute;n</H1>
<form name=frmLiqPersonas action=liqPersonas.php method=post>
<?
$NumeroLiquidacion = LimpiarNumero($_POST["NumeroLiquidacion"]);
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Continuar'){
	$rs = pg_query($db, "
SELECT pe.\"FechaPeriodo\", pe.\"TipoLiquidacionID\", pe.\"Estado\" FROM \"tblPeriodos\" pe
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND pe.\"Estado\" in (1,3)
AND pe.\"NumeroLiquidacion\" = $NumeroLiquidacion
	");
	if (!$rs){
		exit;
	}
	if (!ChequearSeguridad(1))
		exit;
	$row = pg_fetch_array($rs);
	if ($row[2] == '3'){
		if (!ChequearSeguridad(3))
			exit;
	}
	$chkTipoRelM = (LimpiarNumero($_POST["chkTipoRelM"]) == '1' ? true : false);
	$chkTipoRelJ = (LimpiarNumero($_POST["chkTipoRelJ"]) == '1' ? true : false);
	$chkTipoRelC = (LimpiarNumero($_POST["chkTipoRelC"]) == '1' ? true : false);
	$chkTipoRelL = (LimpiarNumero($_POST["chkTipoRelL"]) == '1' ? true : false);
	$chkTipoRelP = (LimpiarNumero($_POST["chkTipoRelP"]) == '1' ? true : false);
	$chkBorrar = (LimpiarNumero($_POST["chkBorrar"]) == '1' ? true : false);
	$LegDesde = LimpiarNumero($_POST["LegDesde"]);
	$LegHasta = LimpiarNumero($_POST["LegHasta"]);
	$FechaPeriodo = $row[0];
	$TipoLiquidacion = $row[1];
	$TipoRelacion = '';
	if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
	if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
	if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
	if ($chkTipoRelL == true)
		$TipoRelacion .= '4,';
	if ($chkTipoRelP == true)
		$TipoRelacion .= '5,';
	$TipoRelacion = substr($TipoRelacion, 0, -1);
?>
	Liquidando para el per&iacute;odo: <B><?=FechaSQL2WEB($FechaPeriodo)?></B> &nbsp;&nbsp;&nbsp;&nbsp; Numero De Liquidaci&oacute;n:<B><?=$NumeroLiquidacion?></B><br><br>
<?
	$sWhere = "WHERE em.\"FechaEgreso\" IS NULL AND ";
	if ($TipoRelacion != '')
		$sWhere .= "em.\"TipoRelacion\" in ($TipoRelacion) AND ";
	if ($LegajoNumerico == '1'){
		if ($LegDesde != '')
			$sWhere .= "to_number(em.\"Legajo\", '999999') >= $LegDesde AND ";
		if ($LegHasta != '')
			$sWhere .= "to_number(em.\"Legajo\", '999999') <= $LegHasta AND ";
	}
	$sWhere = substr($sWhere, 0, -5);
	$rs = pg_query($db, "SELECT count(1) FROM \"tblEmpleados\" em $sWhere");
	if (!$rs){
		exit;
	}
	$row = pg_fetch_array($rs);
	$Cantidad = $row[0];
?>
<div id=divLoading style="display:block">
<table align=center valign=center>
<tr><td nowrap="nowrap"><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Liquidando <?=$Cantidad?> Empleados</td></tr>
<tr><td><table width=100% border="1" bgcolor="#FFFFFF"  height=20 cellpadding="0" cellspacing="0">
  <tr><td><table id=tbPorc width=1 height=20><tr><td bgcolor=blue></td></tr></table></td></tr></table></td></tr>
</table>
</div>
<?
	$rs = pg_query($db, "SELECT em.\"Legajo\" FROM \"tblEmpleados\" em $sWhere");
	if (!$rs){
		exit;
	}
	pg_exec($db, "DELETE FROM \"tblLiquidacionErrores\"");
	if (ChequearSeguridad(2) && $chkBorrar == true){
		pg_exec($db, "DELETE FROM \"tblRecibos\" WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND
\"Fecha\" = '$FechaPeriodo' AND \"NumeroLiquidacion\" = $NumeroLiquidacion");
		pg_exec($db, "DELETE FROM \"tblImputacionesRafam\" WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID
AND \"Fecha\" = '$FechaPeriodo' AND \"NumeroLiquidacion\" = $NumeroLiquidacion");
	}
	$Hechos = 0;
	while($row = pg_fetch_array($rs)){
		//print "SELECT \"LiquidacionEmpleado\"($EmpresaID, $SucursalID, '$row[0]', '$FechaPeriodo'::date, $NumeroLiquidacion::int2, true)\n";
		if (!pg_exec($db, "
SELECT \"LiquidacionEmpleado\"($EmpresaID, $SucursalID, '$row[0]', '$FechaPeriodo'::date, $NumeroLiquidacion::int2, true)
		")){
			pg_exec($db, "INSERT INTO \"tblLiquidacionErrores\" VALUES ($EmpresaID, $SucursalID, '$row[0]', 'Error grave al liquidar el empleado', null)");
		}
		$Hechos++;
		$Porc = 100*$Hechos/$Cantidad;
?>
<script>
	ActualizarProgreso(<? print round($Porc); ?>);
</script>
<?
	}
	Alerta("$Hechos Empleados Liquidados");
?>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<?
	// Nos fijamos si hay errores de liquidacion
	$rs = pg_query($db, "
SELECT DISTINCT le.\"Legajo\", le.\"Descripcion\" FROM \"tblLiquidacionErrores\" le
WHERE le.\"EmpresaID\" = $EmpresaID AND le.\"SucursalID\" = $SucursalID");
	if ($rs && pg_numrows($rs) > 0){
?>
		<b>Errores de liquidacion</b><br><br>
		<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr><th>Legajo</th><th>Descripcion</th></tr>
<?
		while($row = pg_fetch_array($rs)){
			print "<tr><td>$row[0]</td><td>$row[1]</td></tr>\n";
		}
		print "</table>\n";
	}
}
if ($accion == 'Cancelar' || $accion == ''){
	// Tiene que seleccionar la liquidacion a realizar
	$rs = pg_query($db, "
SELECT pe.\"NumeroLiquidacion\", pe.\"Descripcion\", pe.\"Estado\", pe.\"FechaPeriodo\" FROM \"tblPeriodos\" pe
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND pe.\"Estado\" in (1,3)
	");
	if (!$rs){
		exit;
	}
?>
	<table class=datauser align="left">
	<TR>
	<TD class="izquierdo">Seleccione Liquidaci&oacute;n:</td><TD class=derecho2>
	<select id=NumeroLiquidacion name=NumeroLiquidacion <? print (pg_numrows($rs)>0 ? "" : "disabled");?>>
<?
	if (pg_numrows($rs) > 0){
		while($row = pg_fetch_array($rs)){
			switch($row[2]){
			case 1:
				$Estado = 'Abierta';
				break;
			case 3:
				$Estado = 'Confirmada';
				break;
			}
			print "<option value=$row[0]";
	        if ($_SESSION["NumeroLiquidacion"] == $row[0] && $_SESSION["FechaPeriodo"] == $row[3])
    	        print " selected";
			print ">$row[1] ($Estado)</option>\n";
		}
	}else{
		print "<option value=0>No hay liquidaciones activas</option>\n";
	}
?>
	</select></td></tr>
	<TR>
	<TD class="izquierdo">Tipo de Relaci&oacute;n:</td><TD class=derecho2>
	<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1>Mensual.
	<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1>Jornal.
	<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1>Contratados
	<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1>Loc. de obra
	<input type=checkbox id=chkTipoRelP name=chkTipoRelP value=1>Pasantia
	</td></tr>
<?
	if ($LegajoNumerico == '1'){
?>
	<tr><TD class="izquierdo">Legajo Desde:</td><TD class=derecho2><input type=text name=LegDesde size=5>
	Si no se completa comienza por el primer Legajo</td></tr>
	<tr><TD class="izquierdo">Legajo Hasta:</td><TD class=derecho2><input type=text name=LegHasta size=5>
	Si no se completa continua hasta el ultimo Legajo</td></tr>
<?
	}
?>
	<TR>
	<TD class="izquierdo">Borrar Liquidaci&oacute;n Anterior?:</td><TD class=derecho2><input type=checkbox id=chkBorrar name=chkBorrar value=1 onclick="javascript:if (!ChequearSeguridad(2)) return false;"></td></tr>
	<TR>
	<TD class="izquierdo">&nbsp;</td><TD class=derecho2>
<script>
	function Liquidar(){
		if (!ChequearSeguridad(1))
			return false;
		var NL = document.frmLiqPersonas.NumeroLiquidacion;

		if (NL.options[NL.selectedIndex].text.indexOf('Confirmada') > 0){
			if (!ChequearSeguridad(3))
				return false;
		}
		if (document.frmLiqPersonas.chkTipoRelM.checked == false && document.frmLiqPersonas.chkTipoRelJ.checked == false &&
			document.frmLiqPersonas.chkTipoRelC.checked == false && document.frmLiqPersonas.chkTipoRelL.checked == false && document.frmLiqPersonas.chkTipoRelP.checked == false){
			alert('Debe seleccionar al menos 1 tipo de relacion');
			return false;
		}
		document.frmLiqPersonas.accion.value = 'Continuar';
		document.frmLiqPersonas.submit();
	}
</script>
	<input type=hidden id=accion name=accion>
	<input type=button value="Continuar" onclick="javascript:Liquidar();"></td></tr></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
