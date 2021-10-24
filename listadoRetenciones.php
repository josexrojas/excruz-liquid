<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoRetenciones action=listadoRetenciones.php method=post>
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
SELECT er.jurisdiccion, er.codigo_ff, re.\"AliasID\", re.\"Descripcion\", sum(re.\"Descuento\")
FROM \"tblRecibos\" re
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblConceptos\" co
ON re.\"EmpresaID\" = co.\"EmpresaID\" AND co.\"ClaseID\" = 3 AND re.\"ConceptoID\" = co.\"ConceptoID\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY er.jurisdiccion, er.codigo_ff, re.\"AliasID\", re.\"Descripcion\"
ORDER BY 1,2,3
");
	if (!$rs){
		exit;
	}
?>
<H1> Listado de Retenciones por Jurisdiccion y Fuente de Financiamiento</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
	$AntJur = '';
	$AntFF = '';
	$TotalJur = 0;
	$TotalFF = 0;
	while($row = pg_fetch_array($rs))
	{
		$Jur = $row[0];
		$FF = $row[1];
		$AID = $row[2];
		$Desc = $row[3];
		$Imp = $row[4];

		if ($AntJur <> $Jur){
			// Cambio Jurisdiccion
			if ($AntJur <> ''){
				print "</table>\n<br><br><b>Total Fuente $AntFF: $TotalFF<br>";
				print "Total Jurisdiccion $AntJur: $TotalJur<br></b>";
			}
			$AntJur = $Jur;
			$AntFF = $FF;
			$TotalGeneral += $TotalJur;
			$TotalFF = 0;
			$TotalJur = 0;
			print "<br><font size=3><b>Jurisdiccion $Jur<br>Fuente $FF<br><br></b></font>";
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Concepto</th><th>Descripcion</th><th>Importe</th></tr>
<?
		}
		$TotalFF += $Imp;
		$TotalJur += $Imp;
?>
		<tr><td><?=$AID?></td><td><?=$Desc?></td><td><?=$Imp?></td></tr>
<?
	}
	print "</table>\n<br><br><b>Total Fuente $AntFF: $TotalFF<br>";
	print "Total Jurisdiccion $AntJur: $TotalJur<br></b>";
	$TotalGeneral += $TotalJur;
	print "<br><b>Total General: $TotalGeneral</b><br>";
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
