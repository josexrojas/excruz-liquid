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
	$sqlLegajo = "to_number(ed.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "ed.\"Legajo\"";
}

?>

<script>
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
	}
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>

<form name=frmListadoUDF method=post>
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
SELECT $sqlLegajo, re.\"Apellido\", re.\"Nombre\",
    ed.\"FechaIngreso\", ed.\"FechaNacimiento\",
    ce.\"Sueldo\",
    re.\"FechaEgreso\",
    ce.\"Categoria\",
    ce.\"Planta\",
    ce.\"Grupo\",
    ce.\"Jornada\"
FROM \"tblEmpleados\" re
LEFT JOIN \"tblCategoriasEmpleado\" ce ON ce.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" ed ON ed.\"EmpresaID\" = re.\"EmpresaID\"
AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosRafam\" er ON er.\"EmpresaID\" = re.\"EmpresaID\"
AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"FechaEgreso\" IS NULL OR re.\"FechaEgreso\" > '$FechaPeriodo'
ORDER BY 4, 5
";
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
	$Fecha = date("d-m-Y");
	$Hora = date("Hi");
?>
<H1>Listado de Personal con Basico y Categoria</H1>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Planta</th><th>Categoria</th><th>Legajo</th><th>Apellido Y Nombre</th><th>Fecha Ingreso</th><th>Sueldo Basico</th><th>Horas</th><th>Grupo</th><tr>

<?
	$TotalRem = 0;
	$TotalPrima = 0;
	$CantEmp = 0;
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$Ape = $row[1];
		$Nom = $row[2];
		$FechaIng = FechaSQL2WEB($row[3]);
		$FechaNac = FechaSQL2WEB($row[4]);
		$Basico = $row[5];
		$Horas = $row[10];
		$Categoria = $row[7];
		$Planta = $row[8];
		$Grupo = $row[9];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
?>
		<tr><td><?=$Planta?></td><td><?=$Categoria?></td><td><?=$Legajo?></td><td><?=$ApeYNom?><td><?=$FechaIng?></td><td><?=$Basico?></td><td><?=$Horas?></td><td><?=$Grupo?></td></tr>
<?
		$TotalRem += $Remuneracion;
		$TotalPrima += $Prima;
		$TotalCony += $Conyuge;
		$CantEmp++;
	}
	
	print "</table>\n";
	print "<br><b>Cantidad de Legajos Procesados: $CantEmp</b><br>\n";
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
