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
if ($accion == 'Ver Listado'){
	$Desde = LimpiarNumero($_POST["selDesdeMes"]);
	$Hasta = LimpiarNumero($_POST["selHastaMes"]);
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	if ($Desde == '' || $Hasta == '')
		exit;
	$Meses = "($Desde";
	while($Desde!=$Hasta){
		$Desde++;
		if ($Desde > 12)
			$Desde = 1;
		$Meses .= ",$Desde";
	}
	$Meses .= ")";
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Apellido\", em.\"Nombre\", ed.\"FechaNacimiento\", extract('day' from ed.\"FechaNacimiento\"),
	extract('month' from ed.\"FechaNacimiento\")".($Jur=='1'?",er.jurisdiccion":"")."
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NULL
AND extract('month' from ed.\"FechaNacimiento\") in $Meses
ORDER BY ".($Jur=='1'?"7,":"")."6,5,4
");
	if (!$rs)
		exit;
?>
<H1>Listado de Cumplea&ntilde;os</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR><br>
<?
	$Jurisdiccion = '';
	$AntJur = '';
	$AntMes = '';
	$Abrir = 1;
	while($row = pg_fetch_array($rs))
	{
		$i = 0;
		$Legajo = $row[$i++];
		$Apellido = $row[$i++];
		$Nombre = $row[$i++];
		$FechaNac = FechaSQL2WEB($row[$i++]);
		$DiaCumple = $row[$i++];
		$MesCumple = $row[$i++];
		$ApeyNom = $Apellido . ' ' . $Nombre;
		if ($Jur == '1')
			$Jurisdiccion = $row[$i++];

		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($MesCumple != '' && $AntMes != $MesCumple){
			if ($AntMes != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
			print "</table><br>\n";
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$Abrir = 1;
		}
		if ($MesCumple != '' && $AntMes != $MesCumple){
			print "<b>Cumplea&ntilde;os del mes de " . Mes($MesCumple) . "</b><br><br>";
			$AntMes = $MesCumple;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Fecha de Nacimiento</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$FechaNac?></td></tr>
<?
	}
	print "</table>\n";
}

if ($accion == ''){
?>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Desde Mes:</TD><TD class="derecho">
		<select id=selDesdeMes name=selDesdeMes>
<?
		for ($i=1;$i<13;$i++)
			print "<option value=$i>" . Mes($i) . "</option>\n";
?>
		</select></td>
	</TR>
	<TR>
		<TD class="izquierdo">Hasta Mes:</TD><TD class="derecho">
		<select id=selHastaMes name=selHastaMes>
<?
		for ($i=1;$i<13;$i++)
			print "<option value=$i>" . Mes($i) . "</option>\n";
?>
		</select></td>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
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
