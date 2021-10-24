<?
require 'funcs.php';

if (!($db = Conectar()))
	exit;

$Jur = LimpiarNumero($_GET["Jur"]);
$TR = LimpiarNumero($_GET["TR"]);
$dAno = LimpiarNumero($_GET["Ano"]);
$dMes = LimpiarNumero($_GET["Mes"]);
$TipoRelacion = LimpiarVariable($_GET["TipoRelacion"]);

$EmpresaID = 1;
$SucursalID = 1;

print "<Body>\n";
print "<Header><!--CancelarModoComprimido-->" . str_repeat('-', 82);
print "\nMunicipalidad de Exaltacion de la Cruz               Fecha: <!--Fecha-->\n";
print "Administracion de Personal                             Pagina:   <!--NumeroPagina-->\n";
print str_repeat('-', 120) . "\n\n";
print str_repeat(' ', 22) . "*** LISTADO DE PERSONAL LIQUIDADO ***\n\n";
print "Periodo: " . Mes($dMes) . " de $dAno\n\n";
print "</Header><Cuerpo>";

if (strlen($dMes) < 2)
	$dMes = "0$dMes";
$rs = pg_query($db, "
SELECT DISTINCT re.\"Legajo\", em.\"Nombre\", em.\"Apellido\",
em.\"TipoRelacion\", er.categoria, cpe.\"HorasDiarias\"".($Jur=='1'?",er.jurisdiccion":"").", ca.detalle AS \"Cargo\"
FROM \"tblRecibos\" re
INNER JOIN  \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')." 
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblCategoriasPorEmpresa\" cpe
ON cpe.\"EmpresaID\" = re.\"EmpresaID\" AND cpe.\"Agrupamiento\" = er.agrupamiento
AND cpe.\"Categoria\" = er.categoria AND cpe.\"Cargo\" = er.cargo
LEFT JOIN owner_rafam.cargos ca
ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND
er.categoria = ca.categoria AND er.cargo = ca.cargo
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"ConceptoID\" = 99 AND
re.\"Fecha\" >= '$dAno-$dMes-01' AND re.\"Fecha\" < '$dAno-$dMes-01'::timestamp + interval '1 month' 
ORDER BY ".($Jur=='1'?"7,":"")."4,3
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
	while($row = pg_fetch_array($rs))
	{
		$CantEmp++;
		$CantGEmp++;
		$Legajo = $row[0];
		$ApeYNom = trim($row[2] . ', ' . $row[1]);
		$Cat = $row[4];
		$Horas = $row[5];
		switch($row[3]){
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
		$i = 6;
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
			print "\nCantidad de empleados activos: $CantEmp\n\n";
			$CantEmp = 1;
		}
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				print "<!--SaltoDePagina-->";
			print "Jurisdiccion: " . Jurisdiccion($db, $Jurisdiccion) . "\n\n";
			$AntJur = $Jurisdiccion;
			$AntTP = '0';
			$Abrir = 1;
		}
		if ($TR == '1' && $TipoRel != '' && $AntRel != $TipoRel){
			print "Tipo De Relacion: $TipoRel\n\n";
			$AntRel = $TipoRel;
			$Abrir = 1;
		}
		if ($Abrir == 1){
			$Abrir = 0;
			print str_pad("Leg", 5, ' ', STR_PAD_RIGHT);
			print str_pad("Apellido y Nombre", 35, ' ', STR_PAD_RIGHT);
			print str_pad("Cat", 4, ' ', STR_PAD_RIGHT);
			print str_pad("Cargo", 26, ' ', STR_PAD_RIGHT);
			print str_pad("Hs", 3, ' ', STR_PAD_RIGHT);
			print str_pad("Planta", 13, ' ', STR_PAD_RIGHT);
			print "\n";
			print str_repeat('-', 86) . "\n";
		}
		print str_pad($Legajo, 5, ' ', STR_PAD_RIGHT);
		print str_pad($ApeYNom, 35, ' ', STR_PAD_RIGHT);
		print str_pad($Cat, 4, ' ', STR_PAD_RIGHT);
		print str_pad(substr($Car, 0, 26), 26, ' ', STR_PAD_RIGHT);
		print str_pad($Horas, 3, ' ', STR_PAD_BOTH);
		print str_pad($TipoRel, 13, ' ', STR_PAD_RIGHT);
		print "\n";
	}
	print "\nCantidad de empleados activos: $CantEmp\n";
	print "\nCantidad Total de empleado activos: $CantGEmp\n";

pg_close($db);
?>
</Cuerpo>
</Body>
