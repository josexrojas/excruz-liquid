<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

include ('header.php');

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

// Archivos
$DBFArchivoDatosEmp = '../listados/datosemp.dbf';
$DBFArchivoAfiliado = '../listados/afiliado.dbf';
$DBFArchivoRevista = '../listados/revista.dbf';
$DBFArchivoSalario = '../listados/salario.dbf';

// Definiciones de bases
$DBDefinicionDatosEmp = array (
   array('CODEMP',		CHARACTER_FIELD, 6),
   array('NOMEMP',		CHARACTER_FIELD, 40),
   array('DOMICILIO',	CHARACTER_FIELD, 30),
   array('LOCALIDAD',	CHARACTER_FIELD, 20),
   array('CODPOSTAL',	CHARACTER_FIELD, 6),
   array('NCUIT',		CHARACTER_FIELD, 11)
);

$DBDefinicionAfiliado = array (
   array ('CODEMP',		CHARACTER_FIELD, 6),
   array ('TIPODOC',	NUMBER_FIELD, 1, 0),
   array ('NRODOC',		NUMBER_FIELD, 8, 0),
   array ('CUIL',		CHARACTER_FIELD, 11),
   array ('APENOM',		CHARACTER_FIELD, 35),
   array ('DOMICILIO',	CHARACTER_FIELD, 30),
   array ('LOCALIDAD',	CHARACTER_FIELD, 20),
   array ('CODPOSTAL',	CHARACTER_FIELD, 6),
   array ('FECNAC',		DATE_FIELD),
   array ('NACIONA',	CHARACTER_FIELD, 1),
   array ('SEXO',		CHARACTER_FIELD, 1),
   array ('ESTCIVIL',	CHARACTER_FIELD, 1),
   array ('ESTUAFI',	NUMBER_FIELD, 1, 0),
   array ('PROFE',		CHARACTER_FIELD, 25),
   array ('CANTHIJOS',	NUMBER_FIELD, 2, 0)
);

$DBDefinicionRevista = array (
   array ('CODEMP',		CHARACTER_FIELD, 6),
   array ('TIPODOC',	NUMBER_FIELD, 1, 0),
   array ('NRODOC',		NUMBER_FIELD, 8, 0),
   array ('NROLEG',		CHARACTER_FIELD, 8),
   array ('NCARGO',		NUMBER_FIELD, 4, 0),
   array ('FINGEMP',	DATE_FIELD),
   array ('FECBAJA',	DATE_FIELD),
   array ('REGEST',		CHARACTER_FIELD, 2),
   array ('ENCPREV',	CHARACTER_FIELD, 2),
   array ('MODREV',		CHARACTER_FIELD, 1),
   array ('AGRUP',		CHARACTER_FIELD, 6),
   array ('CATRE',		CHARACTER_FIELD, 5),
   array ('CATFU',		CHARACTER_FIELD, 5),
   array ('CARGO',		CHARACTER_FIELD, 25),
   array ('FECPOS',		DATE_FIELD),
   array ('REGH',		NUMBER_FIELD, 2, 0),
   array ('FORPAGO',	CHARACTER_FIELD, 1),
   array ('LICENCIA',	CHARACTER_FIELD, 1),
   array ('FECLIC',		DATE_FIELD),
   array ('FEFLIC',		DATE_FIELD),
   array ('ARIPS',		NUMBER_FIELD, 2, 0),
   array ('AROTS',		NUMBER_FIELD, 2, 0),
   array ('BENPRE',		CHARACTER_FIELD, 1),
   array ('CAJAOT',		CHARACTER_FIELD, 2),
   array ('FECBEN',		DATE_FIELD)
);

$DBDefinicionSalario = array(
   array ('CODEMP',		CHARACTER_FIELD, 6),
   array ('TIPODOC',	NUMBER_FIELD, 1, 0),
   array ('NRODOC',		NUMBER_FIELD, 8, 0),
   array ('NROLEG',		CHARACTER_FIELD, 8),
   array ('MODREV',		CHARACTER_FIELD, 1),
   array ('ENCPREV',	CHARACTER_FIELD, 2),
   array ('PERLIQ',		CHARACTER_FIELD, 7),
   array ('NUMLIQ',		NUMBER_FIELD, 2, 0),
   array ('TIPOLIQ',	CHARACTER_FIELD, 1),
   array ('TIPDJUR',	CHARACTER_FIELD, 1),
   array ('CODCIPS',	CHARACTER_FIELD, 6),
   array ('CODCEMP',	CHARACTER_FIELD, 8),
   array ('TIPCONC',	CHARACTER_FIELD, 3),
   array ('DESCRI',		CHARACTER_FIELD, 25),
   array ('IMPORTE',	NUMBER_FIELD, 10, 2),
   array ('NCARGO',		NUMBER_FIELD, 4, 0)
);

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoIPS action=listadoIPS.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
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
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Archivos</td></tr>
</table>
</div>
<?
	// Elimina los archivos
	if(file_exists($DBFArchivoDatosEmp))
		unlink($DBFArchivoDatosEmp);
	if(file_exists($DBFArchivoAfiliado))
		unlink($DBFArchivoAfiliado);
	if(file_exists($DBFArchivoRevista))
		unlink($DBFArchivoRevista);
	if(file_exists($DBFArchivoSalario))
		unlink($DBFArchivoSalario);

	// Creamos los archivos
	$dbDat = @ dbase_create($DBFArchivoDatosEmp, $DBDefinicionDatosEmp) or die ("Error al crear $DBFArchivoDatosEmp");
	$dbAfi = @ dbase_create($DBFArchivoAfiliado, $DBDefinicionAfiliado) or die ("Error al crear $DBFArchivoAfiliado");
	$dbRev = @ dbase_create($DBFArchivoRevista, $DBDefinicionRevista) or die ("Error al crear $DBFArchivoRevista");
	$dbSal = @ dbase_create($DBFArchivoSalario, $DBDefinicionSalario) or die ("Error al crear $DBFArchivoSalario");

	// Abrimos los archivos
	$fhDat = @ dbase_open($DBFArchivoDatosEmp, READ_WRITE) or die ("Error al abrir $DBFArchivoDatosEmp");
	$fhAfi = @ dbase_open($DBFArchivoAfiliado, READ_WRITE) or die ("Error al abrir $DBFArchivoAfiliado");
	$fhRev = @ dbase_open($DBFArchivoRevista, READ_WRITE) or die ("Error al abrir $DBFArchivoRevista");
	$fhSal = @ dbase_open($DBFArchivoSalario, READ_WRITE) or die ("Error al abrir $DBFArchivoSalario");

	$CodEmp = 'M036-1';

//	print ("
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
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
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
	dbase_add_record($fhDat, array($CodEmp, 'MUNICIPALIDAD DE EXALTACION DE LA CRUZ  ', 'RIVADAVIA 411                 ', 'CAPILLA DE SENOR    ', '2812  ', '33999295989'));
	$CantEmp = 0;
	while($row = pg_fetch_array($rs))
	{
		$CantEmp++;
		if ($row[3] == '1')
			$TipoDoc = 5;
		elseif ($row[3] == '4')
			$TipoDoc = 1;
		elseif ($row[3] == '5')
			$TipoDoc = 2;
		else
			$TipoDoc = 6;
		$Legajo = str_pad($row[0], 8, ' ', STR_PAD_LEFT);
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
		$EncPre = '1'; //$row[24];
		if ($EncPre == '')
			$EncPre = '1';
		if ($row[0] == '3175')
			$EncPre = '4';
		if (strlen($EncPre) < 2)
			$EncPre = "0$EncPre";
		$bMostrarEgreso = ($row[25]=='t' ? true:false);
		if (!$bMostrarEgreso)
			$FechaEgr = '        ';
		
		$NumeroCargo = $row['NumeroCargo'];//str_pad($row['NumeroCargo'], 2, "0", STR_PAD_LEFT);
		if (!$NumeroCargo) $NumeroCargo = 1;

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
		dbase_add_record($fhAfi, array($CodEmp, $TipoDoc, $NroDoc, $CUIL, $ApeYNom, $Dom, $Loc, $CodPos, $FechaNac, $Nac, $Sexo, $EstCiv, $Estudios, $Profe, $CantHijos));
		dbase_add_record($fhRev, array($CodEmp, $TipoDoc, $NroDoc, $Legajo, $NumeroCargo, $FechaIng, $FechaEgr, $RegEst, $EncPre, $ModRev, 
			$Agrup, $CatRev, $CatFun, $Cargo, $FechaPos, $RegHor, $ForPago, $Lic, $FechaCLic, $FechaFLic, $AntIPS, $AntOtras,
			$BenPrev, $CajaOtor, $FechaCBen));

		$rs1 = pg_query($db, "
SELECT re.\"NumeroLiquidacion\", re.\"ConceptoID\", re.\"AliasID\", re.\"Descripcion\", re.\"Haber1\", re.\"Haber2\", 
	re.\"Descuento\", re.\"Aporte\", ca.\"CodigoIPS\", ca.\"TipoConceptoIPS\", COUNT(ra.cargo) AS \"NumeroCargo\"
FROM \"tblRecibos\" re
INNER JOIN \"tblConceptosAlias\" ca
ON ca.\"EmpresaID\" = re.\"EmpresaID\" AND ca.\"AliasID\" = re.\"AliasID\" AND ca.\"AliasID\" <> 29 AND ca.\"AliasID\" <> 269
INNER JOIN \"tblEmpleadosRafam\" ra ON  re.\"Legajo\" = ra.\"Legajo\"
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"Legajo\" = '$row[0]' AND 
EXTRACT('year' FROM re.\"Fecha\") = $dAno AND EXTRACT('month' FROM re.\"Fecha\") = $dMes AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY re.\"ItemID\", re.\"NumeroLiquidacion\", re.\"ConceptoID\", re.\"AliasID\", re.\"Descripcion\", re.\"Haber1\",
 re.\"Haber2\", re.\"Descuento\", re.\"Aporte\", ca.\"CodigoIPS\", ca.\"TipoConceptoIPS\"
ORDER BY \"ItemID\", \"NumeroLiquidacion\"
		");
		if (!$rs1){
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
			}else{
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
			$NCargo = $rowL['NumeroCargo'];//str_pad($rowL['NumeroCargo'], 2, "0", STR_PAD_LEFT);
			if (!$NCargo) $NCargo = 1;
				dbase_add_record($fhSal, array($CodEmp, $TipoDoc, $NroDoc, $Legajo, $ModRev, $EncPre, $Periodo, $NumLiq, 
					'N', 'O', $CodIPS, $AliasID, $TipIPS, $Descrip, $Importe, $NCargo));
			}
		}
		$TotalLiq = round($TotalLiq, 2);
		dbase_add_record($fhSal, array($CodEmp, $TipoDoc, $NroDoc, $Legajo, $ModRev, $EncPre, $Periodo, $NumLiq, 
			'N', 'O', '      ', '        ', 'LIQ', '     IMPORTE NETO COBRADO', $TotalLiq, $NCargo));
	}

	dbase_close($fhDat);
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
	print "<br><b>Per&iacute;odo: " . Mes($dMes) . " de $dAno</b><br>";
?>
	<b>Se Procesaron <?=$CantEmp?> Empleados</b><br><br>
	<script>
		document.getElementById('divLoading').style.display = 'none';
		function BajarListado(sArch){
			document.getElementById('accion').value = 'Bajar Listado';
			document.getElementById('listado').value = sArch;
			document.frmListadoIPS.submit();
		}
	</script>
	<a class="tecla" href="javascript:BajarListado('datosemp.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Datos Empleador</a> 
	<a class="tecla" href="javascript:BajarListado('afiliado.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Afiliado</a> 
	<a class="tecla" href="javascript:BajarListado('revista.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Revista</a> 
	<a class="tecla" href="javascript:BajarListado('salario.dbf'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Salario</a> 
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
