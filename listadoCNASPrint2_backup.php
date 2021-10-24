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
print str_repeat(' ', 40) . "*** LISTADO DE SEGURO DE VIDA ***\n";
print "Periodo: $Per  Numero De Liquidacion: $NumeroLiquidacion\n\n";
print str_pad('Leg.', 4, ' ', STR_PAD_RIGHT);
print str_pad('Apellido y Nombre', 30, ' ', STR_PAD_RIGHT);
print str_pad('Tipo', 6, ' ', STR_PAD_RIGHT);
print str_pad('Num.Doc.', 9, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Ing.', 12, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Nac.', 12, ' ', STR_PAD_RIGHT);
print str_pad('Sueldo+Antig', 13, ' ', STR_PAD_RIGHT);
print str_pad('Prima Seg.', 10, ' ', STR_PAD_RIGHT);
print str_pad('Conyuge', 8, ' ', STR_PAD_RIGHT);
print str_pad('Fecha Egr.', 12, ' ', STR_PAD_RIGHT);
print "\n" . str_repeat('-', 110);
print "</Header>\n";
print "<Cuerpo>";

if (!($db = Conectar()))
	exit;

$EmpresaID = 1;
$SucursalID = 1;
if ('1' == '1'){
	$sqlLegajo = "to_number(ed.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "ed.\"Legajo\"";
}

	$rs = pg_query($db, "
SELECT $sqlLegajo, ed.\"TipoDocumento\", ed.\"NumeroDocumento\", em.\"Apellido\", em.\"Nombre\",
    ed.\"FechaIngreso\", ed.\"FechaNacimiento\", round(sum(re.\"Haber1\")::numeric, 2) AS \"Remuneracion\",
    (SELECT round(re1.\"Descuento\"::numeric, 2) FROM \"tblRecibos\" re1
    WHERE re.\"EmpresaID\" = re1.\"EmpresaID\" AND re.\"SucursalID\" = re1.\"SucursalID\" AND re.\"Fecha\" = re1.\"Fecha\"
    AND re.\"NumeroLiquidacion\" = re1.\"NumeroLiquidacion\" AND re1.\"AliasID\" in (33, 114) AND re1.\"Legajo\" = re.\"Legajo\"
    ) AS s1,
	(SELECT round(re1.\"Descuento\"::numeric, 2) FROM \"tblRecibos\" re1
    WHERE re.\"EmpresaID\" = re1.\"EmpresaID\" AND re.\"SucursalID\" = re1.\"SucursalID\" AND re.\"Fecha\" = re1.\"Fecha\"
    AND re.\"NumeroLiquidacion\" = re1.\"NumeroLiquidacion\" AND re1.\"AliasID\" = 34 AND re1.\"Legajo\" = re.\"Legajo\"
    ) AS s2, em.\"FechaEgreso\"
FROM \"tblRecibos\" re
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = re.\"EmpresaID\" AND ed.\"SucursalID\" = re.\"SucursalID\" AND ed.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Fecha\" = '$FechaPeriodo'
AND re.\"NumeroLiquidacion\" = '$NumeroLiquidacion' AND re.\"ConceptoID\" in (1,2,10)
GROUP BY 1, 2, 3, 4, 5, 6, 7, 9, 10, 11
ORDER BY 1
");
	if (!$rs){
		exit;
	}
	$TotalReg = 0;
	$TotalRem = 0;
	$TotalPrima = 0;
	$Detalle = '';
	while($row = pg_fetch_array($rs))
	{
		$Legajo = $row[0];
		$TipoDoc = $row[1];
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
		$NumDoc = $row[2];
		$Ape = $row[3];
		$Nom = $row[4];
		$FechaIng = FechaSQL2WEB($row[5]);
		$FechaNac = FechaSQL2WEB($row[6]);
		$Remuneracion = $row[7];
		$Prima = $row[8];
		if ($Prima == '')
			$Prima = '0';
		$Conyuge = $row[9];
		if ($Conyuge == '')
			$Conyuge = '0';
		$FechaEgr = $row[10];
		$ApeYNom = trim(str_replace(',', ' ', $Ape)) . ' ' . trim(str_replace(',', ' ', $Nom));
		print str_pad($Legajo, 4, ' ', STR_PAD_RIGHT);
		print str_pad($ApeYNom, 30, ' ', STR_PAD_RIGHT);
		print str_pad($TipoDoc, 6, ' ', STR_PAD_RIGHT);
		print str_pad($NumDoc, 9, ' ', STR_PAD_RIGHT);
		print str_pad($FechaIng, 12, ' ', STR_PAD_RIGHT);
		print str_pad($FechaNac, 12, ' ', STR_PAD_RIGHT);
		print str_pad($Remuneracion, 12, ' ', STR_PAD_LEFT) . ' ';
		print str_pad($Prima, 9, ' ', STR_PAD_LEFT) . ' ';
		print str_pad($Conyuge, 7, ' ', STR_PAD_LEFT) . ' ';
		print str_pad($FechaEgr, 12, ' ', STR_PAD_RIGHT);
		print "\n";
		$TotalReg++;
		$TotalRem += $Remuneracion;
		$TotalPrima += $Prima;
		$TotalCony += $Conyuge;
	}
	if ($TotalReg > 0){
		print "\nTotal Sueldo+Antig: $TotalRem\n";
		print "Total Prima Seg.: $TotalPrima\n";
		print "Total Conyuge: $TotalCony\n";
		print "Cantidad de Legajos Procesados: $TotalReg\n";
	}

pg_close($db);
?>
</Cuerpo>
</Body>
