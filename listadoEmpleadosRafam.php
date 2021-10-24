<?
include ('header.php');

if (!($db = Conectar()))
	exit;
?>

<form name=frmListadoEmpleadoDatosRafam method=post>
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
	<H1>Listado Empleados datos Rafam</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = <<<FIN

SELECT 
       e."Legajo",
       (CASE WHEN e."Apellido" IS NULL THEN '' ELSE e."Apellido" END || ',' || CASE WHEN e."Nombre" IS NULL THEN '' ELSE e."Nombre" END) AS "ApellidoNombre",
       c."Dependencia",
       c."Grupo",
       r."agrupamiento" as "Agrupamiento",
       r."categoria" as "Categoria",
       r."cargo" as "Cargo",
       SUBSTRING(r."jurisdiccion", 6, 2) as "Jurisdiccion",
       r."programa" as "Programa",
       r."activ_proy" as "Actividad",
       c."Planta",
       c."Categoria",
       c."Jornada"
FROM      "tblEmpleados"           e
LEFT JOIN "tblCategoriasEmpleado"  c ON c."Legajo" = e."Legajo"
LEFT JOIN "tblEmpleadosRafam"      r ON r."Legajo" = e."Legajo"
WHERE e."FechaEgreso" IS NULL      
ORDER BY CAST(c."Legajo" AS INT)

FIN;

	$rs = pg_query($db, $sql);

	if (!$rs) exit;

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid" >
		<tr><th nowrap>Legajo</th><th nowrap>Apellido y Nombre</th><th nowrap>Dependencia</th><th nowrap>Grupo</th><th nowrap>Agrupamiento</th><th nowrap>Categoria</th><th nowrap>Cargo</th><th nowrap>Jurisdiccion</th><th nowrap>Programa</th><th nowrap>Actividad</th><th nowrap>Planta</th><th nowrap>Categoria</th><th nowrap>Jornada</th></tr>

<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td nowrap><?=$row[0]?></td><td nowrap><?=$row[1]?></td><td nowrap><?=$row[2]?></td><td nowrap><?=$row[3]?></td><td nowrap><?=$row[4]?></td><td nowrap><?=$row[5]?></td><td nowrap><?=$row[6]?></td><td nowrap><?=$row[7]?></td><td nowrap><?=$row[8]?></td><td nowrap><?=$row[9]?></td><td nowrap><?=$row[10]?></td><td nowrap><?=$row[11]?></td><td nowrap><?=$row[12]?></td><td nowrap><?=$row[13]?></td></tr>
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

