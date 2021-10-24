<?
require_once('funcs.php');

if (!($db = Conectar()))
	exit;

$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado')
{
	$arch = LimpiarVariable($_POST["listado"]);
	
	EnviarArchivo('../listados/', $arch);
	
	exit;
}

EstaLogeado();

include ('header.php');


$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>

<form name=frmListadoAltasBajas action=listadoAltasBajas.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Generar Listado'){
	$FechaDesde = FechaWEB2SQL(LimpiarNumero($_POST["FechaDesde"]));
	$FechaHasta = FechaWEB2SQL(LimpiarNumero($_POST["FechaHasta"]));
	$selPeriodo = $_POST["selPeriodo"];
	#print $selPeriodo;
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
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
	$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"FechaEgreso\", em.\"TipoRelacion\", AVG(rem.\"Remuneracion\") AS \"Remuneracion\", ed.\"FechaNacimiento\", ed.\"CUIT\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"

LEFT JOIN (SELECT \"EmpresaID\", \"SucursalID\", \"Legajo\", \"Fecha\", \"NumeroLiquidacion\", SUM(CASE WHEN re.\"Haber1\" IS NULL THEN 0 ELSE re.\"Haber1\" END) + SUM(CASE WHEN re.\"ConceptoID\"=32 THEN re.\"Haber2\" ELSE 0 END) AS \"Remuneracion\" FROM \"tblRecibos\" re WHERE \"ConceptoID\" NOT IN (99) GROUP BY  \"EmpresaID\", \"SucursalID\", \"Legajo\", \"Fecha\", \"NumeroLiquidacion\") rem
ON ed.\"EmpresaID\" = rem.\"EmpresaID\" AND ed.\"SucursalID\" = rem.\"SucursalID\" AND ed.\"Legajo\" = rem.\"Legajo\" AND rem.\"Fecha\" >= '$FechaDesde' AND rem.\"Fecha\" <= '$FechaHasta'

WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND
em.\"FechaEgreso\" >= '$FechaDesde' AND em.\"FechaEgreso\" <= '$FechaHasta'
AND em.\"TipoRelacion\" <> 4
GROUP BY em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"FechaEgreso\", em.\"TipoRelacion\", ed.\"FechaNacimiento\",  ed.\"CUIT\"
") or die(pg_last_error());

	if (!$rs){
		exit;
	}

	//################################################################
	//################################################################
	//################################################################
	//################################################################	
	$fh = fopen('../listados/ListaAltaBaja.csv'.'_'.date("d-m-Y"),'w');

	// Procesamos bajas
	$CantBaja = 0;
	if (pg_numrows($rs)>0){
$divBajas .= "<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>\n";
$divBajas .= "<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Numero de Documento</th><th>CUIL</th><th>Plantel</th><th>Fecha de Egreso</th><th>Remuneracion</th><th>Fecha Nac.</th></tr>\n";
	while($row = pg_fetch_array($rs))
	{
		$CantBaja++;
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		$FechaIng = FechaSQL2WEB($row[4]);
		$FechaEgr = FechaSQL2WEB($row[5]);
		$Remu = $row[7];
		$FechaNac = FechaSQL2WEB($row[8]);
		$CUIT = $row[9];
		switch($row[6]){
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
$divBajas .= "<tr><td>$Legajo</td><td>$ApeyNom</td><td>$NroDoc</td><td>$CUIT</td><td>$TipoRel</td><td>$FechaEgr</td><td>$Remu</td><td>$FechaNac</td></tr>\n";
	}
$divBajas .= "</table><br>\n";
	}else{
$divBajas .= "<div class=alerta>No figuran empleados dados de baja</div><br>\n";
	}
	
$strBajas = str_replace('</td><td>',',',$divBajas);

$strBajas = str_replace('</table><br>','',$strBajas);
$strBajas = str_replace('<tr><td>','',$strBajas);
$strBajas = str_replace('</td></tr>','',$strBajas);


$strBajas = str_replace('</th><th>',',',$strBajas);
$strBajas = str_replace('<tr><th>','',$strBajas);
$strBajas = str_replace('</th></tr>','',$strBajas);
$strBajas = str_replace('<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>','',$strBajas);


	
	$sql = "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"TipoRelacion\", AVG(rem.\"Remuneracion\") AS \"Remuneracion\", ed.\"FechaNacimiento\",ed.\"CUIT\" 
FROM \"tblEmpleadosDatos\" ed
INNER JOIN \"tblEmpleados\" em
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"

LEFT JOIN (SELECT \"EmpresaID\", \"SucursalID\", \"Legajo\", \"Fecha\", \"NumeroLiquidacion\", SUM(CASE WHEN re.\"Haber1\" IS NULL THEN 0 ELSE re.\"Haber1\" END) + SUM(CASE WHEN re.\"ConceptoID\"=32 THEN re.\"Haber2\" ELSE 0 END) AS \"Remuneracion\" FROM \"tblRecibos\" re WHERE \"ConceptoID\" NOT IN (99) GROUP BY  \"EmpresaID\", \"SucursalID\", \"Legajo\", \"Fecha\", \"NumeroLiquidacion\") rem
ON ed.\"EmpresaID\" = rem.\"EmpresaID\" AND ed.\"SucursalID\" = rem.\"SucursalID\" AND ed.\"Legajo\" = rem.\"Legajo\" AND rem.\"Fecha\" >= '$FechaDesde' AND rem.\"Fecha\" <= '$FechaHasta'

WHERE ed.\"EmpresaID\" = $EmpresaID AND ed.\"SucursalID\" = $SucursalID AND
ed.\"FechaIngreso\" >= '$FechaDesde' AND ed.\"FechaIngreso\" <= '$FechaHasta'
AND em.\"TipoRelacion\" <> 4
GROUP BY em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"NumeroDocumento\", ed.\"FechaIngreso\", em.\"TipoRelacion\", ed.\"FechaNacimiento\",  ed.\"CUIT\"
";
	$rs = pg_query($db, $sql) or die(pg_last_error());
	if (!$rs){
		exit;
	}
	// Procesamos altas
	$CantAlta = 0;
	if (pg_numrows($rs)>0){
$divAltas .= "<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>\n";
$divAltas .= "<tr><th>Legajo</th><th>Apellido y Nombre</th><th>Numero de Documento</th><th>CUIL</th><th>Plantel</th><th>Fecha de Ingreso</th><th>Remuneracion</th><th>Fecha Nac.</th></tr>\n";
	while($row = pg_fetch_array($rs))
	{
		$CantAlta++;
		$ApeyNom = $row[2] . ' ' . $row[1];
		$NroDoc = $row[3];
		$Legajo = $row[0];
		$FechaIng = FechaSQL2WEB($row[4]);
		$Remu = $row[6];
		$FechaNac = FechaSQL2WEB($row[7]);
		$CUIT = $row[8];
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
$divAltas .= "<tr><td>$Legajo</td><td>$ApeyNom</td><td>$NroDoc</td><td>$CUIT</td><td>$TipoRel</td><td>$FechaIng</td><td>$Remu</td><td>$FechaNac</td></tr>\n";
	}
$divAltas .= "</table><br>\n";
	}else{
$divAltas .= "<div class=alerta>No figuran empleados dados de alta</div><br>\n";
	}

$strAltas = str_replace('</td><td>',',',$divAltas);
$strAltas = str_replace('</table><br>','',$strAltas);
$strAltas = str_replace('<tr><td>','',$strAltas);
$strAltas = str_replace('</td></tr>','',$strAltas);

$strAltas = str_replace('</th><th>',',',$strAltas);
$strAltas = str_replace('<tr><th>','',$strAltas);
$strAltas = str_replace('</th></tr>','',$strAltas);
$strAltas = str_replace('<table width=100% border=0 cellpadding=5 cellspacing=1 class=datagrid>','',$strAltas);

$strListCompleto = "-- ALTAS --\n ".$strAltas." \n \n \r -- BAJAS --\n \n ".$strBajas;

fwrite($fh, $strListCompleto);

fclose($fh);

copy('../listados/ListaAltaBaja.csv'.'_'.date("d-m-Y"), '../listados/ListaAltaBaja.csv');

?>


	<h1>Listado de altas y bajas de personal</h1>


<script>

		function BajarListado(sArch)
		{
			document.getElementById('accion').value = 'Bajar Listado';
			document.getElementById('listado').value = sArch;
			document.frmListadoAltasBajas.submit();
		}

</script>

	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>
    
    &nbsp;
    
   	<a class="tecla" href="javascript:BajarListado('ListaAltaBaja.csv'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Altas y Bajas</a> 

    
    <br>
    
<?
	print "<br><b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br>";
?>
	<br><b>Se Procesaron <?=$CantAlta?> Altas</b>
	<br><br><?=$divAltas?>
	<b>Se Procesaron <?=$CantBaja?> Bajas</b>
	<br><br><?=$divBajas?>
	<script>
		document.getElementById('divLoading').style.display = 'none';
	</script>
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
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Generar Listado"></TD></TR></table>
	<script>
		Fechas(document.getElementById('selPeriodo').value);
	</script>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>

