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

	<H1>Listado de Empleados activos por categoría</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN

select e."Legajo", "Apellido" || ', ' || "Nombre" as "Nombre", "Dependencia", "Grupo", "Planta", "Categoria", "Jornada", "Sueldo"
from "tblEmpleados" e
left join "tblCategoriasEmpleado" ec on e."EmpresaID" = 1 and e."SucursalID" = 1 and e."Legajo" = ec."Legajo"
where e."FechaEgreso" IS NULL
order by "Categoria", "Jornada", "Dependencia", "Grupo", "Planta"
;
  
FIN;

	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Dependencia - Grupo - Planta</th><th>Categoría</th><th>Jornada</th><th>Sueldo</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row[0]?></td><td><?=$row[1]?></td><td><?=$row[2]?> - <?=$row[3]?> - <?=$row[4]?></td><td><?=$row[5]?></td><td><?=$row[6]?></td><td>$ <?=number_format($row[7], 2)?></td></tr>
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
