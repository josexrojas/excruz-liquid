<? include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

?>
<H1><img src="images/icon64_novedadesMultiple.gif" width="64" height="64" align="absmiddle" /> Carga Multiple De Novedades</H1>

<script language="JavaScript">
	function AgregarNovedad(iCual)
	{
		if (iCual == 0){
			document.frmNovedades.CID.value = '';
			document.frmNovedades.accion.value = 'Agregar Novedad';
			submitEmpleado();
		}else{
			var bError = false;
			try {
				var FechaDesde = new String(document.getElementById('ParamDesde').value);
				var FechaHasta = new String(document.getElementById('ParamHasta').value);
				var dDesde = FechaDesde.split("-");
				var dHasta = FechaHasta.split("-");

				if (dHasta[2] < dDesde[2]){
					bError = true;
				}else{
					if (dHasta[1] < dDesde[1]){
						bError = true;
					}else{
						if (dHasta[0] < dDesde[0])
							bError = true;
					}
				}
			} catch(e) {}
			if (bError){
				alert('La fecha de finalizacion no puede ser menor a la fecha de comienzo');
				return;
			}
			document.frmNovedades.CID.value = document.frmNovedades.selConcepto.options[document.frmNovedades.selConcepto.selectedIndex].value;
			document.frmNovedades.accion.value = 'Aceptar';
		}
		document.frmNovedades.submit();
	}

	var objXMLHttp;
	if (window.ActiveXObject){ //for IE
		objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){ //for Mozilla
		objXMLHttp = new XMLHttpRequest();
	}

	function ObtenerXML(CID, Agregar, ID, Ajuste)
	{
		var objXML;

		objXMLHttp.open("GET", "/ParametrosConcepto.php?CID="+CID+"&Agregar="+Agregar+"&ID="+ID+"&Ajuste="+Ajuste, false, "", "");
		if (window.XMLHttpRequest){
			objXMLHttp.send(null);
		}else{
			objXMLHttp.send();
		}
		if ((objXMLHttp.readyState==4) && (objXMLHttp.status==200)){
			objXML = objXMLHttp.responseTEXT;
			return objXML;		
		}
		return false;
	}
	function VerAjuste(){
		var dvAjuste = document.getElementById('dvAjuste');
		if (dvAjuste.style.display=='none'){
			dvAjuste.style.display='block';
			dvParametros.style.display='none';
		}else{
			dvAjuste.style.display='none';
			dvParametros.style.display='block';
		}
	}
	function CargarParametrosConcepto(CID, Agregar, ID, Ajuste)
	{
		var sTexto = ObtenerXML(CID, Agregar, ID, Ajuste);
	
		document.getElementById('dvCamposForm').innerHTML = sTexto;
	}
</script>
<form name=frmNovedades action=novCargaMultiple.php method=post>
<?
$CID = LimpiarNumero($_POST["CID"]);
print "<input type=hidden id=CID name=CID value=\"$CID\">";
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Aceptar')
{
	if ($CID != ""){
		$rs = pg_query($db, "
SELECT ca.\"ConceptoID\", cp.\"ParametroID\", cp.\"Descripcion\"
FROM \"tblConceptosAlias\" ca
LEFT JOIN \"tblConceptosParametros\" cp
ON ca.\"ConceptoID\" = cp.\"ConceptoID\" AND ca.\"EmpresaID\" = cp.\"EmpresaID\"
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"AliasID\" = $CID ORDER BY cp.\"ParametroID\"");
		if (!$rs){
			pg_close($db);
		}
		$i = 0;
		$val = "";
		while($row = pg_fetch_array($rs)){
			$i++;
			$Con = $row[0];
			$var = LimpiarVariable($_POST["Param$i"]);
			if ($val != "")
				$val .= ', ';
			$val .= $var;		
		}
		$Ajuste = LimpiarNumero($_POST["Ajuste"]);
		if ($Ajuste == ''){
			$chkAjuste = LimpiarNumero($_POST["chkAjuste"]);
			$rdEn = LimpiarNumero($_POST["rdEn"]);
			$Ajuste = ($chkAjuste == 1 ? ($rdEn == 1 ? 1 : 2) : 0);
		}
		if ($Ajuste > 0){
			$val = LimpiarNumero2($_POST["AjusteValor"]);
			if ($Ajuste == 2)
				$val = "-$val";
		}
		$vDesde = FechaWEB2SQL(LimpiarNumero($_POST["ParamDesde"]));
		$vHasta = FechaWEB2SQL(LimpiarNumero($_POST["ParamHasta"]));
		if ($vDesde == "")
			$vDesde = "null";
		else
			$vDesde = "'$vDesde'";
		if ($vHasta == "")
			$vHasta = "null";
		else
			$vHasta = "'$vHasta'";
		if ($val == "")
			$val = "null";
		else
			$val = "'\{$val}'";
		$rs = pg_query($db, "
SELECT em.\"Legajo\", no.\"AliasID\" FROM \"tblEmpleados\" em
LEFT JOIN \"tblNovedades\" no
ON no.\"EmpresaID\"=em.\"EmpresaID\" AND no.\"SucursalID\"=em.\"SucursalID\" AND
no.\"Legajo\"=em.\"Legajo\" AND no.\"AliasID\"=$CID AND no.\"Ajuste\" = $Ajuste
WHERE em.\"EmpresaID\"=$EmpresaID AND em.\"SucursalID\"=$SucursalID AND em.\"FechaEgreso\" IS NULL");
		$iCant = 0;
		while($row = pg_fetch_array($rs)){
			$ID = $row[0];
			$bActualiza = ($row[1] == '' ? false : true);
			if ($bActualiza){
				$sql = "
UPDATE \"tblNovedades\" SET \"FechaDesde\" = $vDesde, \"FechaHasta\" = $vHasta, \"Valores\" = $val
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Legajo\" = '$ID' AND \"AliasID\" = $CID
AND \"Ajuste\" = $Ajuste";
				pg_exec($db, $sql);
			}else{
				$sql = "
INSERT INTO \"tblNovedades\" VALUES ($Con, $EmpresaID, $SucursalID, '$ID', $CID, $vDesde, $vHasta, $val, $Ajuste, true)";
				pg_exec($db, $sql);
			}
			$iCant++;
		}
		Alerta("Se cargaron $iCant novedades con exito");
		$accion = '';
	}
}

if ($accion == ''){
	$rs = pg_query($db, "
SELECT ca.\"AliasID\", ca.\"Descripcion\" FROM \"tblConceptosAlias\" ca
INNER JOIN \"tblConceptos\" co
ON co.\"ConceptoID\"=ca.\"ConceptoID\" AND co.\"Obligatorio\"=false AND co.\"Activo\"=true
WHERE ca.\"EmpresaID\" = $EmpresaID AND ca.\"Liquida\" > 0 ORDER BY ca.\"Descripcion\"");
	if (!$rs)
	{
		pg_close($db);
		exit;
	}
	$Ajuste = LimpiarNumero($_POST["Ajuste"]);
?>
	<b>Se agregar&aacute; la novedad a todos los empleados</b><br><br>
	<table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo">Seleccione un concepto:</TD>
		<TD class="derecho"><select  id=selConcepto onchange="javascript:CargarParametrosConcepto(this.options[selectedIndex].value, 1, '', '<?=$Ajuste?>')";>
<?
	while($row = pg_fetch_array($rs))
	{
		$AliasID = $row[0];
		$Descripcion = $row[1];
		print "<option value=\"$AliasID\"";
		if ($AliasID == $CID)
			print " selected";
		print ">$Descripcion</option>";
	}
?>
	</select></TD></TR></table><br><br>
	<div id=dvCamposForm style="position:relative; clear:both;">
		<!-- Se carga dinamica, por XML-->
	</div>
<script>
	var CID = document.frmNovedades.selConcepto.options[document.frmNovedades.selConcepto.selectedIndex].value;
	CargarParametrosConcepto(CID, 1, '', '<?=$Ajuste?>')
</script>
	<input type=hidden name=accion id=accion>
	<input type=hidden name=Ajuste id=Ajuste value="<?=$Ajuste?>">
	<DIV style="clear:both"><table class="datauser" align="left" border="0">
	<TR>
		<TD width="200" class="izquierdo" >&nbsp;</TD><td class=derecho><a href="javascript:AgregarNovedad(1); void(0);" class="tecla"> 
	<img src="images/icon24_grabar.gif" alt="Aceptar" width="24" height="23" border="0" align="absmiddle">  Aceptar </a>
	&nbsp;&nbsp;&nbsp;<a href="novedades.php" class="tecla"> 
	<img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver </a></TD></TR></table></DIV>
	<br />
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
