<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

$accion = LimpiarVariable($_POST["accion"]);
?>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoHijos action=listadoHijos.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$chkTipoPlanta = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkJurisdiccion = LimpiarNumero($_POST["chkJurisdiccion"]);
	$chkSoloDisc = LimpiarNumero($_POST["chkSoloDisc"]);
	$txtEdad = LimpiarNumero($_POST["txtEdad"]);
	if ($txtEdad == '')
		$txtEdad = 0;

	$rs = pg_query($db, "
SELECT $sqlLegajo, em.\"Apellido\", em.\"Nombre\", ef.\"Nombres\", ef.\"Apellido\", ef.\"FechaNacimiento\", 
ef.\"Discapacitado\", er.jurisdiccion, em.\"TipoRelacion\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosFamiliares\" ef
ON ef.\"EmpresaID\" = em.\"EmpresaID\" AND ef.\"SucursalID\" = em.\"SucursalID\" AND ef.\"Legajo\" = em.\"Legajo\" 
AND ef.\"TipoDeVinculo\" = 2 AND ef.\"FechaBaja\" IS NULL ".($chkSoloDisc=='1'?'AND ef."Discapacitado"=true':'')."
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NULL
ORDER BY ".($chkJurisdiccion == '1' ? '8,':'').($chkTipoPlanta == '1' ? '9,':'')."1
");
	if (!$rs){
		exit;
	}
?>
<H1>Listado de Hijos e Hijos Discapacitados</H1>
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a><br><br>
<?
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoRel = '';
	$AntRel = '';
	$Abrir = 1;
	$TotalImporte = 0;
	$Detalle = '';
	$CantHijos = 0;
	while($row = pg_fetch_array($rs))
	{
		$CantHijos++;
		$Legajo = $row[0];
		$ApeyNom = $row[1].', '.$row[2];
		$ApeyNomHijo = $row[4].', '.$row[3];
		$FechaNac = FechaSQL2WEB($row[5]);
		$EsDisc = ($row[6]=='t'?'Si':'No');
		$tmp = split( "-" , $FechaNac);
		$nac = mktime(0, 0, 0, $tmp[1], $tmp[0], $tmp[2]);
		$anios = time()-$nac;
		$anios = $anios/3600;
		$anios = $anios/8766;
		if ($txtEdad > 0 && $anios > $txtEdad)
			continue;

		$Edad = floor($anios);
		switch($row[8]){
		case 1:
			$TipoRel = 'Mensualizado';
			break;
		case 2:
			$TipoRel = 'Jornalizado';
			break;
		case 3:
			$TipoRel = 'Contratado';
			break;
		}
		if ($chkJurisdiccion == '1')
			$Jurisdiccion = $row[7];
		if ($chkJurisdiccion == '1' && $Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($chkTipoPlanta == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			if ($AntRel != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
			$CantHijos--;
			print "</table><br>\n";
			print "<b>Cantidad de hijos activos: $CantHijos</b><br><br>\n";
			$CantHijos = 1;
		}
		if ($chkJurisdiccion == '1' && $Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($chkTipoPlanta == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			print "<b>Tipo De Relaci&oacute;n: $TipoRel</b><br><br>";
			$AntRel = $TipoRel;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre Empleado</th><th>Apellido y Nombre Hijo</th><th>Fecha Nacimiento</th><th>Edad</th><th>&iquest;Es Discapacitado?</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$ApeyNomHijo?></td><td><?=$FechaNac?></td><td><?=$Edad?></td><td><?=$EsDisc?></td></tr>
		
<?
	}
	print "</table><br>\n";
	print "<b>Cantidad de hijos activos: $CantHijos</b><br><br>\n";
}

if ($accion == ''){
?>
	<table>
	<TR>
		<TD class="izquierdo">Desglosar por Tipo De Relaci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkTipoPlanta name=chkTipoPlanta value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Desglosar por Jurisdicci&oacute;n:</TD><TD class="derecho"><input type=checkbox id=chkJurisdiccion name=chkJurisdiccion value=1></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Listar hijos de hasta:</TD><TD class="derecho"><input type=text name=txtEdad size=3> a&ntilde;os (Si no completa no se limitar&aacute; por edad)</TD>
	</TR>
	<TR>
		<TD class="izquierdo">Listar solo hijos discapacitados</TD><TD class="derecho"><input type=checkbox name=chkSoloDisc value=1></TD>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho">
		<input type=submit id=accion name=accion value="Ver Listado">
	</TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
