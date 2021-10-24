<?
require_once('funcs.php');
print "<Body>";
print "<Header>" . str_repeat('-', 90);
print "\nMunicipalidad de Exaltacion de la Cruz				Fecha: <!--Fecha-->\n";
print "Administracion de Personal						Pagina: <!--NumeroPagina-->\n\n";
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

$rs = pg_query($db, "
SELECT a.*, (case ed.\"LugarPago\" when 2 then '7134' when 3 then '7134' when 4 then '7135' when 5 then '7135' end) AS \"LugarPago\",
    ed.\"TipoCuenta\", ed.\"NumeroCuenta\"
FROM \"VerAdelantosPendientesRafam\"() a
INNER JOIN \"tblEmpleadosDatos\" ed
ON a.\"EmpresaID\" = ed.\"EmpresaID\" AND a.\"SucursalID\" = ed.\"SucursalID\" AND a.\"Legajo\" = ed.\"Legajo\"
AND ed.\"NumeroCuenta\" IS NOT NULL AND ed.\"LugarPago\" IN (2,3,4,5)
");
if (!$rs){
	exit;
}
	$TotalReg = 0;
	$TotalPesos = 0;
	while($row = pg_fetch_array($rs))
	{

		$LugarPago = $row['LugarPago'];
		$TipoCuenta = $row['TipoCuenta'];
		$NumeroCuenta = $row['NumeroCuenta'];
		$Legajo = $row['Legajo'];
		$Apellido = $row['Apellido'];
		$Nombre = $row['Nombre'];
		$Monto = $row['Monto'];
		$ApeYNom = trim(str_replace(',', ' ', $Apellido)) . ' ' . trim(str_replace(',', ' ', $Nombre));
		if ($Importe == '')
			$Importe = '0';
		$Importe = $Monto;

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
		print "TOTAL ADELANTOS CAPILLA : ".($TotalPesosSucursales{'7134'}==''?0:$TotalPesosSucursales{'7134'})."\n";
		print "TOTAL ADELANTOS CARDALES: ".($TotalPesosSucursales{'7135'}==''?0:$TotalPesosSucursales{'7135'})."\n";
	}

pg_close($db);
?>
</Cuerpo>
</Body>
