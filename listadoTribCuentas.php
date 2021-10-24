<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

include("header.php");


function conectarBase($anio,$mes){
if ($anio == date('Y') && $mes == date('m')) $anio = $mes = '';

/*
echo '</br>';
echo '</br>';
echo '</br>';

echo $anio . $mes;
*/
return pg_connect("dbname=Sueldos" .  $anio .  $mes . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
}

function montar($anio,$mes){

	if($mes==12){
		$anio_sig = $anio + 1;

					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-01-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";

					$resultado = system($comando,$error);

					if(!$resultado){
					return $resultado;
					}else{
					return conectarBase($anio,$mes);
					}
	}else{
				if($mes == 9 || $mes == 10 || $mes == 11){
					$mes_sig  = $mes + 1;
					$anio_sig = $anio;

					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-" . $mes_sig . "-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";
echo  $comando;
				  $resultado = system($comando,$error);

					if(!$resultado){
					return $db;
					}else{
					return conectarBase($anio,$mes);
					}
				}else{
					$mes_sig  = $mes + 1;
					$anio_sig = $anio;

					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-0" . $mes_sig . "-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";

					$resultado = system($comando,$error);

					if(!$resultado){
					return $db;
					}else{
					return conectarBase($anio,$mes);
					}
				}
    }
}

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(ed.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "ed.\"Legajo\"";
}

?>

<script>
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
	}
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>

<form name=frmListadoUDF1 method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));

echo '<br';
echo '<br';
echo '<br';
/*
echo '<br';
echo '<br';
echo '<br';
echo 'periodo' . $FechaPeriodo;
echo 'liquidacion' . $NumeroLiquidacion;
*/

	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
//echo 'sali';
		exit;
	}

	$AnioPeriodo=((substr($FechaPeriodo, 0, 4)));
	$MesPeriodo =((substr($FechaPeriodo, 5, 2)));
//echo 'periodo' . $FechaPeriodo;
//echo 'liquidacion' . $NumeroLiquidacion;
//	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";
$db2 = conectarBase($AnioPeriodo,$MesPeriodo);

	if (!$db2){
//echo 'funcion montar';
    //NO FUNCIONA montar()
		$db2 = montar($AnioPeriodo,$MesPeriodo);

			if(!$db2){
			?>
				 <script language="JavaScript">
						alert('Error al buscar datos');
						window.history.back();
				</script>
			<?
			}
	}
/*
echo 'anio y periodo' . $AnioPeriodo . $MesPeriodo;
echo 'numero liqui:' . $NumeroLiquidacion;
*/
 //print("

	$rs = pg_query($db2, "
SELECT
$sqlLegajo,
e.\"Nombre\" || ' ' || e.\"Apellido\" as \"Nombre\",
ed.\"Sexo\",
CASE WHEN ed.\"EstadoCivil\" = 1 THEN 'Soltero/a' WHEN ed.\"EstadoCivil\" = 2 THEN 'Casado/a' WHEN ed.\"EstadoCivil\" = 3 THEN 'Viudo/a' WHEN ed.\"EstadoCivil\" = 4 THEN 'Divorciado/a' ELSE '' END AS \"EstadoCivil\",
CASE WHEN ed.\"TipoDocumento\" = 1 THEN 'DNI' WHEN ed.\"TipoDocumento\" = 2 THEN 'CI' WHEN ed.\"TipoDocumento\" = 3 THEN 'PASAPORTE' WHEN ed.\"TipoDocumento\" = 4 THEN 'LE' WHEN ed.\"TipoDocumento\" = 5 THEN 'LC' ELSE '' END AS \"TipoDocumento\",
ed.\"NumeroDocumento\",
ed.\"CUIT\",
ed.\"FechaNacimiento\",
CASE WHEN er.\"TipoDePlanta\" = 1 THEN 'Permanente' WHEN er.\"TipoDePlanta\" = 2 THEN 'Contratado' WHEN er.\"TipoDePlanta\" = 3 THEN 'Locacion de obra' ELSE '' END AS \"TipoDePlanta\",
ju.denominacion AS \"Jurisdiccion\",
cat.detalle as \"Categoria\",
ca.detalle as \"Cargo\",
MAX(\"Haber1\") AS \"ConAportes\",
MAX(\"Haber2\") AS \"SinAportes\",
MAX(\"Descuento\") AS \"Descuento\",
MAX(\"Haber1\") + MAX(\"Haber2\") - MAX(\"Descuento\") AS \"Neto\",
CASE WHEN MIN(ea.\"FechaDesde\") IS NULL THEN MIN(ed.\"FechaIngreso\") ELSE MIN(ea.\"FechaDesde\") END AS \"FechaAntiguedad\"
FROM \"tblRecibos\" r
INNER JOIN \"tblEmpleados\" e ON r.\"EmpresaID\" = e.\"EmpresaID\" AND r.\"SucursalID\" = e.\"SucursalID\" AND r.\"Legajo\" = e.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" ed ON e.\"EmpresaID\" = ed.\"EmpresaID\" AND e.\"SucursalID\" = ed.\"SucursalID\" AND e.\"Legajo\" = ed.\"Legajo\"
INNER JOIN \"tblEmpleadosRafam\" er ON e.\"EmpresaID\" = er.\"EmpresaID\" AND e.\"SucursalID\" = er.\"SucursalID\" AND e.\"Legajo\" = er.\"Legajo\"
LEFT JOIN owner_rafam.jurisdicciones ju ON ju.jurisdiccion = er.jurisdiccion
LEFT JOIN owner_rafam.agrupamientos ag ON substr(ag.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ag.agrupamiento = er.agrupamiento
LEFT JOIN owner_rafam.categorias cat ON substr(cat.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND cat.agrupamiento = er.agrupamiento AND cat.categoria = er.categoria
LEFT JOIN owner_rafam.cargos ca ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento AND ca.categoria = er.categoria AND ca.cargo = er.cargo
LEFT JOIN owner_rafam.fuen_fin ff ON er.codigo_ff = ff.codigo_ff
LEFT JOIN \"tblEmpleadosAntecedentes\" ea ON e.\"EmpresaID\" = ea.\"EmpresaID\" AND e.\"SucursalID\" = ea.\"SucursalID\" AND e.\"Legajo\" = ea.\"Legajo\" AND ea.\"ReconoceAntiguedad\" = 't'

WHERE r.\"EmpresaID\" = $EmpresaID AND r.\"SucursalID\" = $SucursalID AND r.\"Fecha\" = '$FechaPeriodo'
AND r.\"NumeroLiquidacion\" = '$NumeroLiquidacion' AND r.\"ConceptoID\" IN (99)

GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12

ORDER BY 1

");

/*
echo 'test:';
$row = pg_fetch_array($rs);
print_r($row);
*/
	if (!$rs){
//echo 'sali';
		exit;
	}
	$Fecha = date("d-m-Y");
	$Hora = date("Hi");

?>
<H1>Listado de Personal</H1>
<?
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";


?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>Legajo</th><th>Empleado</th><th>Sexo</th><th>Estado Civil</th><th>Documento</th><th>CUIL</th><th>F. Nacimiento</th><th>F. Antiguedad</th><th>Tipo de Planta</th><th>Jurisdiccion, Categoria y Cargo</th><th>Con Aportes</th><th>Sin Aportes</th><th>Descuentos</th><th>Neto</th><tr>

<?

	$TotalConAportes = 0;
	$TotalSinAportes = 0;
	$TotalDescuentos = 0;
	$TotalNeto = 0;
	$CantEmp = 0;
	while($row = pg_fetch_array($rs))
	{
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$row['Nombre']?></td><td><?=$row['Sexo']?></td><td><?=$row['EstadoCivil']?></td><td><?=$row['TipoDocumento'].$row['NumeroDocumento']?></td><td><?=$row['CUIT']?></td><td><?=$row['FechaNacimiento']?></td><td><?=$row['FechaAntiguedad']?></td><td><?=$row['TipoDePlanta']?></td><td><?=$row['Jurisdiccion']?> - <?=$row['Categoria']?> - <?=$row['Cargo']?></td><td><?=number_format($row['ConAportes'], 2, '.', '')?></td><td><?=number_format($row['SinAportes'], 2, '.', '')?></td><td><?=number_format($row['Descuento'], 2, '.', '')?></td><td><?=number_format($row['Neto'], 2, '.', '')?></td></tr>
<?
		$TotalConAportes += $row['ConAportes'];
		$TotalSinAportes += $row['SinAportes'];
		$TotalDescuentos += $row['Descuento'];
		$TotalNeto += $row['Neto'];
		$CantEmp++;
	}

	print "</table>\n";
	print "<br><b>Cantidad de Legajos Procesados: $CantEmp</b><br>\n";
	print "<br><b>Total Con Aportes: $TotalConAportes</b><br>\n";
	print "<br><b>Total Sin Aportes: $TotalSinAportes</b><br>\n";
	print "<br><b>Total Descuentos: $TotalDescuentos</b><br>\n";
	print "<br><b>Total Neto: $TotalNeto</b><br>\n";
}

if ($accion == ''){
	include 'selLiquida.php'; ?>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Ver Listado"></TD></TR></table>
<?
}
pg_close($db2);
pg_close($db);

?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>
