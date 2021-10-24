<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}


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

$DBDefinicionSalario = array(
    array ('CODEMP',	CHARACTER_FIELD, 6),
    array ('PERLIQ',	CHARACTER_FIELD, 7),
    array ('NUMLIQ',	NUMBER_FIELD, 2, 0),
    array ('TIPOLIQ',	CHARACTER_FIELD, 1),
    array ('TIPDJUR',	CHARACTER_FIELD, 1),
    array ('CUIL',	    CHARACTER_FIELD, 11),
    array ('NCARGO',	NUMBER_FIELD, 4, 0),
    array ('CODCIPS',	CHARACTER_FIELD, 6),
    array ('CODCEMP',	CHARACTER_FIELD, 8),
    array ('TIPCONC',	CHARACTER_FIELD, 3),
    array ('DESCRI',	CHARACTER_FIELD, 25),
    array ('IMPORTE',	NUMBER_FIELD, 10, 2),
    array ('AN_IPS',	NUMBER_FIELD, 2, 0),
    array ('AN_OTSC',	NUMBER_FIELD, 2, 0),
    array ('ME_OTSC',	NUMBER_FIELD, 2, 0),
    array ('AN_ADOC',	NUMBER_FIELD, 2, 0),
    array ('ME_ADOC',	NUMBER_FIELD, 2, 0),
    array ('FORPAGO',	CHARACTER_FIELD, 1),
    array ('DI_IN_CD',	NUMBER_FIELD, 2, 0),
    array ('DI_LI_SGH',	NUMBER_FIELD, 2, 0),
    array ('ENCPREV',	CHARACTER_FIELD, 2),
    array ('MODREV',	CHARACTER_FIELD, 1)
);

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Generar IPS'){
	
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
		
		$dAno = LimpiarNumero(substr($selPeriodo, 0, 4));
		$dMes = LimpiarNumero(substr($selPeriodo, 4, 4));		

	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == '')
		exit;
		
	if (strlen($dMes) < 2)
		$dMes = "0$dMes";		
		
?>
<?
	$CodEmp = 'M036-1';

//print ("
$rs = pg_query($db, "
SELECT em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"TipoDocumento\", ed.\"NumeroDocumento\", ed.\"CUIT\",
ed.\"FechaNacimiento\", ed.\"Nacionalidad\", ed.\"EstadoCivil\", ed.\"Sexo\", edo.\"Calle\", edo.\"Numero\", 
edo.\"Localidad\", edo.\"CodigoPostal\", (SELECT count(1) AS \"CantHijos\" FROM \"tblEmpleadosFamiliares\" ef
WHERE ef.\"EmpresaID\" = $EmpresaID AND ef.\"SucursalID\" = $SucursalID AND ef.\"Legajo\" = em.\"Legajo\" AND
ef.\"TipoDeVinculo\" = 2) AS ch, ed.\"FechaIngreso\", em.\"FechaEgreso\", em.\"TipoRelacion\",
(SELECT max(\"TipoEstudio\") FROM \"tblEmpleadosEstudios\" es WHERE es.\"TipoEstudio\" < 5 AND
es.\"EmpresaID\" = em.\"EmpresaID\" AND es.\"SucursalID\" = em.\"SucursalID\" AND es.\"Legajo\" = em.\"Legajo\") AS Tes4,
(SELECT max(\"TipoEstudio\") FROM \"tblEmpleadosEstudios\" es WHERE es.\"TipoEstudio\" > 5 AND
es.\"EmpresaID\" = em.\"EmpresaID\" AND es.\"SucursalID\" = em.\"SucursalID\" AND es.\"Legajo\" = em.\"Legajo\") AS Tes5,
er.agrupamiento, er.categoria, ca.detalle, cpe.\"HorasDiarias\", cpe.\"EncuadrePrevisional\",
em.\"FechaEgreso\"<'$dAno-$dMes-01'::date+interval '1 month' as \"MostrarEgreso\", COUNT(er.cargo) AS \"NumeroCargo\"
FROM \"tblEmpleados\" em
INNER JOIN \"tblEmpleadosDatos\" ed
ON ed.\"EmpresaID\" = em.\"EmpresaID\" AND ed.\"SucursalID\" = em.\"SucursalID\" AND ed.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblEmpleadosDomicilio\" edo
ON edo.\"EmpresaID\" = em.\"EmpresaID\" AND edo.\"SucursalID\" = em.\"SucursalID\" AND edo.\"Legajo\" = em.\"Legajo\"
LEFT JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = em.\"EmpresaID\" AND er.\"SucursalID\" = em.\"SucursalID\" AND er.\"Legajo\" = em.\"Legajo\"
LEFT JOIN owner_rafam.cargos ca
ON substr(ca.jurisdiccion, 1, 5) = substr(er.jurisdiccion, 1, 5) AND ca.agrupamiento = er.agrupamiento AND
ca.categoria = er.categoria AND ca.cargo = er.cargo
LEFT JOIN \"tblCategoriasPorEmpresa\" cpe
ON cpe.\"EmpresaID\" = em.\"EmpresaID\" AND cpe.\"Agrupamiento\" = er.agrupamiento
AND cpe.\"Categoria\" = er.categoria AND cpe.\"Cargo\" = er.cargo
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND
em.\"Legajo\" in (SELECT DISTINCT re.\"Legajo\" FROM \"tblRecibos\" re
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Legajo\" NOT IN ('3175') AND
extract('year' from re.\"Fecha\") = $dAno AND extract('month' from re.\"Fecha\") = $dMes AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion) and \"em\".\"TipoRelacion\" != 4 
GROUP BY em.\"Legajo\", em.\"Nombre\", em.\"Apellido\", ed.\"TipoDocumento\", ed.\"NumeroDocumento\", ed.\"CUIT\", 
ed.\"FechaNacimiento\", ed.\"Nacionalidad\", ed.\"EstadoCivil\", ed.\"Sexo\", edo.\"Calle\", edo.\"Numero\", 
edo.\"Localidad\", edo.\"CodigoPostal\", ch, ed.\"FechaIngreso\", em.\"FechaEgreso\", em.\"TipoRelacion\", Tes4, 
Tes5, er.agrupamiento, er.categoria, ca.detalle, cpe.\"HorasDiarias\", cpe.\"EncuadrePrevisional\", 
\"MostrarEgreso\"
");
	if (!$rs){		
		exit;
	}

	$CantEmp = 0;
	while($row = pg_fetch_array($rs))
	{
		$Legajo = str_pad($row[0], 8, ' ', STR_PAD_LEFT);
		$CantEmp++;
		if ($row[3] == '1')
			$TipoDoc = 5;
		elseif ($row[3] == '4')
			$TipoDoc = 1;
		elseif ($row[3] == '5')
			$TipoDoc = 2;
		else
			$TipoDoc = 6;
		$NroDoc = str_replace('.', '', $row[4]);
		$CUIL = str_replace('-', '', str_pad($row[5], 11, ' ', STR_PAD_LEFT));
		$ApeYNom = trim($row[2] . ', ' . $row[1]);
		$Dom = trim($row[10] . ' ' . $row[11]);
		$Loc = $row[12];
		$CodPos = $row[13];
		$ApeYNom = CaracterEspecial($ApeYNom);
		$Dom = CaracterEspecial($Dom);
		$Loc = CaracterEspecial($Loc);
		$FechaNac = LimpiarNumero($row[6]);
		if (strtolower(substr($row[7], 0, 1)) == 'a')
			$Nac = 1;
		else
			$Nac = 2;
		$Sexo = $row[9];
		if ($row[8] == '1')
			$EstCiv = 'S';
		elseif ($row[8] == '2')
			$EstCiv = 'C';
		elseif ($row[8] == '3')
			$EstCiv = 'V';
		elseif ($row[8] == '4')
			$EstCiv = 'D';
		else
			$EstCiv = 'R';

		// Estudio primario a universitario
		$Tes4 = $row[18];
		// Estudio idiomas u otros
		$Tes5 = $row[19];
		if ($Tes4 == ''){
			if ($Tes5 == ''){
				$Estudios = 1;
			}else{
				$Estudios = 3;
			}
		}else{
			$Estudios = $Tes4;
		}
		$Profe = ' ';
		$CantHijos = $row[14];

		$FechaIng = LimpiarNumero($row[15]);
		$FechaEgr = LimpiarNumero($row[16]);
		$RegEst = '  ';

		// Tipo de planta en base al tipo de relacion
		if ($row[17] == '1'){
			$ModRev = 'P';
		}elseif ($row[17] == '2'){
			$ModRev = 'T';
		}else{
			$ModRev = 'T';
		}
		// Si es concejal
		if ($row[21] == 99 && $row[20] == 2)
			$row[24] = 6;
		// Forma de pago en base al tipo de relacion
		if ($row[17] == '1' || $row[17] == '3'){
			$ForPago = 'M';
		}elseif ($row[17] == '2'){
			$ForPago = 'Q';
		}else{
			$ForPago = 'B';
		}
	
		$Agrupamiento = $row[20];
		$CatRev = $row[21];
		$Cargo = substr($row[22], 0, 25);
		$RegHor = intval($row[23]) * 5;
		if ($RegHor == 0)
			$RegHor = 30;
		$CatFun = $CatRev;
		$EncPre = $row[24];
		if ($Legajo == '3175')
			$EncPre = '4';
		if ($EncPre == '')
			$EncPre = '1';
		if (strlen($EncPre) < 2)
			$EncPre = "0$EncPre";
		$bMostrarEgreso = ($row[25]=='t' ? true:false);
		if (!$bMostrarEgreso)
			$FechaEgr = '        ';
		
		$NumeroCargo = $row['NumeroCargo'];//str_pad($row['NumeroCargo'], 2, "0", STR_PAD_LEFT);
		
		// Codificacion del agrupamiento en base al IPS
		switch($row[20]){
		case 1:
			$Agrup = 'R';
			break;
		case 2:
			$Agrup = 'J';
			break;
		case 3:
			$Agrup = 'P';
			break;
		case 4:
			$Agrup = 'T';
			break;
		case 6:
			$Agrup = 'S';
			break;
		case 7:
			$Agrup = 'O';
			break;
		default:
			$Agrup = 'A';
			break;
		}

		$Agrup = '     ' . $Agrup;



		$FechaPos = $FechaIng;
		$FechaCLic = '        ';
		$FechaFLic = '        ';
		$FechaCBen = '        ';
		$Lic = ' ';
		$AntIPS = 0;
		$AntOtras = 0;
		$BenPrev = 0;
		$CajaOtor = ' 0';

		$sql = "
SELECT re.\"NumeroLiquidacion\", re.\"ConceptoID\", re.\"AliasID\", re.\"Descripcion\", re.\"Haber1\", re.\"Haber2\", 
	re.\"Descuento\", re.\"Aporte\", ca.\"CodigoIPS\", ca.\"TipoConceptoIPS\", c.\"Categoria\" AS \"NumeroCargo\", c.\"Grupo\" 
FROM \"tblRecibos\" re
INNER JOIN \"tblConceptosAlias\" ca
ON ca.\"EmpresaID\" = re.\"EmpresaID\" AND ca.\"AliasID\" = re.\"AliasID\" AND ca.\"AliasID\" NOT IN (29, 30, 31, 32)
LEFT JOIN \"tblEmpleadosRafam\" ra ON  re.\"Legajo\" = ra.\"Legajo\"
LEFT JOIN \"tblCategoriasEmpleado\" c on re.\"Legajo\" = c.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Legajo\" = '$row[0]' AND 
EXTRACT('year' FROM re.\"Fecha\") = $dAno AND EXTRACT('month' FROM re.\"Fecha\") = $dMes AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY re.\"ItemID\", re.\"NumeroLiquidacion\", re.\"ConceptoID\", re.\"AliasID\", re.\"Descripcion\", re.\"Haber1\",
 re.\"Haber2\", re.\"Descuento\", re.\"Aporte\", ca.\"CodigoIPS\", ca.\"TipoConceptoIPS\", c.\"Categoria\", c.\"Grupo\"
ORDER BY \"ItemID\", \"NumeroLiquidacion\"
		";
		$rs1 = pg_query($db, $sql);
		if (!$rs1){ print $sql;
			exit;
		}
		$Periodo = "$dAno/$dMes";
		$TotalLiq = 0;
		while($rowL = pg_fetch_array($rs1)){
			$NumLiq = $rowL[0];
			$ConcID = $rowL[1];
			$AliasID = str_pad($rowL[2], 8, ' ', STR_PAD_LEFT);
			$Descrip = str_pad($rowL[3], 25, ' ', STR_PAD_LEFT);
			$CodIPS = $rowL[8];
			$TipIPS = $rowL[9];
			if ($ConcID == 99){
				// Total
				$TotalLiq = $TotalLiq + ($rowL[4] + $rowL[5] - $rowL[6]);
				$Importe = ($rowL[4] + $rowL[5] - $rowL[6]);
				$TipIPS = 'LIQ';
			}
			else
			{
				if ($rowL[4] == ''){
					if ($rowL[5] == ''){
						if ($rowL[6] == ''){
							$Importe = $rowL[7];
						}else{
							$Importe = $rowL[6];
						}
					}else{
						$Importe = $rowL[5];
					}
				}else{
					$Importe = $rowL[4];
				}
			}
/*
			if ($ConcID == 91){
				if ($Importe > 0) // cuando el redondeo es positivo, entonces no es un descuento
					$TipIPS = 'RSA';
			}
			if (in_array($ConcID, array(36))){
				if ($Importe < 0) // cuando el descuento es negativo, entonces no es un descuento
					$TipIPS = 'RSA';
			}
			if (in_array($ConcID, array(40))){
				if ($Importe < 0) // cuando el haber es negativo, entonces es un descuento
					$TipIPS = 'DES';
			}
*/
			{
			$NCargo = $rowL['NumeroCargo'];//str_pad($rowL['NumeroCargo'], 2, "0", STR_PAD_LEFT);
			$NCargo = substr($NCargo, 0, 1).str_pad(substr($NCargo, 1), 2, "0", STR_PAD_LEFT);
			
			header('Content-disposition: attachment; filename=salario.txt');
			header('Content-type: application/octet-stream');
			//header('Content-type: text/plain');
			header("Pragma: no-cache");
			header("Expires: 0");
			
			
			print str_pad($CodEmp, 6, ' ', STR_PAD_RIGHT);
			print str_pad($Periodo, 7, ' ', STR_PAD_RIGHT);
			print str_pad($NumeroLiquidacion, 2, '0', STR_PAD_LEFT);
			print 'N';
			print 'O';
			print str_pad($CUIL, 11, ' ', STR_PAD_RIGHT);
			if ($rowL[10] == '' && $rowL[11] == 'INTENDENTE') $NCargo = '900';
			elseif ($rowL[10] == '' && $rowL[11] == 'CONCEJAL') $NCargo = '901';
			elseif ($rowL[10] == '') $NCargo = '999';
			switch (substr($NCargo, 0, 1))
			{
			case 'A':
				$NCargo = "1".substr($NCargo, 1);
				break;
			case 'B':
				$NCargo = "2".substr($NCargo, 1);
				break;
			case 'C':
				$NCargo = "3".substr($NCargo, 1);
				break;
			case 'D':
				$NCargo = "4".substr($NCargo, 1);
				break;
			}
			print str_pad('1'/*$NCargo*/, 4, '0', STR_PAD_LEFT); // antes legajo num
			print str_pad($CodIPS, 6, ' ', STR_PAD_RIGHT);
			print str_pad($AliasID, 8, ' ', STR_PAD_RIGHT);
			print str_pad($TipIPS, 3, ' ', STR_PAD_RIGHT);
			print str_pad(substr(trim($Descrip), 0, 25), 25, ' ', STR_PAD_RIGHT);
			if ($Importe >= 0) print str_pad((int)abs($Importe), 7, '0', STR_PAD_LEFT).".".str_pad((int)abs(($Importe*100) % 100), 2, '0', STR_PAD_LEFT);
			else print '-'.str_pad((int)abs($Importe), 6, '0', STR_PAD_LEFT).".".str_pad((int)abs(($Importe*100) % 100), 2, '0', STR_PAD_LEFT);
			
			print '00'; //AN_IPS
			print '00';
			print '00';
			print '00';
			print '00';
			print '00';
			print $ForPago;
			print '00';
			print '00';
			
			
			/*- 13) AN_IPS              Numérico       2	(AÑOS DE ANTIGÜEDAD EN IPS)
			- 14) ME_IPS              Numérico       2	(MESES DE ANTIGÜEDAD EN IPS)
			- 15) AN_OTSC             Numérico       2 	(AÑOS DE ANTIGÜEDAD EN OTRAS CAJAS)
			- 16) ME_OTSC             Numérico       2	(MESES DE ANTIGÜEDAD EN OTRAS CAJAS)
			- 17) AN_ADOC             Numérico       2	(AÑOS DE ANTIGÜEDAD DOCENTE)
			- 18) ME_ADOC             Numérico       2	(MESES DE ANTIGÜEDAD DOCENTE)
			- 19) FORPAGO             Carácter       1	(FORMA DE PAGO)
			- 20) DI_IN_CD            Numérico       2	(DIAS DESCONTADS POR INASISTENCIA)
			- 21) DI_LI_SGH           Numérico       2	(DIAS DESCONTADOS POR LICENCIA SIN GOCE DE HABERES)
			*/
			
			
			
			print $EncPre;
			print $ModRev;
			print "\r\n";
			
				/*dbase_add_record($fhSal, array($CodEmp, $TipoDoc, $NroDoc, $Legajo, $ModRev, $EncPre, $Periodo, $NumLiq, 
					'N', 'O', $CodIPS, $AliasID, $TipIPS, $Descrip, $Importe, $NCargo));*/
			}
		}
		$TotalLiq = round($TotalLiq, 2);
		/*dbase_add_record($fhSal, array($CodEmp, $TipoDoc, $NroDoc, $Legajo, $ModRev, $EncPre, $Periodo, $NumLiq, 
			'N', 'O', '      ', '        ', 'LIQ', '     IMPORTE NETO COBRADO', $TotalLiq, $NCargo));*/
	}

	
	exit;
	
	/*dbase_close($fhDat);
	dbase_close($fhAfi);
	dbase_close($fhRev);
	dbase_close($fhSal);
	$fp = fopen($DBFArchivoDatosEmp, "a");
	fwrite($fp, chr(26), 1);
	fclose($fp);
	$fp = fopen($DBFArchivoAfiliado, "a");
	fwrite($fp, chr(26), 1);
	fclose($fp);
	$fp = fopen($DBFArchivoRevista, "a");
	fwrite($fp, chr(26), 1);
	fclose($fp);
	$fp = fopen($DBFArchivoSalario, "a");
	fwrite($fp, chr(26), 1);
	fclose($fp);
	alerta('Los archivos del IPS fueron generados correctamente');
	print "<br><b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br>";*/

	
}

include ('header.php');

?>

<form name=frmListadoIPS action=listadoIPS2017.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Archivos</td></tr>
</table>
</div>

<?

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
	<table class="datauser" align="left">
<?php    include 'selLiquida.php';?>
    <TR>
	<TD class="izquierdo">Tipo de Relaci&oacute;n:</td><TD class=derecho2>
	<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1>Mensualizados
	<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1>Jornalizados
	<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1>Contratados
	<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1>Loc. de obra
	</td></tr>
	
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Generar IPS"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>

