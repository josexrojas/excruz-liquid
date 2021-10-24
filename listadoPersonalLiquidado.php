<?
include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListadoPersonalLiquidado action=listadoPersonalLiquidado.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>

<?
if ($accion == 'Generar Informe'){
	$dAno = $_POST["selPeriodo"];
		
	if ($dAno == ''){
		exit;
	}
	//if (strlen($dMes) < 2)
//		$dMes = "0$dMes";
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$TR = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkTipoRelM = true;
	$chkTipoRelJ = true;
	$chkTipoRelC = true;
	$chkTipoRelL = true;
	$TipoRelacion = '';
	if ($chkTipoRelM == true)
		$TipoRelacion .= '1,';
	if ($chkTipoRelJ == true)
		$TipoRelacion .= '2,';
	if ($chkTipoRelC == true)
		$TipoRelacion .= '3,';
	if ($chkTipoRelL == true)
		$TipoRelacion .= '4,';
	$TipoRelacion = substr($TipoRelacion, 0, -1);
?>
	<H1>Listado de Personal Liquidado</H1>
<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<?
$sql = "
SELECT DISTINCT re.\"Legajo\", em.\"Nombre\", em.\"Apellido\",
em.\"TipoRelacion\", er.categoria, cpe.\"HorasDiarias\"".($Jur=='1'?",er.jurisdiccion":"").", ca.detalle AS \"Cargo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Haber1\" ELSE 0 END ) as \"EneroRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Haber2\" ELSE 0 END ) as \"EneroNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN re.\"Descuento\" ELSE 0 END ) as \"EneroRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 1 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"EneroLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Haber1\" ELSE 0 END ) as \"FebreroRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Haber2\" ELSE 0 END ) as \"FebreroNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN re.\"Descuento\" ELSE 0 END ) as \"FebreroRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 2 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"FebreroLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Haber1\" ELSE 0 END ) as \"MarzoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Haber2\" ELSE 0 END ) as \"MarzoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN re.\"Descuento\" ELSE 0 END ) as \"MarzoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 3 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"MarzoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Haber1\" ELSE 0 END ) as \"AbrilRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Haber2\" ELSE 0 END ) as \"AbrilNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN re.\"Descuento\" ELSE 0 END ) as \"AbrilRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 4 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"AbrilLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Haber1\" ELSE 0 END ) as \"MayoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Haber2\" ELSE 0 END ) as \"MayoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN re.\"Descuento\" ELSE 0 END ) as \"MayoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 5 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"MayoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Haber1\" ELSE 0 END ) as \"JunioRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Haber2\" ELSE 0 END ) as \"JunioNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN re.\"Descuento\" ELSE 0 END ) as \"JunioRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 6 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"JunioLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Haber1\" ELSE 0 END ) as \"JulioRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Haber2\" ELSE 0 END ) as \"JulioNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN re.\"Descuento\" ELSE 0 END ) as \"JulioRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 7 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"JulioLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Haber1\" ELSE 0 END ) as \"AgostoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Haber2\" ELSE 0 END ) as \"AgostoNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN re.\"Descuento\" ELSE 0 END ) as \"AgostoRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 8 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"AgostoLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Haber1\" ELSE 0 END ) as \"SeptiembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Haber2\" ELSE 0 END ) as \"SeptiembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN re.\"Descuento\" ELSE 0 END ) as \"SeptiembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 9 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"SeptiembreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Haber1\" ELSE 0 END ) as \"OctubreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Haber2\" ELSE 0 END ) as \"OctubreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN re.\"Descuento\" ELSE 0 END ) as \"OctubreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 10 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"OctubreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Haber1\" ELSE 0 END ) as \"NoviembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Haber2\" ELSE 0 END ) as \"NoviembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN re.\"Descuento\" ELSE 0 END ) as \"NoviembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 11 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"NoviembreLiquido\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Haber1\" ELSE 0 END ) as \"DiciembreRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Haber2\" ELSE 0 END ) as \"DiciembreNoRemunerativo\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN re.\"Descuento\" ELSE 0 END ) as \"DiciembreRetenciones\",
SUM(CASE WHEN date_part('month', re.\"Fecha\"::timestamp ) = 12 THEN (re.\"Haber1\" + re.\"Haber2\") - re.\"Descuento\" ELSE 0 END ) as \"DiciembreLiquido\",
emd.\"Sexo\" as \"Sexo\", CASE WHEN emd.\"EstadoCivil\" = 1 THEN 'Soltero/a' WHEN emd.\"EstadoCivil\" = 2 THEN 'Casado/a' WHEN emd.\"EstadoCivil\" = 3 THEN 'Viudo/a' WHEN emd.\"EstadoCivil\" = 4 THEN 'Divorciado/a' ELSE '' END AS \"EstadoCivil\", emd.\"NumeroDocumento\", CASE WHEN emd.\"TipoDocumento\" = 1 THEN 'DNI' WHEN emd.\"TipoDocumento\" = 2 THEN 'CI' WHEN emd.\"TipoDocumento\" = 3 THEN 'PASAPORTE' WHEN emd.\"TipoDocumento\" = 4 THEN 'LE' WHEN emd.\"TipoDocumento\" = 5 THEN 'LC' ELSE '' END AS \"TipoDocumento\", emd.\"FechaNacimiento\", emd.\"CUIT\", emd.\"FechaIngreso\"
FROM \"tblRecibos\" re
INNER JOIN  \"tblEmpleados\" em
ON em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" AND em.\"Legajo\" = re.\"Legajo\"".
($TipoRelacion != '' ? " AND em.\"TipoRelacion\" in ($TipoRelacion)" : '')." 
INNER JOIN \"tblEmpleadosRafam\" er
ON er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" AND er.\"Legajo\" = re.\"Legajo\"
INNER JOIN \"tblEmpleadosDatos\" emd 
ON emd.\"EmpresaID\" = re.\"EmpresaID\" AND emd.\"SucursalID\" = re.\"SucursalID\" AND emd.\"Legajo\" = re.\"Legajo\"
LEFT JOIN \"tblCategoriasPorEmpresa\" cpe
ON cpe.\"EmpresaID\" = re.\"EmpresaID\" AND cpe.\"Agrupamiento\" = er.agrupamiento
AND cpe.\"Categoria\" = er.categoria AND cpe.\"Cargo\" = er.cargo
LEFT JOIN owner_rafam.cargos ca
ON substr(er.jurisdiccion, 1, 5) = substr(ca.jurisdiccion, 1, 5) AND er.agrupamiento = ca.agrupamiento AND
er.categoria = ca.categoria AND er.cargo = ca.cargo
WHERE re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND re.\"ConceptoID\" = 99 AND
re.\"Fecha\" >= '$dAno-01-01' AND re.\"Fecha\" < '$dAno-12-01'::timestamp + interval '1 month' 
GROUP BY re.\"Legajo\", em.\"Nombre\", em.\"Apellido\", em.\"TipoRelacion\", er.categoria, cpe.\"HorasDiarias\", ca.detalle,emd.\"Sexo\" , emd.\"EstadoCivil\", emd.\"NumeroDocumento\", emd.\"TipoDocumento\", emd.\"FechaNacimiento\", emd.\"CUIT\", emd.\"FechaIngreso\"
ORDER BY ".($Jur=='1'?"7,":"")."4,3
";

 $rs = pg_query($db, $sql);
 
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
	 print "<b>Per&iacute;odo A&ntilde;o ". $dAno ." </b>";

?>
</br>
</br>
<a href="#" class="tecla" onclick="MM_openBrWindow('listadoPersonalLiquidadoPrint.php?Ano=<?=$dAno?>','printpreview','width=872,height=750')">
    <img src="images/icon24_print.gif" alt="Imprimir" width="24" height="23" border="0" align="absmiddle">  Imprimir </a>
	<br><br>
<? 
	while($row = pg_fetch_array($rs))
	{;
		$CantEmp++;
		$CantGEmp++;
		$Legajo = $row[0];
		$ApeYNom = trim($row[2] . ', ' . $row[1]);
		$Cat = $row[4];
		$Horas = $row[5];
                
                $EneroRemunerativo = $row[7];
                $EneroNoremunerativo = $row[8];
                $EneroRetenciones = $row[9];
                $EneroLiquido = $row[10];

                $FebreroRemunerativo = $row[11];
		$FebreroNoremunerativo = $row[12];
		$FebreroRetenciones = $row[13];
		$FebreroLiquido = $row[14];

		$MarzoRemunerativo = $row[15];
		$MarzoNoremunerativo = $row[16];
		$MarzoRetenciones = $row[17];
		$MarzoLiquido = $row[18];

		$AbrilRemunerativo = $row[19];
		$AbrilNoremunerativo = $row[20];
		$AbrilRetenciones = $row[21];
		$AbrilLiquido = $row[22];

		$MayoRemunerativo = $row[23];
		$MayoNoremunerativo = $row[24];
		$MayoRetenciones = $row[25];
		$MayoLiquido = $row[26];

		$JunioRemunerativo = $row[27];
		$JunioNoremunerativo = $row[28];
		$JunioRetenciones = $row[29];
		$JunioLiquido = $row[30];


		$JulioRemunerativo = $row[31];
		$JulioNoremunerativo = $row[32];
		$JulioRetenciones = $row[33];
		$JulioLiquido = $row[34];

		$AgostoRemunerativo = $row[35];
		$AgostoNoremunerativo = $row[36];
		$AgostoRetenciones = $row[37];
		$AgostoLiquido = $row[38];

		$SeptiembreRemunerativo = $row[39];
		$SeptiembreNoremunerativo = $row[40];
		$SeptiembreRetenciones = $row[41];
		$SeptiembreLiquido = $row[42];

		$OctubreRemunerativo = $row[43];
		$OctubreNoremunerativo = $row[44];
		$OctubreRetenciones = $row[45];
		$OctubreLiquido = $row[46];

		$NoviembreRemunerativo = $row[47];
		$NoviembreNoremunerativo = $row[48];
		$NoviembreRetenciones = $row[49];
		$NoviembreLiquido = $row[50];

		$DiciembreRemunerativo = $row[51];
		$DiciembreNoremunerativo = $row[52];
		$DiciembreRetenciones = $row[53];
		$DiciembreLiquido = $row[54];

		$Sexo = $row[55];
		$EstadoCivil = $row[56];


		
		$Documento = $row[58]." - ".$row[57];
		$FechaNacimiento = $row[59];
		$Cuil = $row[60];
		$FechaIngreso = $row[61];	
		
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
			<tr><th colspan="12"></th>
                        <th colspan="4">Enero</th><th colspan="4">Febrero</th> <th colspan="4">Marzo</th><th colspan="4">Abril</th><th colspan="4">Mayo</th> <th colspan="4">Junio</th><th colspan="4">Julio</th><th colspan="4">Agosto</th><th colspan="4">Septiembre</th> <th colspan="4">Octubre</th> <th colspan="4">Noviembre</th><th colspan="4">Diciembre</th></tr>

			<tr>
			<th>Legajo</th><th>Apellido y Nombre</th><th>Categoria</th><th>Cargo</th><th>Horas Diarias</th><th>Planta</th>
			<th>Sexo</th><th>Estado Civil</th><th>Tipo y Numero Documento</th><th>Fecha Nacimiento</th><th>Cuil</th><th>Fecha Antiguedad</th>			
			<th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
                        <th>Remunerativo</th><th>No Remunerativo</th><th>Retenciones</th><th>Liquido a Percibir</th>
			</tr>
<?
		}
?>
		<tr>
		<td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$Cat?></td><td><?=$Car?></td><td><?=$Horas?></td><td><?=$TipoRel?></td>
		<td><?=$Sexo?></td><td><?=$EstadoCivil?></td><td><?=$Documento?></td><td><?=$FechaNacimiento?></td><td><?=$Cuil?></td><td><?=$FechaIngreso?></td>
                <td><?=$EneroRemunerativo?></td><td><?=$EneroNoremunerativo?></td><td><?=$EneroRetenciones?></td><td><?=$EneroLiquido?></td>
                <td><?=$FebreroRemunerativo?></td><td><?=$FebreroNoremunerativo?></td><td><?=$FebreroRetenciones?></td><td><?=$FebreroLiquido?></td>
                <td><?=$MarzoRemunerativo?></td><td><?=$MarzoNoremunerativo?></td><td><?=$MarzoRetenciones?></td><td><?=$MarzoLiquido?></td>
                <td><?=$AbrilRemunerativo?></td><td><?=$AbrilNoremunerativo?></td><td><?=$AbrilRetenciones?></td><td><?=$AbrilLiquido?></td>
                <td><?=$MayoRemunerativo?></td><td><?=$MayoNoremunerativo?></td><td><?=$MayoRetenciones?></td><td><?=$MayoLiquido?></td>
                <td><?=$JunioRemunerativo?></td><td><?=$JunioNoremunerativo?></td><td><?=$JunioRetenciones?></td><td><?=$JunioLiquido?></td>
                <td><?=$JulioRemunerativo?></td><td><?=$JulioNoremunerativo?></td><td><?=$JulioRetenciones?></td><td><?=$JulioLiquido?></td>
                <td><?=$AgostoRemunerativo?></td><td><?=$AgostoNoremunerativo?></td><td><?=$AgostoRetenciones?></td><td><?=$AgostoLiquido?></td>
                <td><?=$SeptiembreRemunerativo?></td><td><?=$SeptiembreNoremunerativo?></td><td><?=$SeptiembreRetenciones?></td><td><?=$SeptiembreLiquido?></td>
                <td><?=$OctubreRemunerativo?></td><td><?=$OctubreNoremunerativo?></td><td><?=$OctubreRetenciones?></td><td><?=$OctubreLiquido?></td>
                <td><?=$NoviembreRemunerativo?></td><td><?=$NoviembreNoremunerativo?></td><td><?=$NoviembreRetenciones?></td><td><?=$NoviembreLiquido?></td>
                <td><?=$DiciembreRemunerativo?></td><td><?=$DiciembreNoremunerativo?></td><td><?=$DiciembreRetenciones?></td><td><?=$DiciembreLiquido?></td>
                </tr>
<?
	}
	print "</table><br>\n";
	print "<b>Cantidad de empleados activos: $CantEmp</b><br>\n";
	print "<br><b>Cantidad Total de empleado activos: $CantGEmp</b><br>";
?>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<?
}

if ($accion == ''){
	$rs = pg_query($db, "
SELECT DISTINCT extract('year' from \"FechaPeriodo\")
FROM \"tblPeriodos\"
GROUP BY extract('year' from \"FechaPeriodo\")
ORDER BY 1 DESC
	");
	if (!$rs){
		exit;
	}
?>
		</br>
	<H1>Listado de Personal Liquidado</H1>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione A&ntilde;o:</TD><TD class="derecho2"><select id=selPeriodo name=selPeriodo>
<?
	while($row = pg_fetch_array($rs)){
		$dAno = $row[0];
	
		print "<option value=$row[0]>$dAno</option>\n";
	}
?>
	</select></TD></TR>

	<TR>
		<TD class="izquierdo"></TD><TD class="derecho">
		<input type=submit id=accion name=accion value="Generar Informe">
		<? Volver(); ?>
		</TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
