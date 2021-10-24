<?
require_once('funcs.php');
$FechaPeriodo = LimpiarNumero2($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
print "<Body>";
print "<Header>" . str_repeat('-', 90);
print "\nMunicipalidad de Exaltacion de la Cruz				Fecha: <!--Fecha-->\n";
print "Administracion de Personal						Pagina: <!--NumeroPagina-->\n\n";
print "Periodo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
print "                 Numero de Liquidacion: $NumeroLiquidacion\n";
print str_repeat('-', 90) . "\n";
print str_pad('Sucursal Benef.', 16, ' ', STR_PAD_RIGHT);
print str_pad('Cuenta y Digito', 16, ' ', STR_PAD_RIGHT);
print str_pad('Codigo Op.', 11, ' ', STR_PAD_RIGHT);
print str_pad('Importe', 11, ' ', STR_PAD_LEFT) . '  ';
print str_pad('Referencia', 13, ' ', STR_PAD_RIGHT);
print str_pad('Rubro Cuenta', 14, ' ', STR_PAD_RIGHT);
print "\n" . str_repeat('-', 90);
print "</Header>\n";
print "<Cuerpo>";

if (!($db = Conectar()))
	exit;

$EmpresaID = 1;
$SucursalID = 1;
if ('1' == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

$rs = pg_query($db, "
SELECT (case ed.\"LugarPago\" when 2 then '7134' when 3 then '7134' when 4 then '7135' when 5 then '7135' end),
    ed.\"TipoCuenta\", ed.\"NumeroCuenta\", $sqlLegajo, em.\"Apellido\", em.\"Nombre\",
    round(SUM(re.\"Haber1\"+re.\"Haber2\"-re.\"Descuento\")::numeric, 2) AS \"Neto\"
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"FechaEgreso\" IS NULL
INNER JOIN \"tblEmpleadosDatos\" ed
ON em.\"EmpresaID\" = ed.\"EmpresaID\" AND em.\"SucursalID\" = ed.\"SucursalID\" AND em.\"Legajo\" = ed.\"Legajo\"
AND ed.\"NumeroCuenta\" IS NOT NULL AND ed.\"LugarPago\" IN (2,3,4,5)
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Legajo\" = em.\"Legajo\" AND
re.\"ConceptoID\" = 99 AND re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY 1,2,3,4,5,6
ORDER BY 1,4
");
if (!$rs){
	exit;
}
	$TotalReg = 0;
	$TotalPesos = 0;
	while($row = pg_fetch_array($rs))
	{
		$LugarPago = $row[0];
		$TipoCuenta = $row[1];
		$NumeroCuenta = $row[2];
		$Legajo = $row[3];
		$SueldoDep = $row[6];
		if ($SueldoDep == '')
			$SueldoDep = '0';
		$Importe = $SueldoDep;

		if ($Importe > 0){
			$TotalPesosSucursales{$LugarPago} += $Importe;
			$TotalRegs++;
			$TotalPesos+=$Importe;
			print str_pad($LugarPago, 16, ' ', STR_PAD_BOTH);
			print str_pad($NumeroCuenta, 16, ' ', STR_PAD_BOTH);
			print str_pad('????????', 11, ' ', STR_PAD_BOTH);
			print str_pad(FormatearImporte($Importe), 11, ' ', STR_PAD_LEFT) . '  ';
			print str_pad($Legajo, 13, ' ', STR_PAD_BOTH);
			print str_pad($TipoCuenta, 14, ' ', STR_PAD_BOTH);
			print "\n";
		}
	}
	$i = strpos($TotalPesos, '.');
	if ($i === false){
		$TotalP = str_pad($TotalPesos, 12, '0', STR_PAD_LEFT) . '00';
	}else{
		$decimal = substr($TotalPesos . '00', $i+1, 2);
		$TotalP = substr($TotalPesos, 0, $i);
		$TotalP = str_pad($TotalP, 12, '0', STR_PAD_LEFT) . $decimal;
	}
	print "\n";
	if ($TotalRegs == 0)
		print "No hay pagos a realizar para la liquidacion seleccionada";
	else{
		print "\nCantidad de Registros Procesados: $TotalRegs\n";
		print "Importe Involucrado en Pesos: $TotalPesos\n";
		print "TOTAL SUELDOS CAPILLA : ".($TotalPesosSucursales{'7134'}==''?0:$TotalPesosSucursales{'7134'})."\n";
		print "TOTAL SUELDOS CARDALES: ".($TotalPesosSucursales{'7135'}==''?0:$TotalPesosSucursales{'7135'})."\n";
	}

pg_close($db);
?>
</Cuerpo>
</Body>
