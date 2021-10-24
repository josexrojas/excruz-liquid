<?
require_once('funcs.php');
$FechaPeriodo = LimpiarNumero($_GET["FechaPeriodo"]);
$NumeroLiquidacion = LimpiarNumero($_GET["NumeroLiquidacion"]);
$Fecha = substr($FechaPeriodo, 6, 2) . '-' . substr($FechaPeriodo, 4, 2) . '-' . substr($FechaPeriodo, 0, 4);
$Per = substr($FechaPeriodo, 4, 2) . '-' . substr($FechaPeriodo, 0, 4);
print "<Body>";
print "<Header>" . str_repeat('-', 110);
print "\nMunicipalidad de Exaltacion de la Cruz							Fecha: <!--Fecha-->\n";
print "Administracion de Personal										Pagina: <!--NumeroPagina-->\n";
print str_repeat('-', 110) . "\n\n";
print str_repeat(' ', 40) . "*** INFORME PARA ART ***" . str_repeat(' ', 30) . "\n\n";
print "MUNICIPALIDAD DE EXALTACION DE LA CRUZ   C.U.I.T. 33-99929598-9  Novedades de la nomina al $Fecha\n";
print "Periodo: $Per  Numero: $NumeroLiquidacion     Rivadavia 411   Capilla del Señor   Prov. Buenos Aires  CP: 2812\n\n";
print str_pad('Leg.', 4, ' ', STR_PAD_RIGHT);
print str_pad('Tipo', 4, ' ', STR_PAD_RIGHT);
print str_pad('Num.Doc.', 9, ' ', STR_PAD_RIGHT);
print str_pad('CUIL', 13, ' ', STR_PAD_RIGHT);
print str_pad('Apellido y Nombre', 30, ' ', STR_PAD_RIGHT);
print str_pad('Categoria-Cargo', 10, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Ing.', 10, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Nac.', 10, ' ', STR_PAD_RIGHT);
print str_pad('Se', 2, ' ', STR_PAD_RIGHT);
print str_pad('Remu ', 8, ' ', STR_PAD_LEFT);
print str_pad('$0,60', 5, ' ', STR_PAD_RIGHT);
print str_pad('2,16%', 5, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Egr.', 10, ' ', STR_PAD_RIGHT);
print "\n" . str_repeat('-', 110);
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
SELECT $sqlLegajo, ed.\"TipoDocumento\", ed.\"NumeroDocumento\", em.\"Apellido\", em.\"Nombre\", ca.denominacion as detalle, 
	ed.\"FechaIngreso\", ed.\"FechaNacimiento\", ed.\"Sexo\", round(re.\"Haber1\"::numeric, 2) AS \"Remuneracion\", 
	(SELECT round(re1.\"Aporte\"::numeric, 2)
	FROM \"tblRecibos\" re1 WHERE 
	re1.\"EmpresaID\" = re.\"EmpresaID\" AND re1.\"SucursalID\" = re.\"SucursalID\" AND re1.\"Fecha\" = re.\"Fecha\" AND 
	re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 32 AND re1.\"Legajo\" = re.\"Legajo\") AS s1,
	(SELECT round(re1.\"Aporte\"::numeric, 2)
	FROM \"tblRecibos\" re1 WHERE 
	re1.\"EmpresaID\" = re.\"EmpresaID\" AND re1.\"SucursalID\" = re.\"SucursalID\" AND re1.\"Fecha\" = re.\"Fecha\" AND 
	re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 31 AND re1.\"Legajo\" = re.\"Legajo\") AS s2,
	em.\"FechaEgreso\", ed.\"CUIT\"
FROM \"tblRecibos\" re 
INNER JOIN \"tblEmpleadosDatos\" ed 
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\" 
INNER JOIN \"tblEmpleados\" em 
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\" 
INNER JOIN \"tblEmpleadosRafam\" er 
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\" 
INNER JOIN owner_rafam.cargos ca 
ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento 
	AND ca.categoria = er.categoria AND ca.cargo = er.cargo 
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Fecha\" = '$FechaPeriodo' AND 
	re.\"NumeroLiquidacion\" = $NumeroLiquidacion AND re.\"ConceptoID\" = 99
ORDER BY 1
");
	if (!$rs){
		exit;
	}
	$TotalReg = 0;
	$TotalRem = 0;
	$TotalPCIA = 0;
	$TotalPCI2 = 0;
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$TipoDoc = $row[1];
		if ($TipoDoc == '2')
			$TipoDoc = 'CI';
		else if ($TipoDoc == '3')
			$TipoDoc = 'PAS';
		else if ($TipoDoc == '4')
			$TipoDoc = 'LE';
		else if ($TipoDoc == '5')
			$TipoDoc = 'LC';
		else
			$TipoDoc = 'DNI';
		$NumDoc = $row[2];
		$CUIL = $row[13];
		$Ape = $row[3];
		$Nom = $row[4];
		$Cargo = $row[5];
		$FechaIng = FechaSQL2WEB($row[6]);
		$FechaNac = FechaSQL2WEB($row[7]);
		$Sexo = $row[8];
		$Remuneracion = $row[9];
		$PciaART = $row[10];
		$PciaPor = $row[11];
		if ($PciaART == '')
			$PciaART = 0;
		if ($PciaPor == '')
			$PciaPor = 0;
		$FechaEgr = $row[12];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
		if (floatval($Remuneracion)>0){
			print str_pad($Legajo, 4, ' ', STR_PAD_RIGHT);
			print str_pad($TipoDoc, 4, ' ', STR_PAD_RIGHT);
			print str_pad($NumDoc, 9, ' ', STR_PAD_RIGHT);
			print str_pad($CUIL, 13, ' ', STR_PAD_RIGHT);
			print str_pad($ApeYNom, 30, ' ', STR_PAD_RIGHT);
			print str_pad(substr($Cargo, 0, 10), 10, ' ', STR_PAD_RIGHT);
			print str_pad($FechaIng, 10, ' ', STR_PAD_RIGHT);
			print str_pad($FechaNac, 10, ' ', STR_PAD_RIGHT);
			print str_pad($Sexo, 2, ' ', STR_PAD_RIGHT);
			print str_pad($Remuneracion, 7, ' ', STR_PAD_LEFT) . ' ';
			print str_pad($PciaART, 5, ' ', STR_PAD_RIGHT);
			print str_pad($PciaPor, 5, ' ', STR_PAD_LEFT);
			print str_pad($FechaEgr, 10, ' ', STR_PAD_RIGHT);
			print "\n";
			$TotalReg++;
			$TotalRem += $Remuneracion;
			$TotalPCIA += $PciaART;
			$TotalPCI2 += $PciaPor;
		}
	}
	if ($TotalReg > 0){
		print "\nTotal Remuneraciones: $TotalRem\n";
		print "Total PCIA ART $0.60: $TotalPCIA\n";
		print "Total MAPFRE 2,16%: $TotalPCI2\n";
		print "Cantidad de Legajos Procesados: $TotalReg\n";
	}

pg_close($db);
?>
</Cuerpo>
</Body>
