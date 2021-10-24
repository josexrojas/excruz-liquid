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
<form name=frmListadoEstudios action='listadoEstudios.php' method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$chkTipoPlanta = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkJurisdiccion = LimpiarNumero($_POST["chkJurisdiccion"]);
	$selNivel = LimpiarNumero($_POST["selNivel"]);
	
	if ($selNivel == '')
		$selNivel = 'NULL';
	
	if ($txtEdad == '')
		$txtEdad = 0;

	$sql = "SELECT I.* FROM
			(
			select  em.\"Legajo\", em.\"Apellido\", em.\"Nombre\", ti.\"Descripcion\" AS \"Titulo\", ee.\"TipoEstudio\", er.\"TipoDePlanta\", er.\"jurisdiccion\", em.\"EmpresaID\", em.\"SucursalID\", em.\"FechaEgreso\", ee.\"Completado\"
				from \"tblEmpleados\" AS em

				LEFT JOIN \"tblEmpleadosEstudios\" AS ee 
					ON ee.\"EmpresaID\" = em.\"EmpresaID\" 
					   AND 
					   ee.\"SucursalID\" = em.\"SucursalID\"
					   AND
					   ee.\"Legajo\" = em.\"Legajo\"
	
				LEFT JOIN \"tblTitulosObtenidos\" AS ti
					ON ee.\"EmpresaID\" = ti.\"EmpresaID\"
					   AND
					   ee.\"TituloObtenido\" = ti.\"TituloID\"

				LEFT JOIN \"tblEmpleadosRafam\" AS er
					ON 
					   em.\"EmpresaID\" = er.\"EmpresaID\"
					   AND
					   em.\"SucursalID\" = er.\"SucursalID\"
					   AND
					   em.\"Legajo\" = er.\"Legajo\"

				WHERE   (ee.\"TipoEstudio\" > 2 OR ee.\"TipoEstudio\" IS NULL)

		UNION

				select  em.\"Legajo\", em.\"Apellido\", em.\"Nombre\", ti.\"Descripcion\" AS \"Titulo\", ee.\"TipoEstudio\", er.\"TipoDePlanta\", er.\"jurisdiccion\", em.\"EmpresaID\", em.\"SucursalID\", em.\"FechaEgreso\", ee.\"Completado\"

				from \"tblEmpleados\" AS em

				RIGHT JOIN \"tblEmpleadosEstudios\" AS ee
					ON ee.\"EmpresaID\" = em.\"EmpresaID\" 
					   AND 
					   ee.\"SucursalID\" = em.\"SucursalID\"
					   AND
					   ee.\"Legajo\" = em.\"Legajo\"

				LEFT JOIN \"tblTitulosObtenidos\" AS ti
					ON ee.\"EmpresaID\" = ti.\"EmpresaID\"
					   AND
					   ee.\"TituloObtenido\" = ti.\"TituloID\"

				LEFT JOIN \"tblEmpleadosRafam\" AS er
					ON 
					   em.\"EmpresaID\" = er.\"EmpresaID\"
					   AND
					   em.\"SucursalID\" = er.\"SucursalID\"
					   AND
					   em.\"Legajo\" = er.\"Legajo\"

				WHERE   em.\"Legajo\" NOT IN
					(SELECT \"Legajo\" FROM \"tblEmpleadosEstudios\" WHERE \"TipoEstudio\" > 2)
					AND ee.\"TipoEstudio\" > 1

		UNION

				select  em.\"Legajo\", em.\"Apellido\", em.\"Nombre\", ti.\"Descripcion\" AS \"Titulo\", ee.\"TipoEstudio\", er.\"TipoDePlanta\", er.\"jurisdiccion\", em.\"EmpresaID\", em.\"SucursalID\", em.\"FechaEgreso\", ee.\"Completado\"

				from \"tblEmpleados\" AS em 
		
				RIGHT JOIN \"tblEmpleadosEstudios\" AS ee
					ON ee.\"EmpresaID\" = em.\"EmpresaID\" 
					   AND 
					   ee.\"SucursalID\" = em.\"SucursalID\"
					   AND
					   ee.\"Legajo\" = em.\"Legajo\"

				LEFT JOIN \"tblTitulosObtenidos\" AS ti
					ON ee.\"EmpresaID\" = ti.\"EmpresaID\"
					   AND
					   ee.\"TituloObtenido\" = ti.\"TituloID\"

				LEFT JOIN \"tblEmpleadosRafam\" AS er
					ON 
					   em.\"EmpresaID\" = er.\"EmpresaID\"
					   AND
					   em.\"SucursalID\" = er.\"SucursalID\"
					   AND
					   em.\"Legajo\" = er.\"Legajo\"

				WHERE   em.\"Legajo\" NOT IN
					(SELECT \"Legajo\" FROM \"tblEmpleadosEstudios\" WHERE \"TipoEstudio\" = 2)
					AND ee.\"TipoEstudio\" = 1
		) AS I	
	
		WHERE I.\"EmpresaID\" = $EmpresaID AND I.\"SucursalID\" = $SucursalID AND I.\"FechaEgreso\" IS NULL
		      AND
			  (I.\"TipoEstudio\" = $selNivel OR $selNivel IS NULL)

		ORDER BY ".($chkJurisdiccion == '1' ? '7,':'').($chkTipoPlanta == '1' ? '6,':'')."1";

	#print "<br><br>".$sql."<br><br>";

	$rs = pg_query($db, $sql);

	if (!$rs){
		exit;
	}
?>
<H1>Listado del nivel de estudios del personal</H1>
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
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$ApeyNom = $row[1].', '.$row[2];
	
		if ($row[3])
			$Titulo = $row[3];
		else
			$Titulo = 'No Cargado';

		$NivelEstudio = 'No Cargado';

		switch($row[4]){
		case 1:
			$NivelEstudio = 'Primario';
			break;
		case 2:
			$NivelEstudio = 'Secundario';
			break;
		case 3:
			$NivelEstudio = 'Terciario';
			break;
		case 4:
			$NivelEstudio = 'Universitario';
			break;
		case 5:
			$NivelEstudio = 'Idiomas';
			break;
		case 6:
			$NivelEstudio = 'Otros';
			break;
		}

		if ($row[10])
			if ($row[10] == 1)
				$Estado = 'Completo';
			else
				$Estado = 'Incompleto';
		else
			$Estado = 'Incompleto';

		switch($row[5]){
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
			$Jurisdiccion = $row[6];
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
			print "</table><br>\n";
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
			<tr><th>Legajo</th><th>Apellido y Nombre Empleado</th><th>Titulo</th><th>Nivel</th><th>Estado</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeyNom?></td><td><?=$Titulo?></td><td><?=$NivelEstudio?></td><td><?=$Estado?></td></tr>
		
<?
	}
	print "</table><br>\n";
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
		<TD class="izquierdo">Nivel de estudios:</TD><TD class="derecho">
		<select id=selNivel name=selNivel>
			<option value=''></option>
			<option value=1>Primario</option>
			<option value=2>Secundario</option>
			<option value=3>Terciario</option>
			<option value=4>Universitario</option>
			<option value=5>Idiomas</option>
			<option value=6>Otros</option>
		</select></TD>
	</TR>

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
