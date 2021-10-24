<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
$Fecha = LimpiarNumero2($_POST['Fecha']);
$Fecha = substr($Fecha, 3, 2).'-'.substr($Fecha, 0, 2).'-'.substr($Fecha, 8, 4);
?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoRetenciones method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$sql = " SELECT ep.jurisdiccion, ep.programa, ep.denominacion AS prog_deno, e.agrupamiento, e.categoria, e.cargo, c.denominacion AS cate_deno, ca.denominacion AS carg_deno, count(e.\"Legajo\") AS cantidad
   FROM owner_rafam.estruc_prog ep
   JOIN \"tblEmpleadosRafam\" e ON ep.jurisdiccion::text = e.jurisdiccion::text AND ep.programa = e.programa AND ep.activ_proy = e.activ_proy
   LEFT JOIN \"tblEmpleados\" ee ON e.\"Legajo\"::text = ee.\"Legajo\"::text
   LEFT JOIN \"tblEmpleadosDatos\" ed ON e.\"Legajo\"::text = ed.\"Legajo\"::text
   LEFT JOIN owner_rafam.categorias c ON e.agrupamiento = c.agrupamiento AND e.categoria = c.categoria
   LEFT JOIN owner_rafam.cargos ca ON e.agrupamiento = ca.agrupamiento AND e.categoria = ca.categoria AND e.cargo = ca.cargo
  WHERE ep.desagrega = 'N'::bpchar AND (ee.\"FechaEgreso\" IS NULL OR ee.\"FechaEgreso\" > '$Fecha') AND (ed.\"FechaIngreso\" IS NULL OR ed.\"FechaIngreso\" < '$Fecha') and ee.\"TipoRelacion\" <> 4
  GROUP BY ep.jurisdiccion, ep.programa, e.agrupamiento, e.categoria, e.cargo, ep.denominacion, c.denominacion, ca.denominacion
  ORDER BY ep.jurisdiccion, ep.programa, e.agrupamiento, e.categoria, e.cargo;";
	//$sql = "SELECT * FROM \"dvRAFAMResumen\"";

	$rs = pg_query($db, $sql);
	if (!$rs){
		exit;
	}
?>
<H1>Listado de Recursos humanos por categoria programatica y cargo</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;
<?
	$AntProg = '';
	$AntAgru = '';
	$AntProgDeno = '';
	$TotalProg = 0;
	while($row = pg_fetch_array($rs))
	{
		$Prog = $row['programa'];
		$Agru = $row['agrupamiento'];
		$ProgDeno = $row['prog_deno'];
		$Cant = $row['cantidad'];
		$Desc = $row['cate_deno'].' - '.$row['carg_deno'];

		if ($AntProg != $Prog){
			// Cambio de Programa
			if ($AntProg != ''){
				print "</table>\n<br><br>";
				print "Total Categoria Programatica $AntProgDeno: $TotalProg<br></b>";
			}
			$AntProg = $Prog;
			$AntProgDeno = $ProgDeno;
			$TotalGeneral += $TotalProg;
			$TotalProg = 0;
			print "<br><font size=3><b>Categoria Programatica $ProgDeno<br><br></b></font>";
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Cargo</th><th>Denominaci&oacute;n</th><th>Cantidad</th></tr>
<?
		}
		$TotalProg += $Cant;
?>
		<tr><td><?=$row['agrupamiento'].'.'.$row['categoria'].'.'.$row['cargo']?></td><td><?=$Desc?></td><td><?=$Cant?></td></tr>
<?
	}
	print "</table>\n<br><br>";
	print "Total Jurisdiccion $AntJur: $TotalJur<br></b>";
	$TotalGeneral += $TotalJur;
	print "<br><b>Total General: $TotalGeneral</b><br>";
}

if ($accion == ''){
?>
	<tr><td><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p></td></tr>
	<tr>
		<td class="izquierdo"></td>
		<td class="derecho"><input type="text" name="Fecha" value="<?=date('d-m-Y')?>" /></td>
	</tr>
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
