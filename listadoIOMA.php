<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

include ('header.php');

// Definicion de constantes
// Campos
define ('BOOLEAN_FIELD',   'L');
define ('CHARACTER_FIELD', 'C');
define ('DATE_FIELD',      'D');
define ('NUMBER_FIELD',    'N');
// Modos
define ('READ_ONLY',  '0');
define ('WRITE_ONLY', '1');
define ('READ_WRITE', '2');

// Archivos
$MesAno = date("my");
$DBFArchivoIOMAAlta = '../listados/iomaalta.dbf';
$DBFArchivoIOMABaja = '../listados/iomabaja.dbf';
$DBFArchivoIOMABase = "../listados/ioma$MesAno.dbf";

// Definiciones de bases
$DBDefinicionIOMAAlta = array (
   array('APEYNOM',		CHARACTER_FIELD, 30),
   array('NRODOC',		CHARACTER_FIELD, 8),
   array('LEGAJO',		CHARACTER_FIELD, 8)
);

$DBDefinicionIOMABaja = array (
   array('APEYNOM',		CHARACTER_FIELD, 30),
   array('NRODOC',		CHARACTER_FIELD, 8),
   array('LEGAJO',		CHARACTER_FIELD, 8)
);

$DBDefinicionIOMABase = array(
   array('APEYNOM',		CHARACTER_FIELD, 30),
   array('NRO_DOC',		NUMBER_FIELD, 8, 0),
   array('LEGAJO',		CHARACTER_FIELD, 10),
   array('SITUACION',	CHARACTER_FIELD, 1),
   array('APORTE',		CHARACTER_FIELD, 11)
);

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoIOMA action=listadoIOMA.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Generar IOMA'){
	$FechaDesde = FechaWEB2SQL(LimpiarNumero($_POST["FechaDesde"]));
	$FechaHasta = FechaWEB2SQL(LimpiarNumero($_POST["FechaHasta"]));
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$dAno = LimpiarNumero(substr($selPeriodo, 0, $i));
		$dMes = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($dAno == '' || $dMes == '' || $FechaDesde == '' || $FechaHasta == '')
		exit;
?>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Archivos</td></tr>
</table>
</div>
<?
	// Elimina los archivos
	if(file_exists($DBFArchivoIOMAAlta))
		unlink($DBFArchivoIOMAAlta);
	if(file_exists($DBFArchivoIOMABaja))
		unlink($DBFArchivoIOMABaja);
	if(file_exists($DBFArchivoIOMABase))
		unlink($DBFArchivoIOMABase);
	
	// Creamos los archivos
	$dbAlta = @ dbase_create($DBFArchivoIOMAAlta, $DBDefinicionIOMAAlta) or die ("Error al crear $DBFArchivoIOMAAlta");
	$dbBaja = @ dbase_create($DBFArchivoIOMABaja, $DBDefinicionIOMABaja) or die ("Error al crear $DBFArchivoIOMABaja");
	$dbBase = @ dbase_create($DBFArchivoIOMABase, $DBDefinicionIOMABase) or die ("Error al crear $DBFArchivoIOMABase");

	// Abrimos los archivos
	$fhAlta = @ dbase_open($DBFArchivoIOMAAlta, READ_WRITE) or die ("Error al abrir $DBFArchivoIOMAAlta");
	$fhBaja = @ dbase_open($DBFArchivoIOMABaja, READ_WRITE) or die ("Error al abrir $DBFArchivoIOMABaja");
	$fhBase = @ dbase_open($DBFArchivoIOMABase, READ_WRITE) or die ("Error al abrir $DBFArchivoIOMABase");

	$CodEmp = 'M036-1';

	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", em.\"FechaEgreso\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND
em.\"FechaEgreso\" >= '$FechaDesde' AND em.\"FechaEgreso\" <= '$FechaHasta'
AND em.\"TipoRelacion\" <> 4
");

	if (!$rs){
		exit;
	}
	// Procesamos bajas de IOMA
	$CantBaja = 0;
$divBajas = "<div id=dvIOMABajas style='display:none'>\n";
	if (pg_numrows($rs)>0){
$divBajas .= "<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>\n";
$divBajas .= "<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Numero de Documento</th><th>Fecha de Egreso</th></tr>\n";
	while($row = pg_fetch_array($rs))
	{
		$CantBaja++;
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		$FechaEgr = FechaSQL2WEB($row[4]);
$divBajas .= "<tr><td>$Legajo</td><td>$ApeyNom</td><td>$NroDoc</td><td>$FechaEgr</td></tr>\n";
		dbase_add_record($fhBaja, array($ApeyNom, $NroDoc, $Legajo));
	}
$divBajas .= "</table><br>\n";
	}else{
$divBajas .= "<div class=alerta>No figuran empleados dados de baja</div><br>\n";
	}
$divBajas .= "</div>\n";
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\"
FROM \"tblEmpleadosDatos\" ed
INNER JOIN \"tblEmpleados\" em
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
WHERE ed.\"EmpresaID\" = $EmpresaID AND ed.\"SucursalID\" = $SucursalID AND
ed.\"FechaIngreso\" >= '$FechaDesde' AND ed.\"FechaIngreso\" <= '$FechaHasta'
AND em.\"TipoRelacion\" <> 4
");

	if (!$rs){
		exit;
	}
	// Procesamos altas de IOMA
	$CantAlta = 0;
$divAltas = "<div id=dvIOMAAltas style='display:none'>\n";
	if (pg_numrows($rs)>0){
$divAltas .= "<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>\n";
$divAltas .= "<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Numero de Documento</th><th>Fecha de Ingreso</th></tr>\n";
	while($row = pg_fetch_array($rs))
	{
		$CantAlta++;
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		$FechaIng = FechaSQL2WEB($row[4]);
$divAltas .= "<tr><td>$Legajo</td><td>$ApeyNom</td><td>$NroDoc</td><td>$FechaIng</td></tr>\n";
		dbase_add_record($fhAlta, array($ApeyNom, $NroDoc, $Legajo));
	}
$divAltas .= "</table><br>\n";
	}else{
$divAltas .= "<div class=alerta>No figuran empleados dados de alta</div><br>\n";
	}
$divAltas .= "</div>\n";
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", em.\"TipoRelacion\", sum(\"Descuento\") 
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"AliasID\" = 17 AND 
extract('year' from re.\"Fecha\") = $dAno AND extract('month' from re.\"Fecha\") = $dMes
AND em.\"TipoRelacion\" <> 4
GROUP BY 1, 2, 3, 4, 5
");

	if (!$rs){
		exit;
	}
	// Procesamos gente en actividad
	$CantActiv = 0;
$divActiv = "<div id=dvIOMAActiv style='display:none'>\n";
	if (pg_numrows($rs)>0){
$divActiv .= "<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>\n";
$divActiv .= "<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Numero de Documento</th><th>Situaci&oacute;n</th><th>Aporte</th></tr>\n";
	while($row = pg_fetch_array($rs))
	{
		$CantActiv++;
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		switch($row[4]){
		case 1:
			$TipoRel = 'M';
			break;
		case 2:
			$TipoRel = 'J';
			break;
		case 3:
			$TipoRel = 'C';
			break;
		}
		$Imp = round($row[5], 2);
		$i = strpos($Imp, '.');
		if ($i === false)
			$Imp .= '.00';
		else
			$Imp = substr($Imp . '00', 0, $i+3);
		$Importe = str_pad($Imp, 11, '0', STR_PAD_LEFT);
$divActiv .= "<tr><td>$Legajo</td><td>$ApeyNom</td><td>$NroDoc</td><td>$TipoRel</td><td>$Imp</td></tr>\n";
		dbase_add_record($fhBase, array($ApeyNom, $NroDoc, $Legajo, $TipoRel, $Importe));
	}
$divActiv .= "</table><br>\n";
	}else{
$divActiv .= "<div class=alerta>No figuran empleados en actividad</div><br>\n";
	}
$divActiv .= "</div>\n";
	dbase_close($fhAlta);
	dbase_close($fhBaja);
	dbase_close($fhBase);
	alerta('Los archivos del IOMA fueron generados correctamente');
	print "<br><b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br>";
?>
	<br><b>Se Procesaron <?=$CantAlta?> Altas</b>
	<a href="javascript:VerDiv(1); void(0);" class="tecla">
	<img src="images/icon24_ver.gif" alt="Ver" width="24" height="24" border="0" align="absmiddle"> Ver/Ocultar Detalle </a>
	<br><br><?=$divAltas?>
	<b>Se Procesaron <?=$CantBaja?> Bajas</b>
	<a href="javascript:VerDiv(2); void(0);" class="tecla">
	<img src="images/icon24_ver.gif" alt="Ver" width="24" height="24" border="0" align="absmiddle"> Ver/Ocultar Detalle </a>
	<br><br><?=$divBajas?>
	<b>Se Procesaron <?=$CantActiv?> Empleados Activos</b>
	<a href="javascript:VerDiv(3); void(0);" class="tecla">
	<img src="images/icon24_ver.gif" alt="Ver" width="24" height="24" border="0" align="absmiddle"> Ver/Ocultar Detalle </a>
	<br><br><?=$divActiv?>
	<script>
		document.getElementById('divLoading').style.display = 'none';
		function BajarListado(sArch){
			document.getElementById('accion').value = 'Bajar Listado';
			document.getElementById('listado').value = sArch;
			document.frmListadoIOMA.submit();
		}
		function VerDiv(iCual){
			var sDiv;
			if (iCual == 1)
				sDiv = 'dvIOMAAltas';
			else if (iCual == 2)
				sDiv = 'dvIOMABajas';
			else if (iCual == 3)
				sDiv = 'dvIOMAActiv';
			var div = document.getElementById(sDiv);
			if (div.style.display == 'none')
				div.style.display = 'block';
			else
				div.style.display = 'none';
		}
	</script><br>
	<a class="tecla" href="javascript:BajarListado('iomaalta.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Altas</a> 
	<a class="tecla" href="javascript:BajarListado('iomabaja.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Bajas</a> 
	<a class="tecla" href="javascript:BajarListado('ioma<?=$MesAno?>.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Empleados En Actividad</a> 
<?
}

if ($accion == ''){
	$rs = pg_query($db, "
SELECT DISTINCT extract('year' from \"FechaPeriodo\"), extract('month' from \"FechaPeriodo\")
FROM \"tblPeriodos\"
ORDER BY 1 DESC, 2 DESC
	");
	if (!$rs){
		exit;
	}
?>
	<script>
		function Fechas(Periodo){
			var i, Ano, Mes, Dia, FecDesde, FecHasta;
			var Dias = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

			i = Periodo.indexOf("|");
			if (i < 0)
				return;
			Ano = Periodo.substring(0, i);
			Mes = Periodo.substring(i+1);
			Dia = Dias[parseInt(Mes)-1];
			if ((Ano % 4 == 0) && ((Ano % 100 != 0) || (Ano % 400 == 0)) && Mes == 2)
				Dia++;
			FecDesde = "01-" + Mes + "-" + Ano;
			FecHasta = Dia + "-" + Mes + "-" + Ano;
			document.getElementById("FechaDesde").value = FecDesde;
			document.getElementById("FechaHasta").value = FecHasta;
		}
	</script>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione Periodo:</TD><TD class="derecho">
		<select id=selPeriodo name=selPeriodo onchange="javascript:Fechas(this.value);">
<?
	while($row = pg_fetch_array($rs)){
		$dAno = $row[0];
		$mes = $row[1];
		if (strlen($mes) < 2)
			$mes = "0$mes";
		$dMes = Mes($row[1]);
		print "<option value=$dAno|$mes>$dMes DE $dAno</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo">Fecha Desde:</TD><TD class="derecho">
		<input type=text name=FechaDesde id=FechaDesde onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FecDesde?>"></TD>
	</TR>
	<TR>
		<TD class="izquierdo">Fecha Hasta:</TD><TD class="derecho">
		<input type=text name=FechaHasta id=FechaHasta onfocus="showCalendarControl(this);" readonly size=11 value="<?=$FecHasta?>"></TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Generar IOMA"></TD></TR></table>
	<script>
		Fechas(document.getElementById('selPeriodo').value);
	</script>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
