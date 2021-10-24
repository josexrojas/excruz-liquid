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
	$sqlLegajo = "to_number(ed.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "ed.\"Legajo\"";
}

?>

<script>
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoCNAS.submit();
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

<form name=frmListadoCNAS action=listadoCNAS.php method=post>
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
SELECT $sqlLegajo, ed.\"TipoDocumento\", ed.\"NumeroDocumento\", em.\"Apellido\", em.\"Nombre\",
    ed.\"FechaIngreso\", ed.\"FechaNacimiento\", round(sum(re.\"Haber1\")::numeric, 2) AS \"Remuneracion\",
    (SELECT round(sum(re1.\"Descuento\"::numeric), 2) FROM \"tblRecibos\" re1
    WHERE re.\"EmpresaID\" = re1.\"EmpresaID\" AND re.\"SucursalID\" = re1.\"SucursalID\" AND re.\"Fecha\" = re1.\"Fecha\"
    AND re.\"NumeroLiquidacion\" = re1.\"NumeroLiquidacion\" AND re1.\"AliasID\" in (33, 114) AND re1.\"Legajo\" = re.\"Legajo\"
    ) AS s1,
	(SELECT round(re1.\"Descuento\"::numeric, 2) FROM \"tblRecibos\" re1
    WHERE re.\"EmpresaID\" = re1.\"EmpresaID\" AND re.\"SucursalID\" = re1.\"SucursalID\" AND re.\"Fecha\" = re1.\"Fecha\"
    AND re.\"NumeroLiquidacion\" = re1.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 34 AND re1.\"Legajo\" = re.\"Legajo\"
    ) AS s2, em.\"FechaEgreso\"
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Fecha\" = '$FechaPeriodo'
AND re.\"NumeroLiquidacion\" = '$NumeroLiquidacion' AND re.\"ConceptoID\" in (1,2,10,60)
AND em.\"TipoRelacion\" <> 4
GROUP BY 1, 2, 3, 4, 5, 6, 7, 9, 10, 11
ORDER BY 1
");
	if (!$rs){
		exit;
	}
	$Fecha = date("d-m-Y");
	$Hora = date("Hi");
	$arch = "CNAS_$Fecha.csv";
?>
<H1>Listado CNAS</H1>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>&nbsp;
<!--	<a class="tecla" href="javascript:EnviarListado('<?=$arch?>'); void(0);">
	<img src="images/icon24_enviarlistado.gif" alt="Enviar Listado por Mail" width="24" height="24" border="0" align="absmiddle">
	Enviar Listado Por Mail</a>-->
	<a class="tecla" href='#' onclick="MM_openBrWindow('listadoCNASPrint.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;
    <a class="tecla" href="listadoAltasBajas.php"> 
	<img src="images/icon24_bajarlistado.gif" alt="Listado Altas y Bajas" width="24" height="24" border="0" align="absmiddle">
	Listado Altas y Bajas</a>&nbsp;<BR />&nbsp;
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido Y Nombre</th><th>Sueldo+Antig</th><th>Prima</th><th>Conyuge</th><tr>

<?
	$TotalRem = 0;
	$TotalPrima = 0;
	$CantEmp = 0;
	$Detalle = "Legajo, Ape. Y Nom., Tipo Doc., Num. Doc., Fecha Ingreso, Fecha Nac., Remuneracion, Prima, Conyugue, Fecha Egreso\n\r";
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$TipoDoc = $row[1];
		if ($TipoDoc == '2')
			$TipoDoc = 'C.I.';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'L.E.';
		else if ($TipoDoc == '5')
			$TipoDoc = 'L.C.';
		else
			$TipoDoc = 'D.N.I.';
		$NumDoc = $row[2];
		$Ape = $row[3];
		$Nom = $row[4];
		$FechaIng = FechaSQL2WEB($row[5]);
		$FechaNac = FechaSQL2WEB($row[6]);
		$Remuneracion = $row[7];
		$Prima = $row[8];
		if ($Prima == '')
			$Prima = '0';
		$Conyuge = $row[9];
		if ($Conyuge == '')
			$Conyuge = '0';
		$FechaEgr = $row[10];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Remuneracion?></td><td><?=$Prima?></td><td><?=$Conyuge?></td></tr>
<?
		$Detalle .= '' . str_pad($Legajo, 10, ' ', STR_PAD_LEFT) . ',';
		$Detalle .= '' . str_pad($ApeYNom, 30, ' ', STR_PAD_RIGHT) . ',';
		$Detalle .= '' . str_pad($TipoDoc, 6, ' ', STR_PAD_RIGHT) . ',';
		$Detalle .= '' . str_pad($NumDoc, 10, ' ', STR_PAD_LEFT) . ',';
		$Detalle .= '' . $FechaIng . ',';
		$Detalle .= '' . $FechaNac . ',';
		$Detalle .= '' . str_pad($Remuneracion, 11, ' ', STR_PAD_LEFT) . ',';
		$Detalle .= '' . str_pad($Prima, 11, ' ', STR_PAD_LEFT) . ',';
		$Detalle .= '' . str_pad($Conyuge, 11, ' ', STR_PAD_LEFT) . ',';
		$Detalle .= '' . str_pad($FechaEgr, 10, ' ', STR_PAD_RIGHT) . "\r\n";
		$TotalRem += $Remuneracion;
		$TotalPrima += $Prima;
		$TotalCony += $Conyuge;
		$CantEmp++;
	}
	?>
	<tr><td><b>TOTAL</b></td><td></td><td><b><?=$TotalRem?></b></td><td><b><?=$TotalPrima?></b></td><td><b><?=$TotalCony?></b></td></tr>
	<?
	$Detalle .= "\n\r,,,,,TOTAL,$TotalRem,$TotalPrima,$TotalCony";
	
	print "</table>\n";
	print "<br><b>Cantidad de Legajos Procesados: $CantEmp</b><br>\n";
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
