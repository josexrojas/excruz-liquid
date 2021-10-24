<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$LegajoNumerico = $_SESSION["LegajoNumerico"];

?>
<script>
	var objXMLHttp;
	if (window.ActiveXObject){ //for IE
		objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
	} else if (window.XMLHttpRequest){ //for Mozilla
		objXMLHttp = new XMLHttpRequest();
	}
	function ObtenerXML(iCual, sJurisdiccion, iAgrupamiento, iCategoria)
	{
		var objXML;

		if (iCual == 1){
			objXMLHttp.open("GET", "/RafamAgrupamiento.php?Jurisdiccion="+sJurisdiccion, false, "", "");
		}else if (iCual == 2){
			objXMLHttp.open("GET", "/RafamCategoria.php?Jurisdiccion="+sJurisdiccion+"&Agrupamiento="+iAgrupamiento, false, "", "");
		}else if (iCual == 3){
			objXMLHttp.open("GET", "/RafamCargo.php?Jurisdiccion="+sJurisdiccion+"&Agrupamiento="+iAgrupamiento+"&Categoria="+iCategoria, false, "", "");
		}
		if (window.XMLHttpRequest){
			objXMLHttp.send(null);
		}else{
			objXMLHttp.send();
		}
		if ((objXMLHttp.readyState==4) && (objXMLHttp.status==200)){
			objXML = objXMLHttp.responseXML;
			return objXML;		
		}
		return false;
	}
	function RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, iCargo){
		var sJuris = sJurisdiccion.substring(0, 5) + '00000';
		var selAgrup, selCat, selCargo, oSelect, i;

		if (iAgrupamiento == 0){
			selAgrup = document.getElementById('selAgrupamiento');
			sJCC = ObtenerXML(1, sJuris, iAgrupamiento, iCategoria);
			for (i = selAgrup.options.length; i >= 0; i--)
				selAgrup.options[i] = null;

			if (sJCC.documentElement.childNodes.length > 0){
				for (i = 0; i < sJCC.documentElement.childNodes.length; i++) {
					selAgrup.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
						sJCC.documentElement.childNodes[i].getAttribute("id"));
				}
				selAgrup.disabled = false;
				iAgrupamiento = sJCC.documentElement.childNodes[0].getAttribute("id");
				RafamCombos(sJurisdiccion, iAgrupamiento, 0, 0);
			}else{
				selAgrup.options[0] = new Option("Elija un agrupamiento", 0);
				selAgrup.disabled = true;
			}
		}else{
			// Seleccionar agrupamiento
			oSelect = document.getElementById('selAgrupamiento');
			for(i=0;i<oSelect.options.length;i++){
				if (oSelect.options[i].value == iAgrupamiento){
					oSelect.options[i].selected = true;
					break;
				}
			}

			if (iCategoria == 0){
				selCat = document.getElementById('selCategoria');
				sJCC = ObtenerXML(2, sJuris, iAgrupamiento, iCategoria);
				for (i = selCat.options.length; i >= 0; i--)
					selCat.options[i] = null;

				if (sJCC.documentElement.childNodes.length > 0){
					for (i = 0; i < sJCC.documentElement.childNodes.length; i++) {
						selCat.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
							sJCC.documentElement.childNodes[i].getAttribute("id"));
					}
					selCat.disabled = false;
					iCategoria = sJCC.documentElement.childNodes[0].getAttribute("id");
					RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, 0);
				}else{
					selCat.options[0] = new Option("Elija una categoria", 0);
					selCat.disabled = true;
				}
			}else{
				// Seleccionar categoria
				oSelect = document.getElementById('selCategoria');
				for(i=0;i<oSelect.options.length;i++){
					if (oSelect.options[i].value == iCategoria){
						oSelect.options[i].selected = true;
						break;
					}
				}
				if (iCargo == 0){
					selCargo = document.getElementById('selCargo');
					sJCC = ObtenerXML(3, sJuris, iAgrupamiento, iCategoria);
					for (i = selCargo.options.length; i >= 0; i--)
						selCargo.options[i] = null;

					if (sJCC.documentElement.childNodes.length > 0){
						for (i = 0; i < sJCC.documentElement.childNodes.length; i++) {
							selCargo.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
								sJCC.documentElement.childNodes[i].getAttribute("id"));
						}
						selCargo.disabled = false;
						iCargo = sJCC.documentElement.childNodes[0].getAttribute("id");
						RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, iCargo);
					}else{
						selCargo.options[0] = new Option("Elija un cargo", 0);
						selCargo.disabled = true;
					}
				}else{
					// Seleccionar cargo
					oSelect = document.getElementById('selCargo');
					for(i=0;i<oSelect.options.length;i++){
						if (oSelect.options[i].value == iCargo){
							oSelect.options[i].selected = true;
							break;
						}
					}
				
					// Lista sueldos basicos
					alert('Cambio Cargo');
				}
			}
		}
	}
</script>

<form name=frmSueldoBasico action=emplSueldoBasico.php method=post>
<?
$accion = LimpiarVariable($_POST["accion"]);
if ($accion == 'Continuar'){
}

if ($accion == 'Cancelar' || $accion == ''){
?>
	<table class=datauser align="left">
	<TR>
	<TD class="izquierdo">Seleccione Jurisdicci&oacute;n:</td><TD class=derecho2><select id=selJurisdiccion name=selJurisdiccion onchange="javascript:RafamCombos(this.options[selectedIndex].value, 0, 0, 0);">
<?
	$rs = pg_query($db, "
SELECT DISTINCT substr(jurisdiccion, 1, 5) || '00000' as jurisdiccion
FROM owner_rafam.jurisdicciones 
WHERE seleccionable='S'
ORDER BY 1");
	if (!$rs){
		exit;
	}
	while($row = pg_fetch_array($rs)){
		print "<option value=$row[0]>$row[0]</option>\n";
	}
?>
	</select></td></tr>
	<tr>
	<TD class="izquierdo">Agrupamiento:</td><TD class=derecho2><select id=selAgrupamiento name=selAgrupamiento disabled onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, this.options[selectedIndex].value, 0, 0);">
	<option value=0>Elija un agrupamiento</option>
	</select></td></tr>
	<tr>
	<TD class="izquierdo">Categoria:</td><TD class=derecho2><select id=selCategoria name=selCategoria disabled 
		onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, 
			selAgrupamiento.options[selAgrupamiento.selectedIndex].value, this.options[selectedIndex].value, 0);">
	<option value=0>Elija una categoria</option>
	</select></td></tr>
	<tr>
	<TD class="izquierdo">Cargo:</td><TD class=derecho2><select id=selCargo name=selCargo disabled 
		onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, 
			selAgrupamiento.options[selAgrupamiento.selectedIndex].value, selCategoria.options[selCategoria.selectedIndex].value, this.options[selectedIndex].value);">
	<option value=0>Elija un cargo</option>
	</select></td></tr>
	</table>
	<script>
		var selJur = document.frmSueldoBasico.selJurisdiccion;
		RafamCombos(selJur.options[selJur.selectedIndex].value, 0, 0, 0);
	</script>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
