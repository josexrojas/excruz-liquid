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

<form name=frmListadoUDF2 action=listadoUDF2.php method=post>
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
	$sql = <<<EOL
SELECT $sqlLegajo, ed."TipoDocumento", ed."NumeroDocumento", em."Apellido", em."Nombre",
    extract(year from age(ed."FechaNacimiento")) as "Edad", ed."FechaIngreso",
    SUM(CASE WHEN re."ConceptoID" IN (1, 2) THEN re."Haber1" ELSE 0 END) AS "SueldoBasico",
    SUM(CASE WHEN re."ConceptoID" = 10 THEN re."Haber1" ELSE 0 END) AS "Antiguedad",
    em."FechaEgreso",
    ce."Categoria",
    ce."Grupo",
    CASE WHEN er."TipoDePlanta" = 1 THEN 'Permanente' ELSE 'Contratado' END,
    ce."Jornada",
    SUM(CASE WHEN re."ConceptoID" IN (31, 34) THEN re."Haber1" ELSE 0 END) AS "Bonif"
FROM "tblRecibos" re
INNER JOIN "tblEmpleadosDatos" ed
ON ed."EmpresaID" = re."EmpresaID" AND ed."SucursalID" = re."SucursalID" AND ed."Legajo" = re."Legajo"
INNER JOIN "tblEmpleados" em
ON em."EmpresaID" = re."EmpresaID" AND em."SucursalID" = re."SucursalID" AND em."Legajo" = re."Legajo"
INNER JOIN "tblEmpleadosRafam" er
ON er."EmpresaID" = re."EmpresaID" AND er."SucursalID" = re."SucursalID" AND er."Legajo" = re."Legajo"
LEFT JOIN "tblCategoriasEmpleado" ce ON em."Legajo" = ce."Legajo"
WHERE re."EmpresaID" = $EmpresaID AND re."SucursalID" = $SucursalID AND re."Fecha" = '$FechaPeriodo'
AND re."NumeroLiquidacion" = '$NumeroLiquidacion' AND re."ConceptoID" in (1,2,10,31,34)
GROUP BY 1, 2, 3, 4, 5, 6, 7, 10, 11, 12, 13, 14
ORDER BY 6 desc, 4, 5
EOL;

	//print $sql;
	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
	$Fecha = date("d-m-Y");
	$Hora = date("Hi");
?>
<H1>Listado de Personal para Jubilacion</H1>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Planta</th><th>Categoria</th><th>Grupo</th><th>Jornada</th><th>Legajo</th><th>Apellido Y Nombre</th><th>Documento</th><th>Edad</th><th>F. Ingreso</th><th>Sueldo Basico</th><th>Antiguedad</th><th>Bonificaciones</th><tr>

<?
	$TotalRem = 0;
	$TotalPrima = 0;
	$CantEmp = 0;
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
		$Edad = $row[5];
		$FechaIng = FechaSQL2WEB($row[6]);
		$Basico = $row[7];
		$Antiguedad = $row[8]; 
		$FechaEgr = $row[9];
		$Categoria = $row[10];
		$Grupo = $row[11];
		$Relacion = $row[12];
		$Jornada = $row[13];
		$Bonif = $row[14];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
?>
		<tr><td><?=$Relacion?></td><td><?=$Categoria?></td><td><?=$Grupo?></td><td><?=$Jornada?></td><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$TipoDoc?><?=$NumDoc?><td><?=$Edad?></td><td><?=$FechaIng?></td><td><?=$Basico?></td><td><?=$Antiguedad?></td><td><?=$Bonif?></td></tr>
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
