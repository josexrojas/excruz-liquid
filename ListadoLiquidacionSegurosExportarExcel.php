
<?
require 'funcs.php';

if (!($db = Conectar()))
	exit;

$Jur  = LimpiarNumero($_GET["Jur"]);
$TR   = LimpiarNumero($_GET["TR"]);
$dAno = LimpiarNumero($_GET["Ano"]);
$dMes = LimpiarNumero($_GET["Mes"]);
$TipoRelacion = LimpiarVariable($_GET["TipoRelacion"]);

$EmpresaID = 1;
$SucursalID = 1;

?>

<?
header('Content-type: application/vnd.ms-excel');
header("Content-Disposition: attachment; filename=ListadoLiquidacionSeguros.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>

<H1>Listado de Liquidacion Poliza Seguro</H1>

<?
	$rs = pg_query($db, "
SELECT DISTINCT to_number(re.\"Legajo\", '99999'), em.\"Nombre\", em.\"Apellido\", ed.\"FechaNacimiento\"
,ed.\"TipoDocumento\", ed.\"NumeroDocumento\",em.\"TipoRelacion\",
CASE
  WHEN SUM(CASE WHEN re.\"ConceptoID\"  IN (1, 10) THEN re.\"Haber1\"  ELSE 0 END) < 536.48 THEN 536.48
  WHEN SUM(CASE WHEN re.\"ConceptoID\"  IN (1, 10) THEN re.\"Haber1\"  ELSE 0 END) > 3025.75 THEN 3025.75
  ELSE SUM(CASE WHEN re.\"ConceptoID\"  IN (1, 10) THEN re.\"Haber1\"  ELSE 0 END)
END AS \"SueldoBruto\",
to_char(SUM(CASE WHEN re.\"AliasID\" IN (114, 33) THEN re.\"Descuento\" ELSE 0 END) / 0.000466, '999999D99') AS \"Capital\",
SUM(CASE WHEN re.\"AliasID\" IN (114, 33) THEN re.\"Descuento\" ELSE 0 END)  as \"PrimaAbonada\",
SUM(CASE WHEN re.\"AliasID\" IN (34) THEN re.\"Descuento\" ELSE 0 END)  as \"PrimaConyuge\" 
FROM \"tblRecibos\" re 
INNER JOIN  \"tblEmpleados\" em ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')." 
INNER JOIN \"tblEmpleadosDatos\" ed ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\" 
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
re.\"Fecha\" = '$dAno-$dMes-01' 
GROUP BY 1, 2, 3, 4, 5, 6, 7 
ORDER BY 1
");
	if (!$rs){
		exit;
	}
	$Jurisdiccion = '';
	$AntJur = '';
	$TipoRel = '';
	$AntRel = '';
	$Abrir = 1;
	$CantEmp = 0;
	$CantGEmp = 0;
	print "<b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br><br>";

	while($row = pg_fetch_array($rs))
	{
		$CantEmp++;
		$CantGEmp++;
		$Legajo = $row[0];
		$ApeYNom = trim($row[2] . ', ' . $row[1]);
		
		$TipoDoc = $row[4];
		if ($TipoDoc == '2')
			$TipoDoc = 'C.I.';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'L.E.';
		else if ($TipoDoc == '5')
			$TipoDoc = 'L.C.';
		else
			$TipoDoc = 'D.N.I.';
			
		$TipoNroDni = trim($TipoDoc . ', ' . $row[5]);
		$FechaNacimiento = $row[3];
		$SueldoBruto = $row[7];
		$Capital = $row[8];
		$PrimaAbonada = $row[9];
		$Conyuge = $row[10];
				
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
		
		$i = 10;
		if ($Jur == '1')
			$Jurisdiccion = $row[$i++];
		$Car = $row[$i++];

		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			if ($AntRel != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
			$CantEmp--;
			print "</table><br>\n";
			print "<b>Cantidad de empleados activos: $CantEmp</b><br><br>\n";
			$CantEmp = 1;
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			print "<b>Jurisdicci&oacute;n: " . Jurisdiccion($db, $Jurisdiccion) . "</b><br><br>";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			print "<b>Tipo De Relaci&oacute;n: $TipoRel</b><br><br>";
			$AntRel = $TipoRel;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
			<tr><th>Legajo</th><th>Apellido y Nombre</th><th>DNI</th><th>Fecha Nacimiento</th><th>Sueldo Sujeto a desc.</th><th>Capital</th><th>Prima</th><th>Conyuge</th></tr>
<?
		}
?>
		<tr><td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$TipoNroDni?></td><td><?=$FechaNacimiento?></td><td><?=$SueldoBruto?></td><td><?=$Capital?></td><td><?=$PrimaAbonada?></td><td><?=$Conyuge?></td></tr><?
	}
	print "</table><br>\n";
	print "<b>Cantidad de empleados activos: $CantEmp</b><br>\n";
	print "<br><b>Cantidad Total de empleado activos: $CantGEmp</b><br>";
?>

