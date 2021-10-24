<? include('header.php');

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$accion = LimpiarVariable($_POST["accion"]);
$Anio = date("Y");

if ($accion == 'Aceptar'){
	$chksDisp = LimpiarNumero2($_POST["chksDisp"]);
	$chksAsoc = LimpiarNumero2($_POST["chksAsoc"]);
	$selGasto = LimpiarNumero($_POST["selGasto"]);
	$TipoPlanta = LimpiarNumero($_POST["TipoPlanta"]);
	$AliasDisp = split('-', $chksDisp);
	$AliasAsoc = split('-', $chksAsoc);
	$bError = false;

////
	$for = 1;
////
	for($i=0;$i<count($AliasAsoc);$i++){
		if ($AliasAsoc[$i] == '')
			continue;
		$Inciso = substr($selGasto, 0, 1);
		$Par_Prin = substr($selGasto, 1, 1);
		$Par_Parc = substr($selGasto, 2, 1);
		$Par_Subp = substr($selGasto, 3, 1);
////////////////////////////////////////////////////////////////////////////////
		if ($for == 1)
		{
			$del = pg_query($db, "DELETE FROM \"tblConceptosAliasGastos\" WHERE \"EmpresaID\" = $EmpresaID AND \"Inciso\" = $Inciso AND \"Par_Prin\" = $Par_Prin AND \"Par_Parc\" = $Par_Parc AND \"Par_Subp\" = $Par_Subp");
			if (!$del){
				$bError = true;
				break;
			}
			$for = 0;
		}
////////////////////////////////////////////////////////////////////////////////
		$rs = pg_query($db, "SELECT \"ConceptosAliasGastos\"($EmpresaID, $AliasAsoc[$i], $TipoPlanta::int2, $Inciso::int2, 
			$Par_Prin::int2, $Par_Parc::int2, $Par_Subp::int2)");
		if (!$rs){
			$bError = true;
			break;
		}else{
			$row = pg_fetch_array($rs);
			if ($row[0] == -1){
				$bError = true;
				break;
			}
		}
	}
/*	if (!$bError){
		$Inciso = substr($selGasto, 0, 1);
		$Par_Prin = substr($selGasto, 1, 1);
		$Par_Parc = substr($selGasto, 2, 1);
		$Par_Subp = substr($selGasto, 3, 1);
		for($i=0;$i<count($AliasDisp);$i++){
			if ($AliasDisp[$i] == '')
				continue;
			if (!pg_exec($db, "
DELETE FROM \"tblConceptosAliasGastos\" 
WHERE \"EmpresaID\" = $EmpresaID AND \"AliasID\" = $AliasDisp[$i] AND \"Inciso\" = $Inciso AND \"Par_Prin\" = $Par_Prin 
AND \"Par_Parc\" = $Par_Parc AND \"Par_Subp\" = $Par_Subp")){
				$bError = true;
			}
		}
	}*/
	if ($bError){
		Alerta('Hubo un error en la asociacion de los conceptos');
	}else{
		Alerta('Se asociaron correctamente los conceptos');
	}
	$accion = 'Ver';
}
?>
<form name=frmAliasGastos action=concAliasGastos.php method=post>
<script language="Javascript1.1">
	function Ver(){
		var Gasto = document.getElementById('selGasto');
		if (Gasto.options[Gasto.selectedIndex].text.substring(0,19)=='Personal permanente')
			document.getElementById('TipoPlanta').value = '1';
		else if (Gasto.options[Gasto.selectedIndex].text.substring(0,19)=='Personal temporario')
			document.getElementById('TipoPlanta').value = '2';
		else
			document.getElementById('TipoPlanta').value = '0';
		document.getElementById('accion').value = 'Ver';
		document.frmAliasGastos.submit();
	}
	function Poner(){
		var disp = document.getElementById('selConceptosDisponibles');
		var asoc = document.getElementById('selConceptosAsociados');
		if (disp.selectedIndex == -1){
			alert('Debe seleccionar 1 o mas conceptos disponibles');
			return;
		}
		asoc[asoc.options.length] = new Option(disp.options[disp.selectedIndex].text, disp.options[disp.selectedIndex].value);
		disp.options[disp.selectedIndex] = null;
		Ordenar(asoc);
	}
	function Sacar(){
		var disp = document.getElementById('selConceptosDisponibles');
		var asoc = document.getElementById('selConceptosAsociados');
		if (asoc.selectedIndex == -1){
			alert('Debe seleccionar 1 o mas conceptos asociados');
			return;
		}
		disp[disp.options.length] = new Option(asoc.options[asoc.selectedIndex].text, asoc.options[asoc.selectedIndex].value);
		asoc.options[asoc.selectedIndex] = null;
		Ordenar(disp);
	}
	function Ordenar(oSelect){
		var i, j, arrCopia, oOption;

		arrCopia = new Array();
		for(i=0;i<oSelect.options.length;i++){
			arrCopia[i] = new Array(oSelect[i].text, oSelect[i].value);
		}

		arrCopia.sort(); //function (a,b) { return a[1]>b[1]; });

		for(i=oSelect.options.length-1;i>-1;i--)
			oSelect.options[i] = null;
		for(i=0;i<arrCopia.length;i++){
			oOption = new Option(arrCopia[i][0], arrCopia[i][1]);
			oSelect.options[i] = oOption;
		}
	}
	function Aceptar(){
		var i;
		var sChk = '';
		// Asigna el tipo de planta dependiendo del nombre que figura en el gasto
		var Gasto = document.getElementById('selGasto');
		if (Gasto.options[Gasto.selectedIndex].text.substring(0,19)=='Personal permanente')
			document.getElementById('TipoPlanta').value = '1';
		else if (Gasto.options[Gasto.selectedIndex].text.substring(0,19)=='Personal temporario')
			document.getElementById('TipoPlanta').value = '2';
		else
			document.getElementById('TipoPlanta').value = '0';

		var disp = document.getElementById('selConceptosDisponibles');
		var asoc = document.getElementById('selConceptosAsociados');

		for(i=0;i<disp.options.length;i++){
			if (sChk == ''){
				sChk = disp.options[i].value;
			}else{
				sChk = sChk + '-' + disp.options[i].value;
			}
		}
		document.getElementById('chksDisp').value = sChk;
		sChk = '';
		for(i=0;i<asoc.options.length;i++){
			if (sChk == ''){
				sChk = asoc.options[i].value;
			}else{
				sChk = sChk + '-' + asoc.options[i].value;
			}
		}
		document.getElementById('chksAsoc').value = sChk;
		document.getElementById('accion').value = 'Aceptar';
		document.frmAliasGastos.submit();
	}
</script>

<b>Seleccione un concepto del gasto del presupuesto en RAFAM.</b><br><br>
<input type=hidden name=accion id=accion>
<input type=hidden name=chksDisp id=chksDisp>
<input type=hidden name=chksAsoc id=chksAsoc>
<input type=hidden name=TipoPlanta id=TipoPlanta>
<table cellspacing="0" cellpadding="0" border="0"><tr height="30"><TD height="30">
<select id=selGasto name=selGasto onchange="javascript:Ver();">
<?
// Query del infierno
$rs = pg_query($db, "
SELECT s2.anio_presup, s2.inciso, s2.par_prin, s2.par_parc, s2.par_subp, trim(g1.denominacion) || ' : ' || trim(s2.denominacion)
FROM owner_rafam.gastos g1,
(
SELECT s1.anio_presup, s1.inciso, s1.par_prin, s1.par_parc, s1.par_subp, trim(g1.denominacion) || ' : ' || trim(s1.denominacion) as denominacion
FROM owner_rafam.gastos g1,
(
SELECT g1.anio_presup, g1.inciso, g1.par_prin, g1.par_parc, g1.par_subp, g1.denominacion
FROM owner_rafam.gastos g1
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin <> 0 and g1.par_parc <> 0 and g1.par_subp<>0 and g1.totalizadora = 'N'
) s1
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin = s1.par_prin and g1.par_parc = s1.par_parc and g1.par_subp=0 and g1.totalizadora = 'S'
) s2
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin = s2.par_prin and g1.par_parc = 0 and g1.par_subp=0 and g1.totalizadora = 'S'

UNION

SELECT s1.anio_presup, s1.inciso, s1.par_prin, s1.par_parc, s1.par_subp, trim(g1.denominacion) || ' : ' || trim(s1.denominacion)
FROM owner_rafam.gastos g1,
(
SELECT g1.anio_presup, g1.inciso, g1.par_prin, g1.par_parc, g1.par_subp, g1.denominacion
FROM owner_rafam.gastos g1
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin <> 0 and g1.par_parc <> 0 and g1.par_subp=0 and g1.totalizadora = 'N'
) s1
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin = s1.par_prin and g1.par_parc = 0 and g1.par_subp=0 and g1.totalizadora = 'S'

UNION

SELECT g1.anio_presup, g1.inciso, g1.par_prin, g1.par_parc, g1.par_subp, g1.denominacion
FROM owner_rafam.gastos g1
WHERE g1.anio_presup = $Anio and g1.inciso = 1 and g1.par_prin <> 0 and g1.par_parc = 0 and g1.par_subp=0 and g1.totalizadora = 'N'

UNION

SELECT g1.anio_presup, g1.inciso, g1.par_prin, g1.par_parc, g1.par_subp, g1.denominacion
FROM owner_rafam.gastos g1
WHERE g1.anio_presup = $Anio and g1.inciso = 3 and g1.par_prin = 4 and g1.par_parc = 2 and g1.par_subp=0 and g1.totalizadora = 'N'

ORDER BY 1,2,3,4,5
");
if (!$rs){
	exit;
}
$selGasto = LimpiarNumero($_POST["selGasto"]);
$arrGastos = Array();
while($row = pg_fetch_array($rs)){
	$Valor = "$row[1]$row[2]$row[3]$row[4]";
	// Selecciona el primer valor si no tiene nada asignado
	if ($selGasto == '')
		$selGasto = $Valor;
	print "<option value=\"$Valor\"";
	if ($Valor == $selGasto)
		print " selected";
	print ">$row[5]</option>\n";
	$arrGastos[$Valor] = $row[5];
}
?>
</select></td></tr></table><br />
<?
	$TipoDePlanta = LimpiarNumero($_POST["TipoPlanta"]);
	if ($TipoDePlanta != '1' && $TipoDePlanta != '2')
		$TipoDePlanta = '1';
	$Inciso = substr($selGasto, 0, 1);
	$Par_Prin = substr($selGasto, 1, 1);
	$Par_Parc = substr($selGasto, 2, 1);
	$Par_Subp = substr($selGasto, 3, 1);
	$rs = pg_query($db, "
SELECT ca.\"Descripcion\", ca.\"AliasID\", (CASE WHEN (cag.\"Inciso\"::varchar || cag.\"Par_Prin\"::varchar || cag.\"Par_Parc\"::varchar || cag.\"Par_Subp\"::varchar)='$selGasto' THEN 1 ELSE 0 END)
FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON ca.\"EmpresaID\" = co.\"EmpresaID\" AND co.\"ClaseID\" in (0,1,2,4) AND ca.\"ConceptoID\" = co.\"ConceptoID\"
LEFT JOIN \"tblConceptosAliasGastos\" cag
ON ca.\"EmpresaID\" = cag.\"EmpresaID\" AND cag.\"AliasID\" = ca.\"AliasID\" AND cag.\"TipoDePlanta\" = $TipoDePlanta
WHERE ca.\"EmpresaID\" = $EmpresaID
ORDER BY 3");
?>
	<br><br>
	<table><tr><td>
	<b>Conceptos Disponibles</b><br><br>
	<select id=selConceptosDisponibles size=10>
<?
	$bMas = false;
	while($row = pg_fetch_array($rs)){
		if ($row[2] == '1'){
			$bMas = true;
			break;
		}
		print "<option value=\"$row[1]\">$row[0]</option>\n";
	}
?>
	</select>
	</td><td>
	<input type=button value="-->" onclick="javascript:Poner();"><br>
	<input type=button value="<--" onclick="javascript:Sacar();">
	</td><td>
	<b>Conceptos Asociados</b><br><br>
	<select id=selConceptosAsociados size=10 width="100">
<?
	if ($bMas){
		print "<option value=\"$row[1]\">$row[0]</option>\n";
		while($row = pg_fetch_array($rs)){
			print "<option value=\"$row[1]\">$row[0]</option>\n";
		}
	}
?>
	</select>
	</td></tr></table>
	<script>
		Ordenar(document.getElementById('selConceptosDisponibles'));
		Ordenar(document.getElementById('selConceptosAsociados'));
	</script>
	<br><br>
	<a class=tecla href="javascript:Aceptar();void(0);">
	<img src="images/icon24_grabar.gif" alt="Guardar Cambios" align="absmiddle" width="24" height="24" border="0"> Guardar Cambios </a>&nbsp;&nbsp;&nbsp;		
	<a class=tecla href="conceptos.php">
	<img src="images/icon24_prev.gif" alt="Volver" align="absmiddle" width="24" height="24" border="0"> Volver </a>
	
<?
pg_close($db);
?>
</table><br><br>
</form>
<? include('footer.php'); ?>
