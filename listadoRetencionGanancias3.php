<?
include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoPersonalLiq method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

	<H1>Listado de Retenciones de Ganancias</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN
  
select
  e."Legajo",
  e."Nombre" || ' ' || e."Apellido" AS "Nombre",
  SUM("Haber1" + "Haber2") AS "Total",
  r."Fecha"
  FROM "tblRecibos" r
  INNER JOIN "tblEmpleados" e ON r."Legajo" = e."Legajo"  
  WHERE "ConceptoID" = 99 AND "Fecha" >= '2015-07-31' AND "Fecha" <= '2015-12-31' -- AND (("Haber1" + "Haber2") > 30000)
  GROUP BY e."Legajo",
  e."Nombre" || ' ' || e."Apellido",
  r."Fecha"
  HAVING SUM("Haber1" + "Haber2") >= 30000
  ORDER BY "Fecha", "Legajo" ASC

  
FIN;


	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Fecha</th><th>Cobrado</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row[0]?></td><td><?=$row[1]?></td><td><?=$row[3]?></td><td>$ <?=number_format($row[2], 2)?></td></tr>
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
