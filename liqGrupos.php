<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$NumeroLiquidacion = 1;
print "Emp:$EmpresaID Suc:$SucursalID";

?>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Liquidando Personas</td></tr>
</table>
</div>
<form name=frmLiqGrupos action=liqGrupos.php method=post>
<?
$GID = LimpiarNumero($_POST["GID"]);
print "<input type=hidden id=GID name=GID value=\"$GID\">";
$accion = LimpiarVariable($_POST["accion"]);
if (isset($accion) && ($accion == 'Liquidar Grupo')){
	$reliq = LimpiarNumero($_POST["chkReliquidar"]);
	if ($reliq == "1")
		$rs = pg_query($db, "
SELECT \"LiquidacionGrupo\"($EmpresaID, $SucursalID, '$GID', $NumeroLiquidacion::int2, true)");
	else
		$rs = pg_query($db, "
SELECT \"LiquidacionGrupo\"($EmpresaID, $SucursalID, '$GID', $NumeroLiquidacion::int2, false)");
	if (!$rs){
		// Ocurrio un error grave
		$res = -3;
	}else{
		$row = pg_fetch_array($rs);
		$res = $row[0];
	}
print "res:$res";
	if ($res == -1){
		// No hay un periodo abierto de liquidacion
	}else if ($res == 1){
		// Nos fijamos si hay errores
		$rs = pg_query($db, "
SELECT DISTINCT le.\"Legajo\", le.\"Descripcion\" FROM \"tblLiquidacionErrores\" le
INNER JOIN \"tblEmpleadosGrupos\" eg
ON eg.\"EmpresaID\" = le.\"EmpresaID\" AND eg.\"SucursalID\" = le.\"SucursalID\" AND eg.\"GrupoID\" = $GID AND le.\"Legajo\" = eg.\"Legajo\"
WHERE le.\"EmpresaID\" = $EmpresaID AND le.\"SucursalID\" = $SucursalID AND le.\"Legajo\" = eg.\"Legajo\"
ORDER BY le.\"Legajo\"");
		if (!$rs){
			exit;
		}
		if (pg_numrows($rs) > 0){
			print "Errores de liquidacion<br><br>\n";
			print "<table><tr><td>Legajo</td><td>Descripcion</td></tr>\n";
			while($row = pg_fetch_array($rs)){
				print "<tr><td>$row[0]</td><td>$row[1]</td></tr>\n";
			}
			print "</table>\n";
		}else{
			print "La liquidacion del grupo finalizo con exito";
		}
	}else if ($res == -3){
		// Se produjo un error grave
		print "Se produjo un error grave";
	}
}
if ($accion == 'Cancelar' || $accion == ''){
	$rs = pg_query($db, "
SELECT pe.\"FechaPeriodo\" FROM \"tblPeriodos\" pe
WHERE pe.\"EmpresaID\" = $EmpresaID AND pe.\"SucursalID\" = $SucursalID AND pe.\"Estado\" = 1
AND pe.\"NumeroLiquidacion\" = $NumeroLiquidacion
");
	if ($rs){
		$row = pg_fetch_array($rs);
		print "Liquidando para el periodo: $row[0]       Numero De Liquidacion:$NumeroLiquidacion<br><br>";
	}
?>
	<input type=checkbox id=chkReliquidar name=chkReliquidar value=1> Reliquidar el grupo<br>
<?
	ComboGrupos($db, $GID, 'Liquidar Grupo', $EmpresaID, $SucursalID);
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
