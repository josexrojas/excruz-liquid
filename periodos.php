<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 4;

include 'seguridad.php';

?>
<script language="javascript">
	function AgregarPeriodo(){
		if (!ChequearSeguridad(1))
			return false;
		document.getElementById('accion').value = 'Agregar Periodo';
		document.frmPeriodos.submit();
	}
	function ConfirmarLiquidacion(Fecha, NumLiq){
		if (!ChequearSeguridad(3))
			return false;
		if (!confirm("\u00bfEsta seguro que quiere confirmar esta liquidacion?"))
			return false;
		document.getElementById('Fecha').value = Fecha;
		document.getElementById('NumLiq').value = NumLiq;
		document.getElementById('accion').value = 'Confirmar Liquidacion';
		document.frmPeriodos.submit();
	}
	function ActivarLiquidacion(Fecha, NumLiq, iTipo){
		if (iTipo == 1){
			if (!ChequearSeguridad(1))
				return false;
			if (!confirm("\u00bfEsta seguro que quiere activar esta liquidacion?"))
				return false;
		}
		document.getElementById('Fecha').value = Fecha;
		document.getElementById('NumLiq').value = NumLiq;
		if (iTipo == 1)
			document.getElementById('accion').value = 'Activar Liquidacion';
		else
			document.getElementById('accion').value = 'Activar LiqSession';
		document.frmPeriodos.submit();
	}
	function EditarLiquidacion(Fecha, NumLiq){
		if (!ChequearSeguridad(1))
			return false;
		document.getElementById('Fecha').value = Fecha;
		document.getElementById('NumLiq').value = NumLiq;
		document.getElementById('accion').value = 'Editar Liquidacion';
		document.frmPeriodos.submit();
	}
	function CerrarPeriodo(){
		if (!ChequearSeguridad(2))
			return false;
		if (!confirm("\u00bfEsta seguro que quiere cerrar todo el periodo activo?"))
			return false;
		document.getElementById('accion').value = 'Cerrar Periodo';
		document.frmPeriodos.submit();
	}
	function CerrarPeriodoLocacion(){
		if (!ChequearSeguridad(2))
			return false;
		if (!confirm("\u00bfEsta seguro que quiere cerrar todo el periodo activo?"))
			return false;
		document.getElementById('accion').value = 'Cerrar Periodo Locacion';
		document.frmPeriodos.submit();
	}
	function Aceptar(){
		var sDesc = document.frmPeriodos.DescPeriodo.value;
		var dFecha = document.frmPeriodos.FechaPago.value;
		if (sDesc == ''){
			alert('Debe completar la descripcion del periodo a agregar');
			return false;
		}
		if (dFecha == ''){
			alert('Debe completar la fecha de pago');
			return false;
		}
		document.getElementById('accion').value = 'Aceptar';
		document.frmPeriodos.submit();
	}
	function Cancelar(){
		document.getElementById('accion').value = 'Cancelar';
		document.frmPeriodos.submit();
	}
</script>

<H1><img src="images/icon64_periodo.gif" width="64" height="64" align="absmiddle" /> Per&iacute;odos</H1>
<form name=frmPeriodos action=periodos.php method=post>
<input type=hidden name=accion id=accion>
<?

$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Agregar Periodo' || $accion == 'Editar Liquidacion'){
	if ($accion == 'Editar Liquidacion'){
		$NroLiq = LimpiarNumero($_POST["NumLiq"]);
		$Fecha = LimpiarNumero2($_POST["Fecha"]);
		$rs = pg_query($db, "
SELECT \"Descripcion\", \"TipoLiquidacionID\", \"FechaPeriodo\", \"FechaPago\", \"NumeroLiquidacion\"
FROM \"tblPeriodos\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"FechaPeriodo\" = '$Fecha' AND 
\"NumeroLiquidacion\" = $NroLiq AND \"Estado\" = 1
");
		if (!$rs)
			exit;
		$row = pg_fetch_array($rs);
		$DescPeriodo = $row[0];
		$TipoLiqID = $row[1];
		$FechaPer = $row[2];
		$Pago = FechaSQL2WEB($row[3]);
		$NumLiq = $row[4];
	}else{
		$DescPeriodo = '';
		$TipoLiqID = '';
		$FechaPer = '';
		$Pago = '';
		$NumLiq = '';
	}
?>
	Descripci&oacute;n De La Liquidaci&oacute;n: <input type=text id=DescPeriodo name=DescPeriodo size=64 value="<?=$DescPeriodo?>"><br>
	<script language="javascript">
	var prevNumLiq;
	function selTipoLiq_change(o)
	{
		if (o.value != 10)
		{
			if (prevNumLiq != undefined)
				document.getElementById('NumLiq').value = prevNumLiq;
			return;	
		}

		prevNumLiq = document.getElementById('NumLiq').value;
		document.getElementById('NumLiq').value = 4;
		
	}
	</script>
	Tipo De Liquidaci&oacute;n: <select id=selTipoLiq onchange="selTipoLiq_change(this);" name=selTipoLiq <? print ($TipoLiqID != '' ? 'disabled' : ''); ?>>
<?
	$rs = pg_query($db, "
SELECT \"TipoLiquidacionID\", \"Descripcion\" FROM \"tblTipoLiquidacion\"
WHERE \"EmpresaID\" = $EmpresaID");
	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs))
	{
		print "<option value=$row[0]>$row[1]</option>\n";
	}
	if ($NumLiq == ''){
		$rs = pg_query($db, "
select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 except select \"NumeroLiquidacion\" from \"tblPeriodos\" where \"Estado\" in (1, 3) order by 1");
		/*$rs = pg_query($db, "
SELECT max(\"NumeroLiquidacion\") FROM \"tblPeriodos\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Estado\" in (1,3)");*/
		if (!$rs){
			exit;
		}
		$row = pg_fetch_array($rs);
		$NumLiq = $row[0];
		/*if ($NumLiq == '')
			$NumLiq = 0;
		$NumLiq++;*/
	}
	if ($Pago == '')
		$Pago = date("02-m-Y");
?>
	</select><br>
	Per&iacute;odo: <select id=PerMes name=PerMes <? print ($FechaPer != '' ? 'disabled' : ''); ?>>
<?
	if ($FechaPer == ''){
		$PerAno = date("Y");
		$Mes = date("m");
	}else{
		$PerAno = substr($FechaPer, 0, 4);
		$Mes = substr($FechaPer, 5, 2);
	}
	for($i=1;$i<13;$i++){
		print "<option value=$i";
		if (intval($Mes) == $i)
			print " selected";
		print ">".Mes($i)."</option>\n";
	}
?>
	</select>
<?
	if ($FechaPer != ''){
?>
		<input type=hidden name=Editar value=1>
		<input type=hidden name=ePerAno value="<?=$PerAno?>">
		<input type=hidden name=ePerMes value="<?=$Mes?>">
		<input type=hidden name=eNumLiq value="<?=$NumLiq?>">
<?
	}
?>
	<input type=text id=PerAno name=PerAno size=6 value="<?=$PerAno?>" <? print ($FechaPer != '' ? 'disabled' : ''); ?>><br>
	Fecha De Pago: <input type=text id=FechaPago name=FechaPago readonly size=11 onfocus="showCalendarControl(this);" value="<?=$Pago?>"><br>
	Numero Liquidaci&oacute;n: <input type=text id=NumLiq name=NumLiq value="<?=$NumLiq?>" <? print ($FechaPer != '' ? 'disabled' : ''); ?> readonly size=3><br><br>
	<input type=button id=aceptar value="Aceptar" onclick="Aceptar();">
	<input type=button id=cancelar value="Cancelar" onclick="Cancelar();">
<?
}else if ($accion == 'Activar Liquidacion'){
	if (!ChequearSeguridad(1))
		exit;
	$NumLiq = LimpiarNumero($_POST["NumLiq"]);
	$Fecha = LimpiarNumero2($_POST["Fecha"]);
if (!pg_exec($db, "
UPDATE \"tblPeriodos\" SET \"Activa\" = false WHERE \"Activa\" = true;
UPDATE \"tblPeriodos\" SET \"Activa\" = true WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID 
AND \"FechaPeriodo\" = '$Fecha' AND \"NumeroLiquidacion\" = $NumLiq")){
		Alerta('Hubo un error al activar la liquidaci&oacute;n');
	}else{
		Alerta('La liquidaci&oacute;n se activ&oacute; con &eacute;xito');
		$rs = pg_query($db, "
SELECT li.\"Liquida\", pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\"
FROM \"tblPeriodos\" pe
INNER JOIN \"tblTipoLiquidacion\" li
ON li.\"EmpresaID\" = pe.\"EmpresaID\" AND li.\"TipoLiquidacionID\" = pe.\"TipoLiquidacionID\"
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND pe.\"Estado\" in (1,3) and pe.\"Activa\" = true");
		if ($rs){
			$row = pg_fetch_array($rs);
			$_SESSION["Liquida"] = $row[0];
			$_SESSION["FechaPeriodo"] = $row[1];
			$_SESSION["NumeroLiquidacion"] = $row[2];
		}
	}
	$accion = '';
}else if ($accion == 'Activar LiqSession'){
	$NumLiq = LimpiarNumero($_POST["NumLiq"]);
	$Fecha = LimpiarNumero2($_POST["Fecha"]);
	$rs = pg_query($db, "
SELECT li.\"Liquida\", pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\"
FROM \"tblPeriodos\" pe
INNER JOIN \"tblTipoLiquidacion\" li
ON li.\"EmpresaID\" = pe.\"EmpresaID\" AND li.\"TipoLiquidacionID\" = pe.\"TipoLiquidacionID\"
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND
pe.\"FechaPeriodo\" = '$Fecha' AND pe.\"NumeroLiquidacion\" = $NumLiq");
	if ($rs){
		$row = pg_fetch_array($rs);
		$_SESSION["Liquida"] = $row[0];
		$_SESSION["FechaPeriodo"] = $row[1];
		$_SESSION["NumeroLiquidacion"] = $row[2];
	}
	$accion = '';

}else if ($accion == 'Confirmar Liquidacion'){
	if (!ChequearSeguridad(3))
		exit;
	$NumLiq = LimpiarNumero($_POST["NumLiq"]);
	$Fecha = LimpiarNumero2($_POST["Fecha"]);

	if (!pg_exec($db, "SELECT \"ConfirmarLiquidacion\"($EmpresaID, $SucursalID, '$Fecha', $NumLiq)"))
		Alerta('Hubo un error al confirmar la liquidaci&oacute;n');
	else
		Alerta('La liquidaci&oacute;n se confirm&oacute; con &eacute;xito');
	$accion = '';
}else if ($accion == 'Cerrar Periodo'){
	if (!ChequearSeguridad(2))
		exit;
	$rs = pg_query($db, "SELECT \"CerrarPeriodo\"($EmpresaID, $SucursalID)");

	if (!$rs){
		exit;
	}
	$row = pg_fetch_array($rs);
	if ($row[0] == '0'){
		Alerta('Todas las liquidaciones deben estar confirmadas para cerrar el per&iacute;odo');
	}else{
		Alerta('El per&iacute;odo se cerr&oacute; con &eacute;xito');
	}
	$accion = '';
}else if ($accion == 'Cerrar Periodo Locacion'){
	if (!ChequearSeguridad(2))
		exit;
	$rs = pg_query($db, "SELECT \"CerrarPeriodoLocacionObra\"($EmpresaID, $SucursalID)");

	if (!$rs){
		exit;
	}
	$row = pg_fetch_array($rs);
	if ($row[0] == '0'){
		Alerta('Todas las liquidaciones deben estar confirmadas para cerrar el per&iacute;odo');
	}else{
		Alerta('El per&iacute;odo se cerr&oacute; con &eacute;xito');
	}
	$accion = '';
}else if ($accion == 'Aceptar'){
	if (!ChequearSeguridad(1))
		exit;
	$Editar = LimpiarNumero($_POST["Editar"]);

	if ($Editar == '1'){
		$DescPeriodo = LimpiarVariable($_POST["DescPeriodo"]);
		$eNumLiq = LimpiarNumero($_POST["eNumLiq"]);
		$ePerMes = LimpiarNumero($_POST["ePerMes"]);
		$ePerAno = LimpiarNumero($_POST["ePerAno"]);
		$FechaPago = FechaWEB2SQL(LimpiarNumero($_POST["FechaPago"]));

		if ($DescPeriodo == '' || $FechaPago == '' || $eNumLiq == '' || $ePerMes == '' || $ePerAno == '')
			exit;

		if (!pg_exec($db, "
UPDATE \"tblPeriodos\" SET \"Descripcion\" = '$DescPeriodo', \"FechaPago\" = '$FechaPago'
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"FechaPeriodo\" = '$ePerAno-$ePerMes-01' AND
\"NumeroLiquidacion\" = $eNumLiq AND \"Estado\" = 1")){
			Alerta('Ha ocurrido un error al editar la liquidaci&oacute;n');
		}else{
			Alerta('La liquidaci&oacute;n se edit&oacute; con &eacute;xito');
		}
	}else{
		$DescPeriodo = LimpiarVariable($_POST["DescPeriodo"]);
		$TipoLiq = LimpiarNumero($_POST["selTipoLiq"]);
		$NumLiq = LimpiarNumero($_POST["NumLiq"]);
		$PerMes = LimpiarNumero($_POST["PerMes"]);
		$PerAno = LimpiarNumero($_POST["PerAno"]);
		$FechaPago = FechaWEB2SQL(LimpiarNumero($_POST["FechaPago"]));

		if ($DescPeriodo == '' || $FechaPago == '' || $TipoLiq == '' || $PerMes == '' || $PerAno == '' || $NumLiq == '')
			exit;

		if ($TipoLiq == 10)
		{
			$rs = pg_query($db, "
SELECT DISTINCT EXTRACT('year' FROM \"FechaPeriodo\") AS \"Anio\", EXTRACT('month' FROM \"FechaPeriodo\") AS \"Mes\"
FROM \"tblPeriodos\" WHERE \"EmpresaID\" = $EmpresaID AND  \"SucursalID\" = $SucursalID AND \"Estado\" in (1, 3) AND \"TipoLiquidacionID\" = 10
			");
		}
		else
		{
			$rs = pg_query($db, "
SELECT DISTINCT EXTRACT('year' FROM \"FechaPeriodo\") AS \"Anio\", EXTRACT('month' FROM \"FechaPeriodo\") AS \"Mes\"
FROM \"tblPeriodos\" WHERE \"EmpresaID\" = $EmpresaID AND  \"SucursalID\" = $SucursalID AND \"Estado\" in (1, 3) AND \"TipoLiquidacionID\" <> 10
			");
		}

		if (!$rs){
			exit;
		}

		$bGrabar = true;
		$bActiva = 'false';
		if (pg_numrows($rs) > 0){
			$row = pg_fetch_array($rs);
			if ($row[0] != $PerAno || $row[1] != $PerMes){
				$bGrabar = false;
			}
		}else{
			$bActiva = 'true';
		}

		if ($bGrabar){
			if (!pg_exec($db, "
INSERT INTO \"tblPeriodos\" (\"EmpresaID\", \"SucursalID\", \"TipoLiquidacionID\", \"FechaPeriodo\", \"NumeroLiquidacion\", 
\"Estado\", \"Descripcion\", \"FechaPago\", \"Activa\")
SELECT $EmpresaID, $SucursalID, $TipoLiq, '$PerAno-$PerMes-01', $NumLiq, 1, '$DescPeriodo', '$FechaPago', $bActiva")){
				Alerta('Ha ocurrido un error al agregar la liquidaci&oacute;n');
			}else{
				Alerta('La liquidaci&oacute;n se agreg&oacute; con &eacute;xito');
			}
		}else{
			Alerta('La liquidaci&oacute;n a agregar debe corresponder al mismo per&iacute;odo que las agregadas anteriormente');
		}
	}

	$accion = '';
}

if ($accion == '' || $accion == 'Cancelar'){
?>
	<input type=hidden id=Fecha name=Fecha>
	<input type=hidden id=NumLiq name=NumLiq>
	<a href="javascript:AgregarPeriodo(); void(0);" class="tecla"> 
		<img src="images/icon24_addperiodo.gif" alt="Agregar Liquidaci&oacute;n" width="24" height="23" border="0" 
		align="absmiddle">  Agregar Liquidaci&oacute;n </a>&nbsp;&nbsp;
	<a href="javascript:CerrarPeriodo(); void(0);" class="tecla"> 
		<img src="images/icon24_cerrarperiodo.gif" alt="Cerrar Per&iacute;odo" width="24" height="23" border="0"
		align="absmiddle">  Cerrar Per&iacute;odo</a>&nbsp;&nbsp;
	<a href="javascript:CerrarPeriodoLocacion(); void(0);" class="tecla"> 
		<img src="images/icon24_cerrarperiodo.gif" alt="Cerrar Per&iacute;odo Locacion" width="24" height="23" border="0"
		align="absmiddle">  Cerrar Per&iacute;odo Locacion</a>&nbsp;&nbsp;
<br /><br />
<?
	$rs = pg_query($db, "
SELECT pe.\"Descripcion\", tl.\"Descripcion\" AS \"DescTipo\", pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\", pe.\"Estado\",
pe.\"FechaPago\"
FROM \"tblPeriodos\" pe
INNER JOIN \"tblTipoLiquidacion\" tl
ON pe.\"TipoLiquidacionID\" = tl.\"TipoLiquidacionID\"
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND pe.\"Estado\" in (1,3)
ORDER BY pe.\"NumeroLiquidacion\"
	");

	if (!$rs){
		exit;
	}
	if (pg_numrows($rs) > 0){
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Descripci&oacute;n</th><th>Per&iacute;odo</th><th>Nro. Liq.</th><th>Detalles</th><th width="24">Editar</th><th width="24">Confirmar</th><th width="24">Establecer Liq.Activa</th>
	</tr>
	<?
	while($row = pg_fetch_array($rs))
	{
		$Descripcion = $row[0];
		$DescTipo = $row[1];
		$Fecha = $row[2];
		$PerAno = substr($row[2], 0, 4);
		$PerMes = substr($row[2], 5, 2);
		$NroLiquidacion = $row[3];
		switch($row[4]){
		case 1:
			$Estado = 'Abierta';
			break;
		case 2:
			$Estado = 'Cerrada';
			break;
		case 3:
			$Estado = 'Confirmada';
			break;
		}
		$FechaPago = FechaSQL2WEB($row[5]);
		if ($FechaPago == '')
			$FechaPago = 'No se elijio fecha';
		?>
		<tr>
		<td><?=$Descripcion?></td>
		<td><? print Mes($PerMes) . " de $PerAno"; ?></td>
		<td><?=$NroLiquidacion?></td>
		<?
		$Texto = "Tipo de Liquidaci&oacute;n: $DescTipo<br>";
		$Texto .= "Estado: $Estado<br>";
		$Texto .= "Fecha de Pago: $FechaPago<br>";
		?><td>
<ilayer><layer onmouseover="return escape('<?=$Texto?>');"><img src="images/icon24_valor.gif" align="absmiddle" border="0" width="24" height="24" onmouseover="return escape('<?=$Texto?>');"></layer></ilayer></td>
<? if ($row[4] == '1') { ?>
		<td><a href="javascript:EditarLiquidacion('<?=$Fecha?>', '<?=$NroLiquidacion?>'); void(0);">
		<img src="images/icon24_editar.gif" alt="Editar Liquidaci&oacute;n" align="absmiddle" border="0" width="24" height="24">
		</a></td>
<? } else { ?>
		<td>---</td>
<? }
if ($row[4] == '1') { ?>
		<td><a href="javascript:ConfirmarLiquidacion('<?=$Fecha?>', '<?=$NroLiquidacion?>');void(0);">
		<img src="images/icon24_liq_confirmar.gif" alt="Confirmar Liquidaci&oacute;n" align="absmiddle" border="0" width="24" height="24"></a></td>
<? } else { ?>
		<td><b>Confirmada</b></td>
<? }
if ($_SESSION["NumeroLiquidacion"] == $NroLiquidacion && $_SESSION["FechaPeriodo"] == $Fecha) { ?>
		<td><b>Liq. Activa</b></td>
<? } else { ?>
		<td><a href="javascript:ActivarLiquidacion('<?=$Fecha?>', '<?=$NroLiquidacion?>', 1);void(0);">
		<img src="images/icon24_liq_permanente.gif" alt="Activar Liquidaci&oacute;n Permanente" align="absmiddle" border="0" width="24" height="24"></a>
		<a href="javascript:ActivarLiquidacion('<?=$Fecha?>', '<?=$NroLiquidacion?>', 2);void(0);">
		<img src="images/icon24_liq_sesion.gif" alt="Activar Liquidaci&oacute;n Temporal" align="absmiddle" border="0" width="24" height="24"></a></td>
<? } ?>
		</tr>
		<?
	}
	print "</table>";
	}else{
		Alerta('No hay liquidaciones abiertas activas');
	}
}

pg_close($db);
?>
</form>
<? include("footer.php"); ?>
