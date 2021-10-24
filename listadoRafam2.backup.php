<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

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

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoART action=listadoRafam2.php method=post>
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
	$sql = "
SELECT
  $sqlLegajo,
  em.\"Apellido\",
  em.\"Nombre\",
  er.jurisdiccion,
  ag.detalle as agrupamiento,
  cat.detalle as categoria,
  ca.detalle as cargo,
  ff.denominacion as fuente

FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleadosDatos\" ed ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleados\" em ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
LEFT JOIN owner_rafam.agrupamientos ag ON substr(ag.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ag.agrupamiento = er.agrupamiento
LEFT JOIN owner_rafam.categorias cat ON substr(cat.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND cat.agrupamiento = er.agrupamiento AND cat.categoria = er.categoria 
LEFT JOIN owner_rafam.cargos ca ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento AND ca.categoria = er.categoria AND ca.cargo = er.cargo
LEFT JOIN owner_rafam.fuen_fin ff ON er.codigo_ff = ff.codigo_ff

WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Fecha\" = '$FechaPeriodo' AND 
	re.\"NumeroLiquidacion\" = $NumeroLiquidacion AND re.\"ConceptoID\" = 99
ORDER BY 1
";
	$rs = pg_query($db, $sql);

	if (!$rs){
		exit;
	}
	$Fecha = date("dmy");
	$Hora = date("Hi");
?>
<H1>Listado con fuente de financiamiento</H1>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido Y Nombre</th><th>Jurisdiccion</th><th>Agrupamiento</th><th>Categoria</th><th>Cargo</th><th>Fuente financiamiento</th><tr>
<?
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$Ape = $row[1];
		$Nom = $row[2];
		$Jur = $row[3];
		$Agru = $row[4];
		$Cat = $row[5];
		$Cargo = $row[6];
		$Fte = $row[7];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
?>
			<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Jur?></td><td><?=$Agru?></td><td><?=$Cat?></td><td><?=$Cargo?></td><td><?=$Fte?></td></tr>
		<?
	}
	?>
	<?
	print "</table>";
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
