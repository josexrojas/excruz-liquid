<?
include ('header.php');

if (!($db = Conectar()))
	exit;
?>

<form name=frmListadoPSE method=post>
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
	<H1>Listado Personal sin estabilidad</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN

SELECT 
       e."Apellido",
       e."Nombre",
       ed."NumeroDocumento",
       e."Legajo"
FROM      "tblEmpleados"          e
LEFT JOIN "tblCategoriasEmpleado" c  ON c."Legajo"  = e."Legajo"
LEFT JOIN "tblEmpleadosRafam"     er ON e."Legajo"  = er."Legajo"
LEFT JOIN "tblEmpleadosDatos"     ed ON ed."Legajo" = e."Legajo"

WHERE (c."Planta" = 'T') OR (c."Legajo" IS NULL AND er."TipoDePlanta" = 2) AND
       e."FechaEgreso" IS NULL      
ORDER BY CAST(e."Legajo" AS int)

FIN;

	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid" >
		<tr><th nowrap>Apellido</th><th nowrap>Nombre</th><th nowrap>DNI</th><th nowrap>Nro. de Legajo</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td nowrap><?=$row[0]?></td><td nowrap><?=$row[1]?></td><td nowrap><?=$row[2]?></td><td nowrap><?=$row[3]?></td></tr>
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

