<?
ob_start();

include ('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
?>

<form name=frmListado action=listadoSeguroVida.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>
<script type="text/JavaScript">
<!--
	function BajarListado(sArch){
			document.getElementById('accion').value = 'Bajar Listado';
			document.getElementById('listado').value = sArch;
			document.frmListadoBanco.submit();
	}
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
	}
//-->
</script>

<?
if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$dAno = LimpiarNumero(substr($selPeriodo, 0, $i));
		$dMes = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($dAno == '' || $dMes == ''){
		exit;
	}
	if (strlen($dMes) < 2)
		$dMes = "0$dMes";
	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$TR = LimpiarNumero($_POST["chkTipoPlanta"]);
	$chkTipoRelM = (LimpiarNumero($_POST["chkTipoRelM"]) == '1' ? true : false);
	$chkTipoRelJ = (LimpiarNumero($_POST["chkTipoRelJ"]) == '1' ? true : false);
	$chkTipoRelC = (LimpiarNumero($_POST["chkTipoRelC"]) == '1' ? true : false);
	$chkTipoRelL = (LimpiarNumero($_POST["chkTipoRelL"]) == '1' ? true : false);
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
	<H1>Listado de Seguros de Vida</H1>
	<div id=divLoading style="display:block">
		<table height=100% align=center valign=center>
			<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
		</table>
	</div>
<?
$sql ='

SELECT e."Legajo",
       e."Nombre",
       e."Apellido",
       e."TipoRelacion",
       ed."NumeroDocumento",
       ed."FechaNacimiento",
(COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) as "Sueldo",
       (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) < 536.48) THEN 536.48 ELSE (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) > 3025.75) THEN 3025.75 ELSE (COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) END ) END) as "SueldoSujetoADesc",

       ((CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) < 536.48) THEN 536.48 ELSE (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) > 3025.75) THEN 3025.75 ELSE
        (COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) END ) END) * (CASE WHEN (EXTRACT(\'year\' FROM (AGE(p."FechaPeriodo", ed."FechaNacimiento" :: timestamp)))) <= 70 THEN 10 ELSE  0 END )) as "Capital",

       ((CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) < 536.48) THEN 536.48 ELSE (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) > 3025.75) THEN 3025.75 ELSE
        (COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) END ) END) * (CASE WHEN (EXTRACT(\'year\' FROM (AGE(p."FechaPeriodo", ed."FechaNacimiento" :: timestamp)))) <= 70 THEN 0.00466 ELSE  0 END ))  as "Prima",

       MAX(ef."Nombres") as "ConyugeNombre",
       MAX(ef."Apellido") as "ConyugeApellido",
       MAX(ef."NumeroDocumento") as "ConyugeNumeroDocumento",
       MAX(ef."FechaNacimiento") as "ConyugeFechaNacimiento",

       0 as "ConyugeSueldoSujetoADesc",


       ((CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) < 536.48) THEN 536.48 ELSE (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) > 3025.75) THEN 3025.75 ELSE
        (COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) END ) END) * (CASE WHEN (EXTRACT(\'year\' FROM (AGE(p."FechaPeriodo", ed."FechaNacimiento" :: timestamp)))) <= 70 THEN 10 ELSE  0 END ) * 0.5) as "ConyugeCapital",

       ((CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) < 536.48) THEN 536.48 ELSE (CASE WHEN ((COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) > 3025.75) THEN 3025.75 ELSE
        (COALESCE(SUM(re."Haber1"),0) + COALESCE(SUM(re."Haber2"),0)) END ) END) * (CASE WHEN (EXTRACT(\'year\' FROM (AGE(p."FechaPeriodo", ed."FechaNacimiento" :: timestamp)))) <= 70 THEN 0.00466 ELSE  0 END ) * 0.5) as "ConyugePrima"


  FROM "tblEmpleados" e
  INNER JOIN "tblEmpleadosDatos" ed ON ed."Legajo" = e."Legajo"
  INNER JOIN "tblRecibos" re ON re."Legajo" = e."Legajo" AND re."Fecha" = \''."$dAno-$dMes-01".'\' AND re."ConceptoID" in (1,10)
  INNER JOIN "tblRecibos" re2 ON re2."Legajo" = e."Legajo" AND re2."Fecha" = re."Fecha" AND re2."AliasID" = 33
  INNER JOIN "tblPeriodos" p ON p."NumeroLiquidacion" = re."NumeroLiquidacion" AND p."FechaPeriodo" = re."Fecha"
  LEFT JOIN "tblRecibos" re3 ON re3."Legajo" = e."Legajo" AND re3."Fecha" = re."Fecha" AND re3."AliasID" = 34
  LEFT JOIN "tblEmpleadosFamiliares" ef ON ef."Legajo" = re3."Legajo" AND ef."TipoDeVinculo" = 1

  WHERE '.(empty($TipoRelacion) ? '1=1 ' : ' e."TipoRelacion" in ('.$TipoRelacion.')'). '

        GROUP BY e."Legajo",
                 e."Nombre",
                 e."Apellido",
                 e."TipoRelacion",
                 e."TipoRelacion",
                 ed."NumeroDocumento",
                 ed."FechaNacimiento",
                 p."FechaPeriodo"
        ORDER BY to_number(e."Legajo", \'99999\');
	';

$rs = pg_query($db, $sql);
	
	if (!$rs){
		exit;
	} ?>
    
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
    <tr>
        <th>Legajo</th>
        <th>Apellido y Nombres</th>
        <th>DNI</th>
        <th>Fecha Nacimiento</th>
        <th>Sueldo Sujeto a Desc.</th>
        <th>Capital</th>
        <th>Prima</th>
    </tr>
    
<?php	
	while($row = pg_fetch_array($rs))
	{
?>	
     <tr>
     	<td style ="font-weight: bold;"><?=$row["Legajo"]?></td>
     	<td><?=$row["Apellido"].", ".$row["Nombre"]?></td>
        <td><?=$row["NumeroDocumento"]?></td>
        <td><?=$row["FechaNacimiento"]?></td>
        <td><?=number_format($row["SueldoSujetoADesc"],2,'.','')?></td>
        <td><?=number_format($row["Capital"],2,'.','')?></td>
        <td><?=number_format($row["Prima"],2,'.','')?></td>
     </tr>
     <?php if (!is_null($row["ConyugeNombre"]) && !is_null($row["ConyugeApellido"])){?>
     
	 <tr>
     	<td> - </td>
     	<td><?=$row["ConyugeApellido"].", ".$row["ConyugeNombre"]?></td>
        <td><?=$row["ConyugeNumeroDocumento"]?></td>
        <td><?=$row["ConyugeFechaNacimiento"]?></td>
        <td><?=number_format($row["SueldoSujetoADesc"],2,'.','')?></td>
        <td><?=number_format($row["ConyugeCapital"],2,'.','')?></td>
        <td><?=number_format($row["ConyugePrima"],2,'.','')?></td>
        <td></td>
     </tr> 
		 	
	 <?php }?>
<?php }
	//exit;
?>
</table>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
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
	<H1>Listado de Seguros de Vida</H1>
	<table class="datauser" align="left">
	<TR>
		<TD class="izquierdo">Seleccione Per&iacute;odo:</TD><TD class="derecho2"><select id=selPeriodo name=selPeriodo>
<?
	while($row = pg_fetch_array($rs)){
		$dAno = $row[0];
		$dMes = Mes($row[1]);
		print "<option value=$row[0]|$row[1]>$dMes DE $dAno</option>\n";
	}
?>
	</select></TD></TR>
	<TR>
		<TD class="izquierdo">Tipo De Relaci&oacute;n:</TD><TD class="derecho2">
		<input type=checkbox id=chkTipoRelM name=chkTipoRelM value=1 checked>Mensualizados
		<input type=checkbox id=chkTipoRelJ name=chkTipoRelJ value=1 checked>Jornalizados
		<input type=checkbox id=chkTipoRelC name=chkTipoRelC value=1 checked>Contratados
		<input type=checkbox id=chkTipoRelL name=chkTipoRelL value=1 checked>Loc. de obra
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho">
		<input type=submit id=accion name=accion value="Ver Listado">
		<? Volver(); ?>
		</TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
