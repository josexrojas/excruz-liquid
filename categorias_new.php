<?php 
include ('header.php');

if (!($db = Conectar()))
	exit;

$categoria		= $_REQUEST['selCategoria'];
$horas			= $_REQUEST['horas'];
$sueldo			= $_REQUEST['sueldo'];
$bonif 			= $_REQUEST['bonificacion'];
$Action			= $_REQUEST['action'];

if ($Action == 'new')
{
	if ($categoria != NULL AND $horas != NULL AND $sueldo != NULL)
	{
		$sql = pg_query ($db, "INSERT INTO \"tblCategorias\" VALUES (1, $categoria, $horas, $sueldo);");
		if ($sql == false)
			{
				print "No se han cargado los datos. El dato que intenta ingresar ya existe.";
			}
		else{
				print "Se han guardado los datos correctamente";
			}
	}
	else{
			echo "Debe seleccionar todos los valores";
		}
}

?>
<H1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Categorias</H1>
<h5>Insertar nueva Categor&iacute;a:</h5>
<br />

<form name="frmData" action="categorias_new.php" method="post">
<input type="hidden" name="action" value="new">

<table>
	<tr style="display2:none;">
		<TD class="izquierdo">Jurisdicci&oacute;n:</td>
		<TD class=derecho>
			<select id=selJurisdiccion name=selJurisdiccion multiple onchange="javascript:RafamCombos(this.options[selectedIndex].value, 0, 0, 0);">
			<option value=0>Elija jurisdicci&oacute;n</option>
			<?
			$rs = pg_query($db, "
				SELECT jurisdiccion, denominacion
				FROM owner_rafam.jurisdicciones
				WHERE seleccionable = 'S'
				ORDER BY 1");
			if (!$rs)
				exit;
			while($row = pg_fetch_array($rs))
			{
				print "<option value=$row[0]";
				//if ($Jurisdiccion == '')
					//$Jurisdiccion = $row[0];
				if ($Jurisdiccion == $row[0])
					print " selected";
				if ($row[1] == '')
					print ">$row[0]</option>\n";
				else
					print ">$row[1]</option>\n";
			}
			?>
			</select>
		</td>
	</tr>	
	
	<tr>
		<TD class="izquierdo">Agrupamiento:</td>
		<TD class=derecho>
			<select id=selAgrupamiento name=selAgrupamiento disabled onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, this.options[selectedIndex].value, 0, 0);">
			<option value=0>Elija un agrupamiento</option>
			</select>
		</td>
	</tr>
	
	<tr>
		<TD class="izquierdo">Categoria:</td>
		<TD class=derecho>
			<select id=selCategoria name=selCategoria disabled 
				onchange2="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, 
			selAgrupamiento.options[selAgrupamiento.selectedIndex].value, this.options[selectedIndex].value, 0);">
			<option value=0>Elija una categoria</option>
			</select>
		</td>
	</tr>
	
	
	<tr>
		<td>Cant. Horas:</td>
		<td><select name="horas">
			<option value="">Elija horas</option>
			<option value="6">6 hs</option>
			<option value="7">7 hs</option>
			<option value="8">8 hs</option>
			<option value="9">9 hs</option>
			</select>
		</td>
	</tr>
	
	
	<tr>
		<td>Sueldo B&aacute;sico:&nbsp;&nbsp;&nbsp;$</td>
		<td><input type="text" name="sueldo" size="6" /></td>
	</tr>
	
	
	
	<tr>
		<td><a href="javascript: frmData.submit();" class="tecla"><img src="images/icon24_grabar.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">Aceptar</a></td>
		<td><a href="categorias.php" class="tecla"><img src="images/icon24_prev.gif" alt="Volver" width="24" height="23" border="0" align="absmiddle">  Volver&nbsp;</a></td>
	</tr>
</table>

<script language="javascript">

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
			}
		}
	}
}

RafamCombos('<?=$Jurisdiccion?>', 0, 0, 0);
RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', 0, 0);
RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', '<?=$Categoria?>', 0);
RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', '<?=$Categoria?>', '<?=$Cargo?>');

</script>
</form>

<?php
include ('footer.php');
?>
