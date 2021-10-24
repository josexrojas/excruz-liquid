<?

require_once('funcs.php');
EstaLogeado();

$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado'){
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}
include('header.php');

if (!($db = Conectar()))
	exit;

$Fecha = date("dmy");
$Hora = date("Hi");

$arch = "UPCN$Fecha.txt";

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>

<script>
<!--
	function BajarListado(sArch){
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmListadoUPCN.submit();
	}
	function MM_openBrWindow(theURL,winName,features) { //v2.0
	  window.open(theURL,winName,features);
}
//-->
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoUPCN action=listadoUPCN.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?

if ($accion == 'Ver Listado'){
	$selPeriodo = $_POST["selPeriodo"];
	$i = strpos($selPeriodo, '|');
	if ($i !== false){
		$FechaPeriodo = LimpiarNumero2(substr($selPeriodo, 0, $i));
		$NumeroLiquidacion = LimpiarNumero(substr($selPeriodo, $i+1));
	}
	if ($FechaPeriodo == '' || $NumeroLiquidacion == ''){
		exit;
	}

	if ($_SESSION["LegajoNumerico"] == '1'){
		$sqlLegajo = "to_number(re.\"Legajo\", '999999') AS \"Legajo\"";
	}else{
		$sqlLegajo = "re.\"Legajo\"";
	}

	$Jur = LimpiarNumero($_POST["chkJurisdiccion"]);
	$Act = LimpiarNumero($_POST["chkActividad"]);
	$Pro = LimpiarNumero($_POST["chkPrograma"]);
	$Orden = 'ORDER BY ';
	$filJurisdiccion = LimpiarNumero($_POST["filJurisdiccion"]);
	$Where = '';
	if ($filJurisdiccion != '0') {
		$Where = " and er.jurisdiccion = '$filJurisdiccion' ";
	}
	if ($Jur == '1')
		$Orden .= '2, ';
	if ($Pro == '1')
		$Orden .= '4, ';
	if ($Act == '1')
		$Orden .= '3, ';
	$Orden = substr($Orden, 0, -2);
	if ($Orden == 'ORDER B')
		$Orden = '';
	
	$sql = "
select distinct $sqlLegajo, er.jurisdiccion, er.activ_proy, er.programa, em.\"Apellido\", em.\"Nombre\",

(select sum(\"Haber1\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\"
and re1.\"ConceptoID\" = 99) as \"SueldoBruto\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (84)) as \"PrestamoUPCN\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (35)) as \"Sindicato\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (74)) as \"Mutual\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (26)) as \"BonoSolidario\",

(select sum(\"Descuento\") from \"tblRecibos\" re1 
where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
and re1.\"AliasID\" in (87)) as \"Turismo\"

from \"tblRecibos\" re 

inner join \"tblEmpleados\" em
on em.\"EmpresaID\" = re.\"EmpresaID\" AND em.\"SucursalID\" = re.\"SucursalID\" and em.\"Legajo\" = re.\"Legajo\"

inner join \"tblEmpleadosRafam\" er
on er.\"EmpresaID\" = re.\"EmpresaID\" AND er.\"SucursalID\" = re.\"SucursalID\" and er.\"Legajo\" = re.\"Legajo\" $Where

where re.\"EmpresaID\" = $EmpresaID AND re.\"SucursalID\" = $SucursalID AND 
re.\"Fecha\" = '$FechaPeriodo' AND re.\"NumeroLiquidacion\" = $NumeroLiquidacion 

AND (
     (
	 	select sum(\"Descuento\") from \"tblRecibos\" re1 
	     where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
	     and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
	     and re1.\"AliasID\" in (84)
     ) > 0

     OR (
         (
		 	select sum(\"Descuento\") from \"tblRecibos\" re1 
	         where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
	         and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
	         and re1.\"AliasID\" in (35)
         ) > 0)
     
	 
	 OR (
         (select sum(\"Descuento\") from \"tblRecibos\" re1 
         where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
         and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
         and re1.\"AliasID\" in (74)
         ) > 0)
     
	 
	 OR (
         (select sum(\"Descuento\") from \"tblRecibos\" re1 
         where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
         and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
         and re1.\"AliasID\" in (26)
         ) > 0)
		 
	OR (
         (select sum(\"Descuento\") from \"tblRecibos\" re1 
         where re1.\"EmpresaID\" = re.\"EmpresaID\" and re1.\"SucursalID\" = re.\"SucursalID\" and re1.\"Fecha\" = re.\"Fecha\"
         and re1.\"NumeroLiquidacion\" = re.\"NumeroLiquidacion\" and re1.\"Legajo\" = re.\"Legajo\" 
         and re1.\"AliasID\" in (87)
         ) > 0)

)
     

$Orden
";
	
	#print "<br><br>".$sql."<br><br>";
		
	$rs = pg_query($db, $sql) or die(pg_last_error());
	if (!$rs){
		exit;
	}
	
?>
<H1>Listado de liquidaciones de UPCN</H1>
	<!--<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>
	&nbsp;&nbsp; -->
	<a class="tecla" href='javascript:window.print(); void(0);'> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<br><br>
<?
	$TotalR1 = 0; $TotalR2 = 0; $TotalR3 = 0; $TotalR4 = 0; $TotalR5 = 0; $TotalR6 = 0; $TotalR7 = 0;
	$TotalR8 = 0; $TotalR9 = 0; $TotalR10 = 0; $TotalR11 = 0; $TotalR12 = 0; $TotalR13 = 0; $TotalR14 = 0;
	$TotalR15 = 0; $TotalR16 = 0; $TotalR17 = 0; $TotalR18 = 0; $TotalR19 = 0;
	$Jurisdiccion = '';
	$Actividad = '';
	$Programa = '';
	$AntJur = '';
	$AntAct = '';
	$AntPro = '';
	$Abrir = 1;
	print "<br><b>Per&iacute;odo: " . Mes(substr($FechaPeriodo, 5, 2)) . " de " . substr($FechaPeriodo, 0, 4);
	print "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero de Liquidaci&oacute;n: $NumeroLiquidacion</b><br><br>";

	$cont = 0;
	$Detalle = "";
	while($row = pg_fetch_array($rs))
	{
		$i=1;

		if ($Jur == '1')
			$Jurisdiccion = $row[1];
		if ($Act == '1')
			$Actividad = $row[2];
		if ($Pro == '1')
			$Programa = $row[3];
		if ($Jurisdiccion != '' && $AntJur != $Jurisdiccion){
			if ($AntJur != '')
				$Cerrar = 1;
		}
		if ($Programa != '' && $AntPro != $Programa){
			if ($AntPro != '')
				$Cerrar = 1;
		}
		if ($Actividad != '' && $AntAct != $Actividad){
			if ($AntAct != '')
				$Cerrar = 1;
		}
		if ($Cerrar == 1){
			$Cerrar = 0;
?>
			<tr><td></th><td><b>Totales</b></td><td><b><?=$TotalR1?></b></td><td><b><?=$TotalR2?></b></td><td><b><?=$TotalR3?></b></td><td><b><?=$TotalR4?></b></td><td><b><?=$TotalR5?></b></td><td><b><?=$TotalR16?></b></td><td><b><?=$TotalR6?></b></td><td><b><?=$TotalR7?></b></td><td><b><?=$TotalR17?></b></td><td><b><?=$TotalR8?></b></td><td><b><?=$TotalR9?></b></td><td><b><?=$TotalR10?></b></td><td><b><?=$TotalR11?></b></td><td><b><?=$TotalR12?></b></td><td><b><?=$TotalR13?></b></td><td><b><?=$TotalR15?></b></td><td><b><?=$TotalR14?></b></td><td><b><?=$TotalR18?></b></td></tr>
<?
			$TotalR1 = 0; $TotalR2 = 0; $TotalR3 = 0;
			
			print "</table><br>\n";
		}

		if ($Abrir == 1){
			$Abrir = 0;
?>
			<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
            <tr><th>Leg</th><th>Apellido y Nombre</th><th>Sueldo Bruto</th><th>BONO SOLIDARIO</th><th>SIND. 2%</th><th>MUTUAL 3%</th><th>PRESTAMO</th><th>TURISMO</th></tr>
                        
<?
		}
		$R1 = FormatearImporte($row['SueldoBruto']);
		$R2 = FormatearImporte($row['PrestamoUPCN']);
		$R3 = FormatearImporte($row['Sindicato']);
		$R4 = FormatearImporte($row['Mutual']);
		$R5 = FormatearImporte($row['BonoSolidario']);
		$R6 = FormatearImporte($row['Turismo']);

		if ($R1 == '') $R1 = 0;
		if ($R2 == '') $R2 = 0;
		if ($R3 == '') $R3 = 0;
		if ($R4 == '') $R4 = 0;
		if ($R5 == '') $R5 = 0;
		if ($R6 == '') $R6 = 0;
		
		$TotalR1 += $R1; $TotalR2 += $R2; $TotalR3 += $R3; $TotalR4 += $R4; $TotalR5 += $R5;  $TotalR6 += $R6;  
		
		$ApeyNom = $row['Apellido'] . ' ' . $row['Nombre'];
?>
		<tr><td><?=$row['Legajo']?></td><td><?=$ApeyNom?></td><td><?=$R1?></td><td><?=$R5?></td><td><?=$R3?></td><td><?=$R4?></td><td><?=$R2?></td><td><?=$R6?></td></tr>
<?
		if ($R1 == 0) $R1 = '0';
		if ($R2 == 0) $R2 = '0';
		if ($R3 == 0) $R3 = '0';
		if ($R4 == 0) $R4 = '0';
		if ($R5 == 0) $R5 = '0';
		if ($R6 == 0) $R6 = '0';
		
		if ($cont == 0)
		{
			#Se arma el encabezado del archivo a bajar
			$Detalle .= "  ".str_pad("Legajo", 10, ' ', STR_PAD_RIGHT);
			$Detalle .= "  ".str_pad("Nombre", 35, ' ', STR_PAD_RIGHT);
			$Detalle .= "  ".str_pad("Bruto", 10, ' ', STR_PAD_LEFT);
			$Detalle .= "   ".str_pad("BONO SOLIDARIO", 8, ' ', STR_PAD_LEFT);
			$Detalle .= "   ".str_pad("SIND. 2%", 8, ' ', STR_PAD_LEFT);
			$Detalle .= "   ".str_pad("MUTUAL 3%", 8, ' ', STR_PAD_LEFT);
			$Detalle .= "   ".str_pad("PRESTAMO", 8, ' ', STR_PAD_LEFT);
			$Detalle .= "   ".str_pad("TURISMO", 8, ' ', STR_PAD_LEFT)."\r\n\r\n";
			
		}
		
		#Se arma el cuerpo principal del archivo a bajar
		$Detalle .= "  ".str_pad($row['Legajo'], 10, ' ', STR_PAD_RIGHT);
		$Detalle .= "  ".str_pad($ApeyNom, 35, ' ', STR_PAD_RIGHT);
		$Detalle .= "  ".str_pad($R1, 10, ' ', STR_PAD_LEFT);
		$Detalle .= "   ".str_pad($R5, 8, ' ', STR_PAD_LEFT);		
		$Detalle .= "   ".str_pad($R3, 8, ' ', STR_PAD_LEFT);
		$Detalle .= "   ".str_pad($R4, 8, ' ', STR_PAD_LEFT);
		$Detalle .= "   ".str_pad($R2, 8, ' ', STR_PAD_LEFT);
		$Detalle .= "   ".str_pad($R6, 8, ' ', STR_PAD_LEFT)."\r\n";

	$cont++;
	}

	#Se arman los totales del archivo a bajar
	$Detalle .= "\r\n  ".str_pad(" ", 10, ' ', STR_PAD_RIGHT);
	$Detalle .= "  ".str_pad("Totales", 35, ' ', STR_PAD_BOTH);
	$Detalle .= "  ".str_pad($TotalR1, 10, ' ', STR_PAD_LEFT);
	$Detalle .= "   ".str_pad($TotalR5, 8, ' ', STR_PAD_LEFT);
	$Detalle .= "   ".str_pad($TotalR3, 8, ' ', STR_PAD_LEFT);
	$Detalle .= "   ".str_pad($TotalR4, 8, ' ', STR_PAD_LEFT);
	$Detalle .= "   ".str_pad($TotalR2, 8, ' ', STR_PAD_LEFT);
	$Detalle .= "   ".str_pad($TotalR6, 8, ' ', STR_PAD_LEFT)."\r\n";

	/*$fp = fopen('../listados/'.$arch, 'wb');
	fputs($fp, $Detalle); 
	fclose($fp);*/

?>
			<tr><td></th><td><b>Totales</b></td><td><b><?=$TotalR1?></b></td><td><b><?=$TotalR5?></b></td><td><b><?=$TotalR3?></b></td><td><b><?=$TotalR4?></b></td><td><b><?=$TotalR2?></b></td><td><b><?=$TotalR6?></b></td></tr>
	</table>
<?
}

if ($accion == ''){
	?><H1>Listado de Liquidaciones de UPCN</H1><?
	include 'selLiquida.php'; ?>

	<TR>
		<TD class="izquierdo">Filtrar Jurisdicci&oacute;n:</TD><TD class="derecho">
			<SELECT name=filJurisdiccion>
			<option value=0>Todas las jurisdicciones</option>
<?
	$rs = pg_query($db, "select jurisdiccion, denominacion from owner_rafam.jurisdicciones where seleccionable = 'S'");
	while($row = pg_fetch_array($rs)) {
		print "<OPTION VALUE=\"".$row[0]."\">".$row[1]."</OPTION>\n";
	}
?>
			</SELECT>
		</TD>
	</TR>
	<TR>
		<TD class="izquierdo"></TD><TD class="derecho"><input type=submit id=accion name=accion value="Ver Listado"></TD></TR></table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
</script>
<? include("footer.php"); ?>