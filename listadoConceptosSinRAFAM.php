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
<form name=frmListadoCumples action=listadoCumples.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
	$rs = pg_query($db, "
select ca.\"AliasID\", ca.\"Descripcion\", count(cag.\"TipoDePlanta\") from \"tblConceptosAlias\" ca
inner join \"tblConceptos\" c on ca.\"ConceptoID\" = c.\"ConceptoID\"
left join \"tblConceptosAliasGastos\" cag on ca.\"AliasID\"= cag.\"AliasID\" and ca.\"EmpresaID\" = cag.\"EmpresaID\"
where c.\"ClaseID\" IN (0, 1, 2, 4)
group by ca.\"AliasID\", ca.\"Descripcion\"
having count(cag.\"TipoDePlanta\") <> 2
");
	if (!$rs)
		exit;
?>
<H1>Listado de Conceptos no asociados a RAFAM</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Concepto</th><th>Cantidad de asociaciones</th></tr>
<?
	while($row = pg_fetch_array($rs))
	{
		$i = 0;
		$Descripcion = $row[1];
		$Cantidad = $row[2];
?>
		<tr><td><?=$Descripcion?></td><td><?=$Cantidad?></td></tr>
<?
	}
	print "</table>\n";

pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
