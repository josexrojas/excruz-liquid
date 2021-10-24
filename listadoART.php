<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

?>

<script>
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoART.submit();
	}
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
	}
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoART action=listadoART.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
		exit;
	}
	$rs = pg_query($db, "
SELECT $sqlLegajo, ed.\"TipoDocumento\", ed.\"NumeroDocumento\", em.\"Apellido\", em.\"Nombre\", ca.detalle, 
	ed.\"FechaIngreso\", ed.\"FechaNacimiento\", ed.\"Sexo\", case when em.\"TipoRelacion\" = 5 THEN round(re.\"Haber2\"::numeric, 2) ELSE round(re.\"Haber1\"::numeric, 2) END AS \"Remuneracion\", 
	(SELECT round(re1.\"Aporte\"::numeric, 2)
	FROM \"tblRecibos\" re1 WHERE 
	re1.\"EmpresaID\" = re.\"EmpresaID\" AND re1.\"SucursalID\" = re.\"SucursalID\" AND re1.\"Fecha\" = re.\"Fecha\" AND 
	re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 32 AND re1.\"Legajo\" = re.\"Legajo\") AS s1,
	(SELECT round(re1.\"Aporte\"::numeric, 2)
	FROM \"tblRecibos\" re1 WHERE 
	re1.\"EmpresaID\" = re.\"EmpresaID\" AND re1.\"SucursalID\" = re.\"SucursalID\" AND re1.\"Fecha\" = re.\"Fecha\" AND 
	re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 31 AND re1.\"Legajo\" = re.\"Legajo\") AS s2,
	em.\"FechaEgreso\", ed.\"CUIT\" 
FROM \"tblRecibos\" re 
INNER JOIN \"tblEmpleadosDatos\" ed 
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\" 
INNER JOIN \"tblEmpleados\" em 
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\" 
INNER JOIN \"tblEmpleadosRafam\" er 
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\" 
LEFT JOIN owner_rafam.cargos ca 
ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento 
	AND ca.categoria = er.categoria AND ca.cargo = er.cargo 
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Fecha\" = '$FechaPeriodo' AND 
	re.\"NumeroLiquidacion\" = $NumeroLiquidacion AND re.\"ConceptoID\" = 99
ORDER BY 1
");
	if (!$rs){
		exit;
	}
	$Fecha = date("dmy");
	$Hora = date("Hi");
	$arch = "ART$Fecha.txt";
?>
<H1>Listado ART</H1>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>
<!--	<a class="tecla" href="javascript:EnviarListado('<?=$arch?>'); void(0);">
	<img src="images/icon24_enviarlistado.gif" alt="Enviar Listado por Mail" width="24" height="24" border="0" align="absmiddle">
	Enviar Listado Por Mail</a>-->
	<a class="tecla" href='#' onclick="MM_openBrWindow('listadoARTPrint.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido Y Nombre</th><th>Remuneracion</th><th>PCIA ART</th><th>MAPFRE 2,16%</th><th>Cargo</th><tr>
<?
	$TotalReg = 0;
	$TotalRem = 0;
	$TotalPCIA = 0;
	$TotalPCI2 = 0;
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$TipoDoc = $row[1];
		if ($TipoDoc == '2')
			$TipoDoc = 'CI';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'LE';
		else if ($TipoDoc == '5')
			$TipoDoc = 'LC';
		else
			$TipoDoc = 'DNI';
		$NumDoc = $row[2];
		$Ape = $row[3];
		$Nom = $row[4];
		$Cargo = $row[5];
		$FechaIng = FechaSQL2WEB($row[6]);
		$FechaNac = FechaSQL2WEB($row[7]);
		$Sexo = $row[8];
		$Remuneracion = $row[9];
		$PciaART = $row[10];
		$PciaPor = $row[11];
		if ($PciaART == '')
			$PciaART = 0;
		if ($PciaPor == '')
			$PciaPor = 0;
		$FechaEgr = $row[12];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
		$CUIT = $row[13];
		if (floatval($Remuneracion)>0 || 1){
?>
			<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Remuneracion?></td><td><?=$PciaART?></td><td><?=$PciaPor?></td><td><?=$Cargo?></td></tr>
<?
			$Detalle .= '"' . str_pad($Legajo, 10, ' ', STR_PAD_LEFT) . '",';
			$Detalle .= '"' . str_pad($TipoDoc, 3, ' ', STR_PAD_RIGHT) . '",';
			$Detalle .= '"' . str_pad($NumDoc, 10, ' ', STR_PAD_LEFT) . '",';
			$Detalle .= '"' . str_pad($ApeYNom, 30, ' ', STR_PAD_RIGHT) . '",';
			$Detalle .= '"' . str_pad(substr($Cargo, 0, 25), 25, ' ', STR_PAD_RIGHT) . '",';
			$Detalle .= '"' . $FechaIng . '",';
			$Detalle .= '"' . $FechaNac . '",';
			$Detalle .= '"' . $Sexo . '",';
			$Detalle .= '"' . str_pad($Remuneracion, 11, ' ', STR_PAD_LEFT) . '",';
			$Detalle .= '"' . str_pad($PciaART, 11, ' ', STR_PAD_LEFT) . '",';
			$Detalle .= '"' . str_pad($PciaPor, 11, ' ', STR_PAD_LEFT) . '",';
			$Detalle .= '"' . str_pad($FechaEgr, 10, ' ', STR_PAD_RIGHT) . '",';
			$Detalle .= '"' . str_pad($CUIT, 12, ' ', STR_PAD_RIGHT) . "\"\r\n";
			$TotalReg++;
			$TotalRem += $Remuneracion;
			$TotalPCIA += $PciaART;
			$TotalPCI2 += $PciaPor;
		}
	}
	?>
	<tr><td><b>TOTAL</b></td><td></td><td><b><?=$TotalRem?></b></td><td><b><?=$TotalPCIA?></b></td><td><b><?=$TotalPCI2?></b></td><td></td></tr>
	<?
	print "</table>";
	print "<br><b>Cantidad de Legajos Procesados: $TotalReg<br>\n";
	$fp = fopen('../listados/'.$arch, 'wb');
	fputs($fp, $Detalle); 
	fclose($fp);
}

if ($accion == ''){
	include 'selLiquida.php'; ?>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Ver Listado"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
