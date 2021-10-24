<?
require 'funcs.php';
?>
<Body>
<Cuerpo><?

if (!($db = Conectar()))
	exit;

$EmpresaID = 1;
$SucursalID = 1;
$LegajoNumerico = '1';
if ($LegajoNumerico == '1'){
	$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "re.\"Legajo\"";
}

$CentroCostos = LimpiarNumero($_GET["CentroCostos"]);
$TipoRelacion = LimpiarVariable($_GET["TipoRelacion"]);
$FechaPeriodo = LimpiarNumero2($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
$LegDesde = LimpiarNumero($_GET["LegDesde"]);
$LegHasta = LimpiarNumero($_GET["LegHasta"]);
$LP = LimpiarNumero($_GET["LP"]);

$sJoin = "INNER JOIN \"tblEmpleados\" em ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\"
AND em.\"Legajo\" = re.\"Legajo\" AND ";
if ($CentroCostos > 0)
	$sJoin .= "em.\"CentroCostos\" = $CentroCostos AND ";
if ($TipoRelacion > 0)
	$sJoin .= "em.\"TipoRelacion\" in ($TipoRelacion) AND ";
if ($LegajoNumerico == '1'){
	if ($LegDesde != '')
		$sJoin .= "to_number(em.\"Legajo\", '999999') >= $LegDesde AND ";
	if ($LegHasta != '')
		$sJoin .= "to_number(em.\"Legajo\", '999999') <= $LegHasta AND ";
}
$sJoin = substr($sJoin, 0, -5);

$rs = pg_query($db, "
SELECT count(DISTINCT re.\"Legajo\")
FROM \"tblRecibos\" re
$sJoin
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion");
if (!$rs){
	exit;
}
$row = pg_fetch_array($rs);
$Cantidad = $row[0];
if ($Cantidad > 0){
	$rs = pg_query($db, "
SELECT \"TipoLiquidacionID\", \"FechaPago\" FROM \"tblPeriodos\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID
AND \"FechaPeriodo\" = '$FechaPeriodo' AND \"NumeroLiquidacion\" = $NumeroLiquidacion");
	if (!$rs){
		exit;
	}
	$row = pg_fetch_array($rs);
	$TipoLiq = $row[0];
	$FechaDePago = $row[1];
	if ($FechaDePago == '')
		$FechaDePago = date("y-m-d");
	$FechaDePago = FechaSQL2WEB($FechaDePago);
	$rs = pg_query($db, "
SELECT $sqlLegajo, re.\"AliasID\", re.\"Descripcion\", re.\"Cantidad\", re.\"Haber1\", re.\"Haber2\", 
re.\"Descuento\", re.\"ConceptoID\", (CASE co.\"ClaseID\" WHEN 0 THEN 9 ELSE co.\"ClaseID\" END) AS \"Orden\",
ed.\"LugarPago\", lp.\"Descripcion\"
FROM \"tblRecibos\" re
INNER JOIN \"tblConceptos\" co
ON co.\"EmpresaID\" = re.\"EmpresaID\" AND co.\"ConceptoID\" = re.\"ConceptoID\" AND co.\"ImprimeEnRecibo\" = true
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblLugaresDePago\" lp
ON lp.\"EmpresaID\" = re.\"EmpresaID\" AND lp.\"LugarPago\" = ed.\"LugarPago\" AND lp.\"Activo\" = true
$sJoin
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID
AND re.\"Legajo\" = em.\"Legajo\" AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
ORDER BY ".($LP == '1'?"ed.\"LugarPago\",":"")."1, \"Orden\", re.\"ConceptoID\", re.\"Descripcion\"");
	if (!$rs){
		exit;
	}
	$Neto = 0;
	$TotalNeto = 0;
	$TotalNetoGeneral = 0;
	$AntLegajo = '';
	$AntLP = '';
	while($row = pg_fetch_array($rs)){
		$Legajo = $row[0];
		$LugarPago = $row[9];
		if ($LP == '1'){
			if ($AntLP == ''){
				$AntLP = $LugarPago;
				$LPDesc = $row[10];
			}
			if ($AntLP != $LugarPago){
				/*print str_pad("TOTAL NETO ($LPDesc): " . $TotalNeto, 130, ' ') . "\n";
				$LineaN = 2;
				while($LineaN < 36){
					print "\n";
					$LineaN++;
				}*/
				$TotalNeto = 0;
				$LPDesc = $row[10];
				$AntLP = $LugarPago;
			}
		}
		if ($Legajo != $AntLegajo){
			if ($AntLegajo != '') {

				print "\n\n\n\n\n";
				print $Linea1;
				print "\n\n\n\n";
				print $Linea2;
				print "\n".$FechaIngreso;
				print "\n\n\n\n";
				print $Linea3;
				print "\n\n\n\n";
				print $Linea4a;
				print "\n";
				print $Linea4;
				print "\n\n\n\n\n\n\n\n\n\n\n\n";
				print $Linea1;
				print "\n\n\n\n";
				print $Linea2;
				print "\n".$FechaIngreso;
				print "\n\n\n\n\n";
				print $LineaConceptos;
				$cant = explode("\n", $LineaConceptos);
				for($i=0; $i<(34-count($cant));$i++)
					print "\n";
				print $Linea5;
				print "\n\n\n\n\n";
				print $Linea3;
				print "\n\n\n\n";
				print $Linea4;
				print "\n<!--SaltoDePagina-->";

			}
			$AntLegajo = $Legajo;
			$rs1 = pg_query($db, "
SELECT em.\"Legajo\", em.\"Apellido\" || ' ' || em.\"Nombre\",
    (SELECT round((\"Haber1\" + \"Haber2\" - \"Descuento\")::numeric, 2) FROM \"tblRecibos\" re
    WHERE re.\"EmpresaID\" = em.\"EmpresaID\" AND re.\"SucursalID\" = em.\"SucursalID\" AND re.\"Legajo\" = em.\"Legajo\" 
	AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion AND re.\"ConceptoID\" = 99
    ) AS s1, trunc(\"AntiguedadEmpleado2\"(em.\"EmpresaID\", em.\"SucursalID\", em.\"Legajo\", '$FechaPeriodo'))
	AS \"Antiguedad\", CASE WHEN ce.\"Categoria\" IS NULL THEN ca.categoria::VARCHAR ELSE ce.\"Categoria\" END AS categoria, CASE WHEN ce.\"Categoria\" IS NULL THEN ca.detalle ELSE ce.\"Grupo\"||' '||ce.\"Categoria\" END AS \"Cargo\", '$FechaPeriodo'::date,
	(SELECT ed.\"NumeroCuenta\" FROM \"tblEmpleadosDatos\" ed
	WHERE ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
	AND ed.\"LugarPago\" in (2,3,4,5)
	) AS s2, ed.\"FechaIngreso\", ed.\"CUIT\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosRafam\" er
ON em.\"EmpresaID\" = er.\"EmpresaID\" AND em.\"SucursalID\" = er.\"SucursalID\" AND em.\"Legajo\" = er.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" ed
ON em.\"EmpresaID\" = ed.\"EmpresaID\" AND em.\"SucursalID\" = ed.\"SucursalID\" AND em.\"Legajo\" = ed.\"Legajo\"
LEFT JOIN owner_rafam.cargos ca
ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND
er.categoria = ca.categoria AND er.cargo = ca.cargo
LEFT JOIN \"tblCategoriasEmpleado\" ce ON em.\"Legajo\" = ce.\"Legajo\"
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"Legajo\" = '$Legajo'
");
			if (!$rs1){
				exit;
			}
			$row1 = pg_fetch_array($rs1);
			$ApeYNom = $row1[1];
			$Neto = $row1[2];
			$Antig = $row1[3];
			$Cat = $row1[4];
			$Cargo = substr($row1[5], 0, 25);
			$dAno = substr($row1[6], 0, 4);
			$dMes = substr($row1[6], 5, 2);
			$CUIT = $row1[9];
			$Fecha = MesCorto(intval($dMes)) . " $dAno";
			if ($TipoLiq == 5){
				$Linea4a = str_repeat(' ', 26) . '(1ra Quincena)';
			}else if ($TipoLiq == 7){
				$Linea4a = str_repeat(' ', 26) . '(2da Quincena)';
			}
			$NumeroCuenta = $row1[7];
			$FechaIngreso = str_repeat(' ', 27) . FechaSQL2WEB($row1[8]);
			if ($NumeroCuenta != '')
				$NumeroCuenta = substr($row1[7], 0, -1) . '/' . substr($row1[7], -1, 1);

			$Linea1 = str_repeat(' ', 28) . str_pad($Legajo, 10, ' ', STR_PAD_LEFT) . str_pad($ApeYNom, 37, ' ', STR_PAD_LEFT) . '  ' . str_pad($CUIT, 11, ' ', STR_PAD_LEFT);
			$Linea2 = str_repeat(' ', 28) . str_pad($Antig, 10, ' ', STR_PAD_LEFT) . str_pad($Cat, 14, ' ', STR_PAD_LEFT) . '   ' . $Cargo;
			$Linea4 = str_repeat(' ', 27) . $Fecha . ' ' . $FechaDePago . '  ' . $NumeroCuenta;
			$LineaConceptos = '';
			$TotalNeto += $Neto;
			$TotalNetoGeneral += $Neto;
		} // END IF

		$Alias = $row[1];
		$Descripcion = $row[2];
		$Cantidad = $row[3];
		$Haber1 = $row[4];
		$Haber2 = $row[5];
		$Descuento = $row[6];
		$ConceptoID = $row[7];
		if ($Haber1 != '')
			$Haber1 = round($Haber1, 2);
		if ($Haber2 != '')
			$Haber2 = round($Haber2, 2);
		if ($Descuento != '')
			$Descuento = round($Descuento, 2);
		if ($Haber1 != '')
			$Haber = $Haber1;
		else
			$Haber = $Haber2;
		if ($ConceptoID == 99){
			if ($NumeroCuenta == ''){
				$NCuenta = '';
			}else{
				$NCuenta = "Acreditado en caja de ahorro numero $NumeroCuenta";
			}
			$Linea3 = str_repeat(' ', 26) .  str_pad($Haber1, 13, ' ', STR_PAD_LEFT) . str_pad($Descuento, 15, ' ', STR_PAD_LEFT) . str_pad($Haber2, 15, ' ', STR_PAD_LEFT) . str_pad($Neto, 15, ' ', STR_PAD_LEFT);
			if ($Neto <= 0){
				$Sueldo = 'Cero Pesos Con 0/100 Centavos';
			}else{
				$iPos = strpos($Neto, '.');
				if ($iPos !== false){
					$Sueldo = NumeroALetras(substr($Neto, 0, $iPos));
					$Sueldo .= ' Pesos Con ' . substr($Neto, $iPos+1) . '/100 Centavos';
				}else{
					$Sueldo = NumeroALetras($Neto);
					$Sueldo .= ' Pesos Con 00/100 Centavos';
				}
			}
			$Linea5 = str_repeat(' ', 27) . $Sueldo;
		}else{
			$LineaConceptos .= str_repeat(' ', 25) .  str_pad($Alias, 5, ' ', STR_PAD_LEFT) . '    ' . str_pad($Descripcion, 29, ' ', STR_PAD_RIGHT) . str_pad($Cantidad, 2, ' ', STR_PAD_LEFT) . str_pad(FormatearImporte($Haber), 10, ' ', STR_PAD_LEFT) . str_pad(FormatearImporte($Descuento), 10, ' ', STR_PAD_LEFT) . "\n";
		}
	} // END WHILE
	print "\n\n\n\n\n";
	print $Linea1;
	print "\n\n\n\n";
	print $Linea2;
	print "\n".$FechaIngreso;
	print "\n\n\n\n";
	print $Linea3;
	print "\n\n\n\n";
	print $Linea4a;
	print "\n";
	print $Linea4;
	print "\n\n\n\n\n\n\n\n\n\n\n\n";
	print $Linea1;
	print "\n\n\n\n";
	print $Linea2;
	print "\n".$FechaIngreso;
	print "\n\n\n\n\n";
	print $LineaConceptos;
	$cant = explode("\n", $LineaConceptos);
	for($i=0; $i<(34-count($cant));$i++)
		print "\n";
	print $Linea5;
	print "\n\n\n\n\n";
	print $Linea3;
	print "\n\n\n\n";
	print $Linea4;
	print "\n<!--SaltoDePagina-->\n";
/*	if ($LP == '1')
		print str_pad("TOTAL NETO ($LPDesc): " . $TotalNeto, 130, ' ') . "\n";
	print "\n\n\n\n\n";
	print str_pad('TOTAL NETO GENERAL: ' . $TotalNetoGeneral, 130, ' ') . "\n";*/
}
?>
</Cuerpo>
</Body>
