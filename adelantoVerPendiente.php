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
$rs = pg_query($db, "SELECT *, \"Monto\" / \"Cuotas\" AS \"MontoCuota\" FROM \"VerAdelantosPendientes\"()");
if (!$rs){
	exit;
}
?>
<H1>Listado de Empleados con adelantos pendientes de saldar</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;

			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Nombre</th><th>Apellido</th><th>Monto</th><th>Cuotas</th><th>Monto cuota</th><th>Saldo</th><th>&nbsp;</th></tr>
<?
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$row['Nombre']?></td><td><?=$row['Apellido']?></td><td><?=$row['Monto']?></td><td><?=$row['Cuotas']?></td><td><?=number_format($row['MontoCuota'], 2, '.', '')?></td></td><td><?=number_format($row['Saldo'], 2, '.', '')?></td>
<?
		if ($row['ProcesadoBanco'] != true && $row['Saldo'] == $row['Monto']) { ?>
			<td><a href='adelantoEliminar.php?AdelantoID=<?=$row['AdelantoID']?>' onclick="return confirm('Confirma eliminar?');">Eliminar</a></td>
          
		<? } 
		   if ($row['Estado'] == 0 )
			  { ?>
            <td><a href='adelantoLiquidar.php?AdelantoID=<?=$row['AdelantoID']?>&Estado=1' onclick="return confirm('Confirma Liquidar?');">Liquidar</a></td>
           <? } 
			  else 
			  {  ?>
			<td><a href='adelantoLiquidar.php?AdelantoID=<?=$row['AdelantoID']?>&Estado=0' onclick="return confirm('Cancelar Liquidación?');">Cancelar Liquidar</a></td>
		<?   } ?>
         </tr>
	<? }
	print "</table>\n<br>";

pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
