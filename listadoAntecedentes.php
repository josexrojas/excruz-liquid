<?
include ('header.php');

if (!($db = Conectar()))
	exit;
?>

<form name=frmListadoAntecedentes method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>
<br>
	<H1>Listado Antecedentes de empleados</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN

SELECT e."Legajo",
(CASE WHEN e."Nombre" IS NULL THEN '' ELSE e."Nombre" END || ',' || CASE WHEN e."Apellido" IS NULL THEN '' ELSE e."Apellido" END) AS "Nombre",
c."Categoria", d."FechaIngreso",e."FechaEgreso",a."FechaDesde" as "FechaAnteriorIngreso",a."FechaHasta" as "FechaAnteriorEgreso"
FROM "tblEmpleados" AS e
INNER JOIN "tblEmpleadosDatos" AS d ON e."Legajo" = d."Legajo"
LEFT JOIN "tblCategoriasEmpleado" AS c ON d."Legajo" = c."Legajo"
LEFT JOIN (SELECT  "Legajo",min("FechaDesde") AS "FechaDesde",min("FechaHasta") AS "FechaHasta" FROM "tblEmpleadosAntecedentes" GROUP BY "Legajo") a ON c."Legajo" = a."Legajo"
ORDER BY CAST(e."Legajo" AS INTEGER);

FIN;

	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid" >
		<tr><th nowrap>Legajo</th><th nowrap>Nombre y Apellido</th><th nowrap>Categoria</th><th nowrap>Fecha de Ingreso</th><th nowrap>Fecha de Egreso</th><th nowrap>Fecha Anterior Ingreso</th><th nowrap>Fecha Anterior Egreso</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td nowrap><?=$row[0]?></td><td nowrap><?=$row[1]?></td><td nowrap><?=$row[2]?></td><td nowrap><?=$row[3]?></td><td nowrap><?=$row[4]?></td><td nowrap><?=$row[5]?></td><td nowrap><?=$row[6]?></td><td nowrap><?=$row[7]?></td></tr>
<?
	}
	print "</table><br>\n";
?>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<?
pg_close($db);
?>
</form>
<? include("footer.php"); ?>

