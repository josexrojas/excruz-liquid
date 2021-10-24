<? include("header.php");

if (!($db = Conectar()))
	exit;

$SEGURIDAD_MODULO_ID = 1;

$EmpresaID 	= $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

if ($_SESSION["LegajoNumerico"] == '1')
{
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}
else
{
	$sqlLegajo = "em.\"Legajo\"";
}

//$arch = 'ultEmpleadoCargado.txt';

include 'seguridad.php';

$accion 	= LimpiarVariable($_POST["accion"]);
$accion2 	= LimpiarVariable($_POST["accion2"]);

$bBusquedaError = false;

if ($accion == 'Buscar')
{
	$busqLegajo 	= LimpiarVariable($_POST["busqLegajo"]);
	$busqNombre 	= strtolower(LimpiarVariable($_POST["busqNombre"]));
	$busqApellido 	= strtolower(LimpiarVariable($_POST["busqApellido"]));
	
	if ($busqLegajo != '')
	{
		// Busqueda por legajo
		
		$sql = "SELECT em.\"Legajo\"";
		$sql.= " FROM \"tblEmpleados\" em";
		$sql.= " WHERE em.\"Legajo\" = '$busqLegajo'";
		$sql.= " AND \"FechaEgreso\" IS NULL";
		
		$rs = pg_query($sql);
		if (!$rs)
			exit;
			
		$row = pg_fetch_array($rs);
		if ($row[0] != $busqLegajo)
		{
			// No Existe
			$bBusquedaError = true;
		}
	}
	else
	{
		// Busqueda por nombre o apellido
		
		$sql = "SELECT em.\"Legajo\"";
		$sql.= " FROM \"tblEmpleados\" em";
		$sql.= " WHERE lower(em.\"Nombre\") LIKE '%$busqNombre%'"; 
		$sql.= " AND lower(em.\"Apellido\") LIKE '%$busqApellido%'";
		$sql.= " AND \"FechaEgreso\" IS NULL"; 
		$sql.= " LIMIT 10";
		
		$rs = pg_query($sql);
		if (!$rs)
			exit;
			
		$iCant = 0;
		while($row = pg_fetch_array($rs))
		{
			$busqLegajo = $row[0];
			$iCant++;
		}
		
		if ($iCant > 1)
		{
			$busqLegajo = '';
		}
		else if ($iCant <= 0)
		{
			// No Existe
			$bBusquedaError = true;
		}
	}
}
?>
<H1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Empleados</H1>

<script language="JavaScript">


function Accion(AccID, ID)
{
	if (AccID == 1)
	{
		if (!ChequearSeguridad(1))
			return false;
		document.getElementById('accion').value='Agregar Empleado';
	}
	
	else if (AccID == 2)
	{
		if (!ChequearSeguridad(4))
			return false;
		document.getElementById('accion').value='Editar Empleado';
	}
	
	else if (AccID == 3)
	{
		if (!ChequearSeguridad(5))
			return false;
		var sFeatures;
		sFeatures = "dialogWidth: 340px; ";
		sFeatures += "dialogHeight: 250px; ";
		sFeatures += "help: no; ";
		sFeatures += "resizable: no; ";
		sFeatures += "scroll: no; ";
		sFeatures += "status: no; ";
		sFeatures += "unadorned: no; ";

		var oResult;
		
		oResult = window.showModalDialog("emplBorrarPopup.php", "emplBorrarPopup", sFeatures);
		  if (oResult) {
				document.getElementById('accion').value = 'Borrar Empleado';
				document.getElementById('FechaEgr').value = oResult.FechaEgr;
				document.getElementById('BajaDecreto').value = oResult.BajaDecreto;
				document.getElementById('BajaMotivo').value = oResult.BajaMotivo;
				
		  }
	}
	
	document.getElementById("ID").value = ID;
	document.frmEmpleados.submit();
}

function VerTab(iTab)
{
	var TipoRelacion = document.getElementById('selTipoRelacion').value;		
	
	var sTab1, sTab2, sTab3, sTab4, sTab5, sTab6, sTab7, sTab8;
	sTab1 = sTab2 = sTab3 = sTab4 = sTab5 = sTab6 = sTab7 = sTab8 = 'none';

	document.getElementById('tblFamiliarError').style.display = 'none';

	if (iTab == 1)
	{
		sTab1 = 'block';
	}
	else if (iTab == 2)
	{
		sTab2 = 'block';
	}
	else if (iTab == 3)
	{
		sTab3 = 'block';
	}
	else if (iTab == 4)
	{
		if (!ChequearSeguridad(2))
			return false;			
		sTab4 = 'block';
	}
	else if (iTab == 5)
	{
		sTab5 = 'block';
	}
	else if (iTab == 6)
	{
		if (document.getElementById('Legajo').value.length < 1)
			document.getElementById('tblFamiliarError').style.display = 'block';
		else
			sTab6 = 'block';
	}
	else if (iTab == 7)
	{
		sTab7 = 'block';
	}
	else if (iTab == 8)
	{
		if (!ChequearSeguridad(3))
			return false;	
		sTab8 = 'block';
	}
	document.getElementById('datosEmpleado').style.display = sTab1;
	document.getElementById('datosGenerales').style.display = sTab2;
	document.getElementById('domicilio').style.display = sTab3;
	document.getElementById('rafam').style.display = sTab4;
	document.getElementById('antecedentes').style.display = sTab5;
	document.getElementById('familiares').style.display = sTab6;
	document.getElementById('estudios').style.display = sTab7;
	document.getElementById('presupuesto').style.display = sTab8;
}

function CambioCategoria()
{

	var TipoRelacion = document.getElementById('selTipoRelacion').value;	
	var selCatSuel = document.getElementById('selCatSuel');
	var selCatID = document.getElementById('selCategoriaID');
	var catID = selCatID.options[selCatID.selectedIndex].value;
	var i;
	
	if (TipoRelacion != 4)
	{
		if (catID == 0)
		{
			document.getElementById('SueldoBasico').disabled = false;
		}
		else
		{
			document.getElementById('SueldoBasico').disabled = true;
			for(i=0;i<selCatSuel.options.length;i++)
			{
				if (selCatSuel.options[i].text == catID)
				{
					document.getElementById('SueldoBasico').value = selCatSuel.options[i].value;
					break;
				}
			}
		}
	}
}

function BorrarFamiliar(iFamiliarID)
{
	if (!confirm("\u00bfEsta seguro que quiere borrar este familiar?"))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'BorrarFamiliar';
	document.getElementById('FamiliarID').value = iFamiliarID;
	document.frmEmpleados.submit();
}

function BorrarEstudio(iEstudioID)
{
	if (!confirm("\u00bfEsta seguro que quiere borrar este estudio?"))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'BorrarEstudio';
	document.getElementById('TipoEstudioID').value = iEstudioID;
	document.frmEmpleados.submit();
}

function BorrarAntecedente(iAntecedenteID)
{
	if (!confirm("\u00bfEsta seguro que quiere borrar este antecedente?"))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'BorrarAntecedente';
	document.getElementById('AntecedenteID').value = iAntecedenteID;
	document.frmEmpleados.submit();
}

function RecuperarFamiliar(iFamiliarID)
{
	if (!confirm("\u00bfEsta seguro que quiere recuperar este familiar?"))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'RecuperarFamiliar';
	document.getElementById('FamiliarID').value = iFamiliarID;
	document.frmEmpleados.submit();
}

function RecuperarEstudio(iEstudioID)
{
	if (!confirm("\u00bfEsta seguro que quiere recuperar este estudio?"))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'RecuperarEstudio';
	document.getElementById('TipoEstudioID').value = iEstudioID;
	document.frmEmpleados.submit();
}

function VerificarDatosFamiliar(iFamiliarID)
{
	var sApellido, sNombres, sNumDoc, FechaNac;
	sApellido = document.getElementById('famApellido' + iFamiliarID).value;
	sNombres = document.getElementById('famNombres' + iFamiliarID).value;
	sNumDoc = document.getElementById('famNumDoc' + iFamiliarID).value;
	FechaNac = document.getElementById('famFechaNac' + iFamiliarID).value;
	
	if (sApellido.length < 1)
	{
		alert('Debe completar el apellido');
		return false;
	}
	if (sApellido.length > 64)
	{
		alert('El apellido no puede tener mas de 64 caracteres');
		return false;
	}
	if (sNombres.length < 1)
	{
		alert('Debe completar el nombre');
		return false;
	}
	if (sNombres.length > 64)
	{
		alert('El nombre no puede tener mas de 128 caracteres');
		return false;
	}
	if (sNumDoc.length > 10)
	{
		alert('El numero de documento no puede tener mas de 10 caracteres');
		return false;
	}
	if (FechaNac.length != 10)
	{
		alert('Debe completar la fecha de nacimiento');
		return false;
	}
	return true;
}

function AgregarFamiliar()
{
	if (!VerificarDatosFamiliar(''))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'AgregarFamiliar';
	document.getElementById('FamiliarID').value = 0;
	document.frmEmpleados.submit();
}

function AgregarEstudio()
{
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'AgregarEstudio';
	document.getElementById('TipoEstudioID').value = 0;
	document.frmEmpleados.submit();
}

function AgregarAntecedente()
{
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'AgregarAntecedente';
	document.getElementById('AntecedenteID').value = 0;
	document.frmEmpleados.submit();
}

function EditarFamiliar(iFamiliarID)
{
	if (!VerificarDatosFamiliar(iFamiliarID))
		return false;
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'EditarFamiliar';
	document.getElementById('FamiliarID').value = iFamiliarID;
	document.frmEmpleados.submit();
}

function EditarEstudio(iEstudioID)
{
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'EditarEstudio';
	document.getElementById('TipoEstudioID').value = iEstudioID;
	document.frmEmpleados.submit();
}

function EditarAntecedente(iAntecedenteID)
{
	document.getElementById('accion').value = '<?=$accion?>';
	document.getElementById('accion2').value = 'EditarAntecedente';
	document.getElementById('AntecedenteID').value = iAntecedenteID;
	document.frmEmpleados.submit();
}

function VolverFamiliar(iDiv)
{
	document.getElementById(iDiv).style.display = 'none';
	document.getElementById('listaFamiliares').style.display = 'block';
	document.getElementById('aceptarCambios').disabled = false;	
	document.getElementById('cancelarCambios').disabled = false;	
}

function VolverEstudio(iDiv)
{
	document.getElementById(iDiv).style.display = 'none';
	document.getElementById('listaEstudios').style.display = 'block';
	document.getElementById('aceptarCambios').disabled = false;	
	document.getElementById('cancelarCambios').disabled = false;	
}

function VolverAntecedente(iDiv)
{
	document.getElementById(iDiv).style.display = 'none';
	document.getElementById('listaAntecedentes').style.display = 'block';
	document.getElementById('aceptarCambios').disabled = false;	
	document.getElementById('cancelarCambios').disabled = false;	
}

function TeclaFamiliar(iDiv)
{
	document.getElementById(iDiv).style.display = 'block';
	document.getElementById('listaFamiliares').style.display = 'none';
	document.getElementById('aceptarCambios').disabled = true;
	document.getElementById('cancelarCambios').disabled = true;
}

function TeclaEstudio(iDiv)
{
	document.getElementById(iDiv).style.display = 'block';
	document.getElementById('listaEstudios').style.display = 'none';
	document.getElementById('aceptarCambios').disabled = true;
	document.getElementById('cancelarCambios').disabled = true;
}

function TeclaAntecedente(iDiv)
{
	document.getElementById(iDiv).style.display = 'block';
	document.getElementById('listaAntecedentes').style.display = 'none';
	document.getElementById('aceptarCambios').disabled = true;
	document.getElementById('cancelarCambios').disabled = true;
}

function CambioAntecedente(iCual, ID)
{
	var pre, dv, i;

	if (ID == '')
		pre = 'dvAgAnt';
	else
		pre = 'dvEdAnt';

	for(i=1;i<4;i++)
	{
		dv = document.getElementById(pre + i + ID);
		if (i == iCual)
			dv.style.display = 'block';
		else
			dv.style.display = 'none';
	}
}

function AceptarCambios(i)
{
	if (i == 0)
	{
		document.getElementById('accion').value='Cancelar';
	}
	else
	{
		var sLegajo, sApellido, sNombre, catID;
		var sTipoRelacion = document.getElementById('selTipoRelacion').value;
	
		sLegajo = document.getElementById('Legajo').value;
		sApellido = document.getElementById('Apellido').value;
		sNombre = document.getElementById('Nombre').value;
		FechaIngreso = document.getElementById('FechaIng').value;
		if (sLegajo.length < 1)
		{
			alert('Debe completar el legajo del empleado');
			return false;
		}
		if (sTipoRelacion == 4 && sLegajo < 5000)
		{
			alert('Para empleados en relacion por locacion de obra, el legajo debe se rmayor o igual a 5000');
			return false;
		}
		if (sApellido.length < 1)
		{
			alert('Debe completar el apellido del empleado');
			return false;
		}
		if (sApellido.length > 64)
		{
			alert('El apellido del empleado no puede exceder los 64 caracteres');
			return false;
		}
		if (sNombre.length < 1)
		{
			alert('Debe completar el nombre del empleado');
			return false;
		}
		if (sNombre.length > 64)
		{
			alert('El nombre del empleado no puede exceder los 64 caracteres');
			return false;
		}
		if (FechaIngreso.length != 10)
		{
			alert('Debe completar la fecha de ingreso del empleado');
			return false;
		}
		document.getElementById('accion2').value='Aceptar Cambios';
		document.getElementById('accion').value='<?=$accion?>';
	}
	document.frmEmpleados.submit();
}
var objXMLHttp;
if (window.ActiveXObject)
{ //for IE
	objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
} 
else if (window.XMLHttpRequest)
{ //for Mozilla
	objXMLHttp = new XMLHttpRequest();
}

function ObtenerXML(iCual, sJurisdiccion, iAgrupamiento, iCategoria)
{
	var objXML;

	if (iCual == 1)
	{
		objXMLHttp.open("GET", "/RafamAgrupamiento.php?Jurisdiccion="+sJurisdiccion, false, "", "");
	}
	else if (iCual == 2)
	{
		objXMLHttp.open("GET", "/RafamCategoria.php?Jurisdiccion="+sJurisdiccion+"&Agrupamiento="+iAgrupamiento, false, "", "");
	}
	else if (iCual == 3)
	{
		objXMLHttp.open("GET", "/RafamCargo.php?Jurisdiccion="+sJurisdiccion+"&Agrupamiento="+iAgrupamiento+"&Categoria="+iCategoria, false, "", "");
	}
	if (window.XMLHttpRequest)
	{
		objXMLHttp.send(null);
	}
	else
	{
		objXMLHttp.send();
	}
	if ((objXMLHttp.readyState==4) && (objXMLHttp.status==200))
	{
		objXML = objXMLHttp.responseXML;
		return objXML;		
	}
	return false;
}


function RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, iCargo)
{
	var sJuris = sJurisdiccion.substring(0, 5) + '00000';
	var selAgrup, selCat, selCargo, oSelect, i;

	if (iAgrupamiento == 0)
	{
		selAgrup = document.getElementById('selAgrupamiento');
		sJCC = ObtenerXML(1, sJuris, iAgrupamiento, iCategoria);
		for (i = selAgrup.options.length; i >= 0; i--)
			selAgrup.options[i] = null;

		if (sJCC.documentElement.childNodes.length > 0)
		{
			for (i = 0; i < sJCC.documentElement.childNodes.length; i++) 
			{
				selAgrup.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
					sJCC.documentElement.childNodes[i].getAttribute("id"));
			}
			selAgrup.disabled = false;
			iAgrupamiento = sJCC.documentElement.childNodes[0].getAttribute("id");
			RafamCombos(sJurisdiccion, iAgrupamiento, 0, 0);
		}
		else
		{
			selAgrup.options[0] = new Option("Elija un agrupamiento", 0);
			selAgrup.disabled = true;
		}
	}
	else
	{
		// Seleccionar agrupamiento
		oSelect = document.getElementById('selAgrupamiento');
		for(i=0;i<oSelect.options.length;i++)
		{
			if (oSelect.options[i].value == iAgrupamiento)
			{
				oSelect.options[i].selected = true;
				break;
			}
		}

		if (iCategoria == 0)
		{
			selCat = document.getElementById('selCategoria');
			sJCC = ObtenerXML(2, sJuris, iAgrupamiento, iCategoria);
			for (i = selCat.options.length; i >= 0; i--)
				selCat.options[i] = null;

			if (sJCC.documentElement.childNodes.length > 0)
			{
				for (i = 0; i < sJCC.documentElement.childNodes.length; i++) 
				{
					selCat.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
						sJCC.documentElement.childNodes[i].getAttribute("id"));
				}
				selCat.disabled = false;
				iCategoria = sJCC.documentElement.childNodes[0].getAttribute("id");
				RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, 0);
			}
			else
			{
				selCat.options[0] = new Option("Elija una categoria", 0);
				selCat.disabled = true;
			}
		}
		else
		{
			// Seleccionar categoria
			oSelect = document.getElementById('selCategoria');
			for(i=0;i<oSelect.options.length;i++)
			{
				if (oSelect.options[i].value == iCategoria)
				{
					oSelect.options[i].selected = true;
					break;
				}
			}
			if (iCargo == 0)
			{
				selCargo = document.getElementById('selCargo');
				sJCC = ObtenerXML(3, sJuris, iAgrupamiento, iCategoria);
				for (i = selCargo.options.length; i >= 0; i--)
					selCargo.options[i] = null;

				if (sJCC.documentElement.childNodes.length > 0)
				{
					for (i = 0; i < sJCC.documentElement.childNodes.length; i++) 
					{
						selCargo.options[i] = new Option(sJCC.documentElement.childNodes[i].getAttribute("detalle"),
							sJCC.documentElement.childNodes[i].getAttribute("id"));
					}
					selCargo.disabled = false;
					iCargo = sJCC.documentElement.childNodes[0].getAttribute("id");
					RafamCombos(sJurisdiccion, iAgrupamiento, iCategoria, iCargo);
				}
				else
				{
					selCargo.options[0] = new Option("Elija un cargo", 0);
					selCargo.disabled = true;
				}
			}
			else
			{
				// Seleccionar cargo
				oSelect = document.getElementById('selCargo');
				for(i=0;i<oSelect.options.length;i++)
				{
					if (oSelect.options[i].value == iCargo)
					{
						oSelect.options[i].selected = true;
						break;
					}
				}
			}
		}
	}
}

</script>

<form name=frmEmpleados action=emplEditar.php method=post>
<?
$iError = 0;
if (isset($accion2) && $accion2 == 'Aceptar Cambios')
{
	// Datos empleado
	$Legajo 		= LimpiarVariable($_POST["Legajo"]);
	$Nombre 		= LimpiarVariable($_POST["Nombre"]);
	$Apellido 		= LimpiarVariable($_POST["Apellido"]);
	$HorasDiarias 		= LimpiarNumero($_POST["HorasDiarias"]);
	$TipoID 		= LimpiarNumero($_POST["selTipoID"]);
	$CatID 			= LimpiarNumero2($_POST["selCategoriaID"]);
	$SueldoBasico 	= LimpiarNumero2($_POST["SueldoBasico"]);
	$Sector 		= LimpiarNumero($_POST["Sector"]);
	$Area 			= LimpiarNumero($_POST["Area"]);
	$CentroCostoID 	= LimpiarNumero($_POST["selCentroCostosID"]);
	$TipoRel 		= LimpiarNumero($_POST["selTipoRelacion"]);
	$LugarPago 		= LimpiarNumero($_POST["selLugarPago"]);
	$NumeroCuenta 	= LimpiarNumero($_POST["NumeroCuenta"]);
	$AltaDecreto 	= LimpiarVariable($_POST["AltaDecreto"]);
	
	// Datos Generales
	$TipoDoc 		= LimpiarNumero($_POST["selTipoDoc"]);
	$NumDoc 		= LimpiarVariable($_POST["NumDoc"]);
	$FechaIng 		= LimpiarNumero($_POST["FechaIng"]);
	$EstadoCivil 	= LimpiarNumero($_POST["selEstadoCivil"]);
	$Sexo 			= LimpiarVariable($_POST["selSexo"]);
	$CUIT 			= LimpiarNumero2($_POST["CUIT"]);
	$Nacionalidad 	= LimpiarVariable($_POST["Nacionalidad"]);
	$PaisNac 		= LimpiarVariable($_POST["PaisNac"]);
	$ProvNac 		= LimpiarVariable($_POST["ProvNac"]);
	$LocNac 		= LimpiarVariable($_POST["LocNac"]);
	$FechaNac 		= LimpiarNumero($_POST["FechaNac"]);
	
	// Domicilio
	$Calle 			= LimpiarVariable($_POST["Calle"]);
	$Numero 		= LimpiarNumero($_POST["Numero"]);
	$Piso 			= LimpiarNumero($_POST["Piso"]);
	$Departamento 	= LimpiarVariable($_POST["Departamento"]);
	$LocalidadDom 	= LimpiarVariable($_POST["LocalidadDom"]);
	$Telefono 		= LimpiarVariable($_POST["Telefono"]);
	$Celular 		= LimpiarVariable($_POST["Celular"]);
	$Email 			= LimpiarVariable($_POST["Email"]);
	$CodigoPostal 	= LimpiarVariable($_POST["CodigoPostal"]);
	
	// RAFAM
	$Jurisdiccion 	= LimpiarVariable($_POST["selJurisdiccion"]);
	$Agrupamiento 	= LimpiarNumero($_POST["selAgrupamiento"]);
	$Categoria 		= LimpiarNumero($_POST["selCategoria"]);
	$Cargo 			= LimpiarNumero($_POST["selCargo"]);
	$CodigoFF 		= LimpiarNumero($_POST["selFuenteFinanciamiento"]);
	$Programa 		= LimpiarNumero($_POST["Programa"]);
	$Activ_Proy 	= LimpiarNumero($_POST["Activ_Proy"]);
	$Activ_Obra 	= LimpiarNumero($_POST["Activ_Obra"]);
	$TipoDePlanta 	= LimpiarNumero($_POST["selTipoPlanta"]);
	
	// Presupuesto
	$Gastos1 = LimpiarNumero2($_POST["Gastos1"]);
	$Gastos2 = LimpiarNumero2($_POST["Gastos2"]);
	$Gastos3 = LimpiarNumero2($_POST["Gastos3"]);
	$Gastos4 = LimpiarNumero2($_POST["Gastos4"]);
	$Gastos5 = LimpiarNumero2($_POST["Gastos5"]);
	$Gastos6 = LimpiarNumero2($_POST["Gastos6"]);
	$Gastos7 = LimpiarNumero2($_POST["Gastos7"]);
	$Gastos8 = LimpiarNumero2($_POST["Gastos8"]);
	$Gastos9 = LimpiarNumero2($_POST["Gastos9"]);
	$Gastos10 = LimpiarNumero2($_POST["Gastos10"]);
	$Gastos11 = LimpiarNumero2($_POST["Gastos11"]);
	$Gastos12 = LimpiarNumero2($_POST["Gastos12"]);
	$Gastos13 = LimpiarNumero2($_POST["Gastos13"]);
	$Gastos14 = LimpiarNumero2($_POST["Gastos14"]);

	if ($Legajo == '' || $Nombre == '' || $Apellido == '' || $FechaIng == '')
		exit;

	$Nombre 		= ParametroSQL($Nombre, 'varchar');
	$Apellido 		= ParametroSQL($Apellido, 'varchar');
	$TipoID 		= ParametroSQL($TipoID, 'int2');
	$CatID 			= ParametroSQL($CatID, 'float');
	$SueldoBasico 	= ParametroSQL($SueldoBasico, 'float');
	$HorasDiarias	 	= ParametroSQL($HorasDiarias, 'int2');
	$Sector 		= ParametroSQL($Sector, 'int4');
	$Area 			= ParametroSQL($Area, 'int4');
	$CentroCostoID 	= ParametroSQL($CentroCostoID, 'int4');
	$TipoRel 		= ParametroSQL($TipoRel, 'int2');
	$LugarPago 		= ParametroSQL($LugarPago, 'int4');
	$NumeroCuenta 	= ParametroSQL($NumeroCuenta, 'varchar');
	$AltaDecreto 	= ParametroSQL($AltaDecreto, 'varchar');
	
	$TipoDoc 		= ParametroSQL($TipoDoc, 'int2');
	$NumDoc 		= ParametroSQL($NumDoc, 'varchar');
	$FechaIng 		= ParametroSQL(FechaWEB2SQL($FechaIng), '');
	$EstadoCivil 	= ParametroSQL($EstadoCivil, 'int2');
	$Sexo 			= ParametroSQL($Sexo, 'char');
	$CUIT 			= ParametroSQL($CUIT, 'varchar');
	$Nacionalidad 	= ParametroSQL($Nacionalidad, 'varchar');
	$PaisNac 		= ParametroSQL($PaisNac, 'varchar');
	$ProvNac 		= ParametroSQL($ProvNac, 'varchar');
	$LocNac 		= ParametroSQL($LocNac, 'varchar');
	$FechaNac 		= ParametroSQL(FechaWEB2SQL($FechaNac), '');

	$Calle 			= ParametroSQL($Calle, 'varchar');
	$Numero 		= ParametroSQL($Numero, 'int4');
	$Piso 			= ParametroSQL($Piso, 'int2');
	$Departamento 	= ParametroSQL($Departamento, 'varchar');
	$LocalidadDom 	= ParametroSQL($LocalidadDom, 'varchar');
	$Telefono 		= ParametroSQL($Telefono, 'varchar');
	$Celular 		= ParametroSQL($Celular, 'varchar');
	$Email 			= ParametroSQL($Email, 'varchar');
	$CodigoPostal 	= ParametroSQL($CodigoPostal, 'varchar');

	$Jurisdiccion 	= ParametroSQL($Jurisdiccion, 'varchar');
	$Agrupamiento 	= ParametroSQL($Agrupamiento, 'int4');
	$Categoria 		= ParametroSQL($Categoria, 'int4');
	$Cargo 			= ParametroSQL($Cargo, 'int4');
	$CodigoFF 		= ParametroSQL($CodigoFF, 'int4');
	$Programa 		= ParametroSQL($Programa, 'int4');
	$Activ_Proy 	= ParametroSQL($Activ_Proy, 'int4');
	$Activ_Obra 	= ParametroSQL($Activ_Obra, 'int4');
	$TipoDePlanta 	= ParametroSQL($TipoDePlanta, 'int2');

	$Gastos1 = ParametroSQL($Gastos1, 'float8');
	$Gastos2 = ParametroSQL($Gastos2, 'float8');
	$Gastos3 = ParametroSQL($Gastos3, 'float8');
	$Gastos4 = ParametroSQL($Gastos4, 'float8');
	$Gastos5 = ParametroSQL($Gastos5, 'float8');
	$Gastos6 = ParametroSQL($Gastos6, 'float8');
	$Gastos7 = ParametroSQL($Gastos7, 'float8');
	$Gastos8 = ParametroSQL($Gastos8, 'float8');
	$Gastos9 = ParametroSQL($Gastos9, 'float8');
	$Gastos10 = ParametroSQL($Gastos10, 'float8');
	$Gastos11 = ParametroSQL($Gastos11, 'float8');
	$Gastos12 = ParametroSQL($Gastos12, 'float8');
	$Gastos13 = ParametroSQL($Gastos13, 'float8');
	$Gastos14 = ParametroSQL($Gastos14, 'float8');

	$sql = "SELECT em.\"Legajo\"";
	$sql.= " FROM \"tblEmpleados\" em";
	$sql.= " WHERE em.\"Legajo\" = '$Legajo'";

	$rs = pg_query($db, $sql);
	if (!$rs)
	{
		exit;
	}
		
	$row = pg_fetch_array($rs);
	if ($accion == 'Agregar Empleado')
	{
		if (!ChequearSeguridad(1))
		{
			exit;
		}
			
		if ($row[0] != '')
		{
			// Ese legajo ya existe
			$iError = 4096;
			$iTab = 1;
		}
		else
		{	
			$sql = "SELECT \"AgregarEmpleado\"";
			$sql.= " (";
			$sql.= " $EmpresaID,"; 
			$sql.= " $SucursalID,"; 
			$sql.= " '$Legajo'::varchar,"; 
			$sql.= " $Nombre,"; 
			$sql.= " $Apellido,"; 
			$sql.= " $TipoID,"; 
			$sql.= " $CatID,"; 
			$sql.= " $SueldoBasico,$HorasDiarias,"; 
			$sql.= " $Sector,"; 
			$sql.= " $Area,"; 
			$sql.= " $CentroCostoID,"; 
			$sql.= " $TipoRel,"; 
			$sql.= " $TipoDoc,"; 
			$sql.= " $NumDoc,"; 
			$sql.= " $FechaIng,"; 
			$sql.= " $EstadoCivil,"; 
			$sql.= " $Sexo,"; 
			$sql.= " $CUIT,"; 
			$sql.= " $Nacionalidad,"; 
			$sql.= " $PaisNac,"; 
			$sql.= " $ProvNac,"; 
			$sql.= " $LocNac,"; 
			$sql.= " $FechaNac,"; 
			$sql.= " $Calle,"; 
			$sql.= " $Numero,"; 
			$sql.= " $Piso,"; 
			$sql.= " $Departamento,"; 
			$sql.= " $LocalidadDom,"; 
			$sql.= " $Telefono,"; 
			$sql.= " $Celular,"; 
			$sql.= " $Email,"; 
			$sql.= " $CodigoPostal,";
			$sql.= " $AltaDecreto";
			$sql.= " )";

			pg_exec($db, $sql);
						
			#/////////////////////////////////////////////////////////////////////////////
			#/////////////////////////////////////////////////////////////////////////////
			#/////////////////////////////////////////////////////////////////////////////
			
			//$fp = fopen('../history/'.$arch, 'w');
			//fputs($fp, $Legajo); 
			//fclose($fp);
			
			#/////////////////////////////////////////////////////////////////////////////
			#/////////////////////////////////////////////////////////////////////////////
			#/////////////////////////////////////////////////////////////////////////////
			
			if (ChequearSeguridad(2))
			{
				if ($TipoRel != 4)
				{
					$sql = "SELECT \"AgregarEmpleadoRafam\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $Jurisdiccion,"; 
					$sql.= " $Agrupamiento,"; 
					$sql.= " $Categoria,"; 
					$sql.= " $Cargo,"; 
					$sql.= " $CodigoFF,"; 
					$sql.= " $Programa,"; 
					$sql.= " $Activ_Proy,"; 
					$sql.= " $Activ_Obra,"; 
					$sql.= " $TipoDePlanta";
					$sql.= " )";
				
					pg_exec($db, $sql);
				}
			}

			if ($TipoRel == 4)
			{
				$sql = "SELECT \"AgregarEmpleadoSueldoLocacionDeObra\"";
				$sql.= " (";
				$sql.= " $EmpresaID,"; 
				$sql.= " $SucursalID,"; 
				$sql.= " '$Legajo'::varchar,"; 
				$sql.= " $SueldoBasico"; 
				$sql.= " )";
			
				pg_exec($db, $sql);
			}

			if (ChequearSeguridad(3))
			{
				$sql = "SELECT \"EmpleadoPresupuesto\"";
				$sql.= " (";
				$sql.= " $EmpresaID,"; 
				$sql.= " $SucursalID,"; 
				$sql.= " '$Legajo'::varchar,"; 
				$sql.= " $Gastos1,"; 
				$sql.= " $Gastos2,";
				$sql.= " $Gastos3,"; 
				$sql.= " $Gastos4,"; 
				$sql.= " $Gastos5,"; 
				$sql.= " $Gastos6,"; 
				$sql.= " $Gastos7,"; 
				$sql.= " $Gastos8,"; 
				$sql.= " $Gastos9,"; 
				$sql.= " $Gastos10,"; 
				$sql.= " $Gastos11,"; 
				$sql.= " $Gastos12,"; 
				$sql.= " $Gastos13,"; 
				$sql.= " $Gastos14,"; 
				$sql.= " $TipoDePlanta";
				$sql.= " )";
			
				pg_exec($db, $sql);
			}

			$sql = "SELECT \"EmpleadoLugarPago\"";
			$sql.= " (";
			$sql.= " $EmpresaID,"; 
			$sql.= " $SucursalID,"; 
			$sql.= " '$Legajo'::varchar,"; 
			$sql.= " $LugarPago,"; 
			$sql.= " $NumeroCuenta";
			$sql.= " )";

			pg_exec($db, $sql);
			$accion = '';
			$_SESSION["CancelarCambios"] = '';
		}
	}
	
	else if ($accion == 'Editar Empleado')
	{
		if (!ChequearSeguridad(4))
		{
			exit;
		}
			
		if ($row[0] == '')
		{
			// Ese empleado no existe
			exit;
		}
		
		$sql = "SELECT \"EditarEmpleado\"";
		$sql.= " (";
		$sql.= " $EmpresaID,"; 
		$sql.= " $SucursalID,"; 
		$sql.= " '$Legajo'::varchar,"; 
		$sql.= " $Nombre,"; 
		$sql.= " $Apellido,"; 
		$sql.= " $TipoID,"; 
		$sql.= " $CatID,"; 
		$sql.= " $SueldoBasico,$HorasDiarias,"; 
		$sql.= " $Sector,"; 
		$sql.= " $Area,"; 
		$sql.= " $CentroCostoID,"; 
		$sql.= " $TipoRel,"; 
		$sql.= " $TipoDoc,"; 
		$sql.= " $NumDoc,"; 
		$sql.= " $FechaIng,"; 
		$sql.= " $EstadoCivil,"; 
		$sql.= " $Sexo,"; 
		$sql.= " $CUIT,"; 
		$sql.= " $Nacionalidad,"; 
		$sql.= " $PaisNac,"; 
		$sql.= " $ProvNac,"; 
		$sql.= " $LocNac,"; 
		$sql.= " $FechaNac,"; 
		$sql.= " $Calle,"; 
		$sql.= " $Numero,"; 
		$sql.= " $Piso,"; 
		$sql.= " $Departamento,"; 
		$sql.= " $LocalidadDom,"; 
		$sql.= " $Telefono,"; 
		$sql.= " $Celular,"; 
		$sql.= " $Email,"; 
		$sql.= " $CodigoPostal,";
		$sql.= " $AltaDecreto";
		$sql.= " )";

		pg_exec($db, $sql);

		if (ChequearSeguridad(2))
		{
			if ($TipoRel != 4)
			{
				$sql = "SELECT \"EditarEmpleadoRafam\"";
				$sql.= " (";
				$sql.= " $EmpresaID,"; 
				$sql.= " $SucursalID,"; 
				$sql.= " '$Legajo'::varchar,"; 
				$sql.= " $Jurisdiccion,"; 
				$sql.= " $Agrupamiento,"; 
				$sql.= " $Categoria,"; 
				$sql.= " $Cargo,"; 
				$sql.= " $CodigoFF,"; 
				$sql.= " $Programa,"; 
				$sql.= " $Activ_Proy,"; 
				$sql.= " $Activ_Obra,"; 
				$sql.= " $TipoDePlanta";
				$sql.= " )";
		
				pg_exec($db, $sql);
			}
		}

		if ($TipoRel == 4)
		{
			$sql = "SELECT \"EditarEmpleadoSueldoLocacionDeObra\"";
			$sql.= " (";
			$sql.= " $EmpresaID,"; 
			$sql.= " $SucursalID,"; 
			$sql.= " '$Legajo'::varchar,"; 
			$sql.= " $SueldoBasico"; 
			$sql.= " )";
		
			pg_exec($db, $sql);

			$sql = "SELECT \"EditarEmpleadoRafam\"";
			$sql.= " (";
			$sql.= " $EmpresaID,"; 
			$sql.= " $SucursalID,"; 
			$sql.= " '$Legajo'::varchar,"; 
			$sql.= " $Jurisdiccion,"; 
			$sql.= " null,"; 
			$sql.= " null,"; 
			$sql.= " null,"; 
			$sql.= " $CodigoFF,"; 
			$sql.= " $Programa,"; 
			$sql.= " $Activ_Proy,"; 
			$sql.= " $Activ_Obra,"; 
			$sql.= " $TipoDePlanta";
			$sql.= " )";
		
			pg_exec($db, $sql);
		}

		if (ChequearSeguridad(3))
		{
			$sql = "SELECT \"EmpleadoPresupuesto\"";
			$sql.= " (";
			$sql.= " $EmpresaID,"; 
			$sql.= " $SucursalID,"; 
			$sql.= " '$Legajo'::varchar,"; 
			$sql.= " $Gastos1,"; 
			$sql.= " $Gastos2,"; 
			$sql.= " $Gastos3,"; 
			$sql.= " $Gastos4,"; 
			$sql.= " $Gastos5,"; 
			$sql.= " $Gastos6,"; 
			$sql.= " $Gastos7,"; 
			$sql.= " $Gastos8,"; 
			$sql.= " $Gastos9,"; 
			$sql.= " $Gastos10,"; 
			$sql.= " $Gastos11,"; 
			$sql.= " $Gastos12,"; 
			$sql.= " $Gastos13,"; 
			$sql.= " $Gastos14,"; 
			$sql.= " $TipoDePlanta";
			$sql.= " )";
		
			pg_exec($db, $sql);
		}

		$sql = "SELECT \"EmpleadoLugarPago\"";
		$sql.= " (";
		$sql.= " $EmpresaID,"; 
		$sql.= " $SucursalID,"; 
		$sql.= " '$Legajo'::varchar,"; 
		$sql.= " $LugarPago,"; 
		$sql.= " $NumeroCuenta";
		$sql.= " )";

		pg_exec($db, $sql);
		
		$accion = '';
		$_SESSION["CancelarCambios"] = '';
	}
}

if (isset($accion) && $accion == 'Borrar Empleado')
{
	if (!ChequearSeguridad(5))
	{
		exit;
	}
		
	$ID 		 = LimpiarVariable($_POST["ID"]);
	$FechaEgr    = LimpiarNumero($_POST["FechaEgr"]);
	$BajaDecreto = LimpiarVariable($_POST["BajaDecreto"]);
	$BajaMotivo  = LimpiarVariable($_POST["BajaMotivo"]);
	$Dia 		 = substr($FechaEgr, 0, 2);
	$Mes 		 = substr($FechaEgr, 2, 2);
	$Ano 		 = substr($FechaEgr, 4, 4);
	
	if ($Dia == '' || $Mes == '' || $Ano == '')
	{
		exit;
	}
	
	$sql = "UPDATE \"tblEmpleadosDatos\"";
	$sql.= " SET  \"BajaDecreto\" ='$BajaDecreto',\"BajaMotivo\" ='$BajaMotivo'";
	$sql.= " WHERE \"Legajo\" = '$ID'";
	
	if (!pg_exec($db, $sql))
	{
		Alerta('Se produjo un error interno al borrar el empleado');
	}
	
	$sql ='';	
	$sql = "UPDATE \"tblEmpleados\"";
	$sql.= " SET \"FechaEgreso\" = '$Ano-$Mes-$Dia'";
	$sql.= " WHERE \"Legajo\" = '$ID'";
		
	if (!pg_exec($db, $sql))
	{
		Alerta('Se produjo un error interno al borrar el empleado');
	}
	else
	{
		Alerta('El empleado se borro con exito');
	}
	//$Undo = "UPDATE \"tblEmpleados\" SET \"FechaEgreso\" = null WHERE \"Legajo\" = '$ID';";
	//$_SESSION["CancelarCambios"] = $Undo . $_SESSION["CancelarCambios"];
	$accion = '';
}

else if (isset($accion) && ($accion == 'Editar Empleado' || $accion == 'Agregar Empleado'))
{
	$ID 	= LimpiarVariable($_POST["ID"]);
	$Nombre = LimpiarVariable($_POST["Nombre"]);
	
	if ($Nombre != '')
	{
		$Legajo 		= LimpiarVariable($_POST["Legajo"]);
		$Apellido 		= LimpiarVariable($_POST["Apellido"]);
		$TipoID 		= LimpiarNumero($_POST["selTipoID"]);
		$CategoriaID 	= LimpiarNumero2($_POST["selCategoriaID"]);
		$SueldoBasico 	= LimpiarNumero2($_POST["SueldoBasico"]);
		$HorasDiarias 	= LimpiarNumero($_POST["HorasDiarias"]);
		$Sector 		= LimpiarNumero($_POST["Sector"]);
		$Area 			= LimpiarNumero($_POST["Area"]);
		$CentroCostos 	= LimpiarNumero($_POST["selCentroCostosID"]);
		$TipoRelacion 	= LimpiarNumero($_POST["selTipoRelacion"]);
		$LugarPago 		= LimpiarNumero($_POST["selLugarPago"]);
		$NumeroCuenta 	= LimpiarNumero($_POST["NumeroCuenta"]);
		$AltaDecreto 	= LimpiarVariable($_POST["AltaDecreto"]);
		
		// Datos Generales
		$TipoDocumento 		= LimpiarNumero($_POST["selTipoDoc"]);
		$NumeroDocumento 	= LimpiarNumero($_POST["NumDoc"]);
		$FechaIngreso 		= LimpiarNumero2($_POST["FechaIng"]);
		$EstadoCivil 		= LimpiarNumero($_POST["selEstadoCivil"]);
		$Sexo 				= LimpiarVariable($_POST["selSexo"]);
		$CUIT 				= LimpiarNumero2($_POST["CUIT"]);
		$Nacionalidad 		= LimpiarVariable($_POST["Nacionalidad"]);
		$PaisNac 			= LimpiarVariable($_POST["PaisNac"]);
		$ProvNac 			= LimpiarVariable($_POST["ProvNac"]);
		$LocalidadNac 		= LimpiarVariable($_POST["LocNac"]);
		$FechaNacimiento 	= LimpiarNumero2($_POST["FechaNac"]);
		$Calle 				= LimpiarVariable($_POST["Calle"]);
		$Numero 			= LimpiarNumero($_POST["Numero"]);
		$Piso 				= LimpiarNumero($_POST["Piso"]);
		$Departamento 		= LimpiarVariable($_POST["Departamento"]);
		$LocalidadDom 		= LimpiarVariable($_POST["LocalidadDom"]);
		$Telefono 			= LimpiarVariable($_POST["Telefono"]);
		$Celular 			= LimpiarVariable($_POST["Celular"]);
		$Email 				= LimpiarVariable($_POST["Email"]);
		$CodigoPostal 		= LimpiarVariable($_POST["CodigoPostal"]);
		
		// RAFAM
		$Jurisdiccion 	= LimpiarVariable($_POST["selJurisdiccion"]);
		$Agrupamiento 	= LimpiarNumero($_POST["selAgrupamiento"]);
		$Categoria 		= LimpiarNumero($_POST["selCategoria"]);
		$Cargo 			= LimpiarNumero($_POST["selCargo"]);
		$CodigoFF 		= LimpiarNumero($_POST["selFuenteFinanciamiento"]);
		$Programa 		= LimpiarNumero($_POST["Programa"]);
		$Activ_Proy 	= LimpiarNumero($_POST["Activ_Proy"]);
		$Activ_Obra 	= LimpiarNumero($_POST["Activ_Obra"]);
		$TipoDePlanta 	= LimpiarNumero($_POST["selTipoPlanta"]);
		
		// Presupuesto
		$Gastos1 = LimpiarNumero2($_POST["Gastos1"]);
		$Gastos2 = LimpiarNumero2($_POST["Gastos2"]);
		$Gastos3 = LimpiarNumero2($_POST["Gastos3"]);
		$Gastos4 = LimpiarNumero2($_POST["Gastos4"]);
		$Gastos5 = LimpiarNumero2($_POST["Gastos5"]);
		$Gastos6 = LimpiarNumero2($_POST["Gastos6"]);
		$Gastos7 = LimpiarNumero2($_POST["Gastos7"]);
		$Gastos8 = LimpiarNumero2($_POST["Gastos8"]);
		$Gastos9 = LimpiarNumero2($_POST["Gastos9"]);
		$Gastos10 = LimpiarNumero2($_POST["Gastos10"]);
		$Gastos11 = LimpiarNumero2($_POST["Gastos11"]);
		$Gastos12 = LimpiarNumero2($_POST["Gastos12"]);
		$Gastos13 = LimpiarNumero2($_POST["Gastos13"]);
		$Gastos14 = LimpiarNumero2($_POST["Gastos14"]);
	}
	else
	{
		if ($accion == 'Editar Empleado')
		{
			$sql = "SELECT em.\"Legajo\","; 
			$sql.= " em.\"Nombre\","; 
			$sql.= " em.\"Apellido\","; 
			$sql.= " em.\"TipoID\","; 
			$sql.= " em.\"SueldoBasico\","; 
			$sql.= " em.\"CategoriaID\",";
			$sql.= " em.\"Sector\","; 
			$sql.= " em.\"Area\","; 
			$sql.= " em.\"CentroCostos\","; 
			$sql.= " em.\"TipoRelacion\",";
			$sql.= " ed.\"TipoDocumento\","; 
			$sql.= " ed.\"NumeroDocumento\","; 
			$sql.= " ed.\"FechaIngreso\","; 
			$sql.= " ed.\"EstadoCivil\", ed.\"Sexo\",";
			$sql.= " ed.\"CUIT\","; 
			$sql.= " ed.\"FechaNacimiento\","; 
			$sql.= " ed.\"Nacionalidad\","; 
			$sql.= " ed.\"LocalidadNac\","; 
			$sql.= " ed.\"ProvinciaNac\","; 
			$sql.= " ed.\"PaisNac\",";
			$sql.= " ed.\"HorasDiarias\",";
			$sql.= " ed.\"AltaDecreto\",";
			$sql.= " eo.\"Calle\","; 
			$sql.= " eo.\"Numero\","; 
			$sql.= " eo.\"Piso\","; 
			$sql.= " eo.\"Departamento\","; 
			$sql.= " eo.\"Localidad\","; 
			$sql.= " eo.\"Telefono\","; 
			$sql.= " eo.\"Celular\",";
			$sql.= " eo.\"Email\","; 
			$sql.= " eo.\"CodigoPostal\","; 
			$sql.= " er.\"TipoDePlanta\","; 
			$sql.= " er.codigo_ff,"; 
			$sql.= " er.jurisdiccion,"; 
			$sql.= " er.agrupamiento,"; 
			$sql.= " er.categoria,";
			$sql.= " er.cargo,"; 
			$sql.= " er.programa,"; 
			$sql.= " er.activ_proy,"; 
			$sql.= " er.activ_obra,"; 
			$sql.= " ed.\"LugarPago\","; 
			$sql.= " ed.\"NumeroCuenta\"";
			$sql.= " FROM \"tblEmpleados\" em"; 
			$sql.= " INNER JOIN \"tblEmpleadosDatos\" ed";
			$sql.= " ON ed.\"EmpresaID\" = em.\"EmpresaID\""; 
			$sql.= " AND ed.\"SucursalID\" = em.\"SucursalID\""; 
			$sql.= " AND ed.\"Legajo\" = em.\"Legajo\"";
			$sql.= " INNER JOIN \"tblEmpleadosDomicilio\" eo";
			$sql.= " ON eo.\"EmpresaID\" = em.\"EmpresaID\""; 
			$sql.= " AND eo.\"SucursalID\" = em.\"SucursalID\""; 
			$sql.= " AND eo.\"Legajo\" = em.\"Legajo\"";
			$sql.= " LEFT JOIN \"tblEmpleadosRafam\" er";
			$sql.= " ON er.\"EmpresaID\" = em.\"EmpresaID\""; 
			$sql.= " AND er.\"SucursalID\" = em.\"SucursalID\""; 
			$sql.= " AND er.\"Legajo\" = em.\"Legajo\"";
			$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
			$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
			$sql.= " AND em.\"Legajo\"='$ID'";

			$rs = pg_query($db, $sql);
			if (!$rs)
			{
				exit;
			}
			
			$i 				= 0;
			$row 			= pg_fetch_array($rs);
			$Legajo 		= $row[$i++];
			$Nombre 		= $row[$i++];
			$Apellido 		= $row[$i++];
			$TipoID 		= $row[$i++];
			$SueldoBasico 	= $row[$i++];
			$CategoriaID 	= $row[$i++];
			$Sector 		= $row[$i++];
			$Area 			= $row[$i++];
			$CentroCostos 	= $row[$i++];
			$TipoRelacion 	= $row[$i++];
			
			// Datos
			$TipoDocumento 		= $row[$i++];
			$NumeroDocumento 	= $row[$i++];
			$FechaIngreso 		= $row[$i++];
			$EstadoCivil 		= $row[$i++];
			$Sexo 				= $row[$i++];
			$CUIT 				= $row[$i++];
			$FechaNacimiento 	= $row[$i++];
			$Nacionalidad 		= $row[$i++];
			$LocalidadNac 		= $row[$i++];
			$ProvNac 			= $row[$i++];
			$PaisNac 			= $row[$i++];
			$HorasDiarias 		= $row[$i++];
			$AltaDecreto 		= $row[$i++];
			
			// Domicilio
			$Calle 			= $row[$i++];
			$Numero 		= $row[$i++];
			$Piso 			= $row[$i++];
			$Departamento 	= $row[$i++];
			$LocalidadDom 	= $row[$i++];
			$Telefono 		= $row[$i++];
			$Celular 		= $row[$i++];
			$Email 			= $row[$i++];
			$CodigoPostal 	= $row[$i++];
			
			// RAFAM
			$TipoDePlanta 		= $row[$i++];
			$CodigoFF 			= $row[$i++];
			$Jurisdiccion 		= $row[$i++];
			$Agrupamiento 		= $row[$i++];
			$Categoria 			= $row[$i++];
			$Cargo 				= $row[$i++];
			$Programa 			= $row[$i++];
			$Activ_Proy 		= $row[$i++];
			$Activ_Obra 		= $row[$i++];
			$FechaIngreso 		= FechaSQL2WEB($FechaIngreso);
			$FechaNacimiento 	= FechaSQL2WEB($FechaNacimiento);

			$LugarPago 		= $row[$i++];
			$NumeroCuenta 	= $row[$i++];
			
			$sql = "SELECT \"GastoID\","; 
			$sql.= " \"Valor\"";
			$sql.= " FROM \"tblEmpleadosPresupuesto\" ep";
			$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
			$sql.= " AND \"SucursalID\" = $SucursalID"; 
			$sql.= " AND \"Legajo\" = '$ID'";
			$sql.= " ORDER BY 1";
			
			$rs = pg_query($sql);

			while($row = pg_fetch_array($rs))
			{
				$Valor = $row[1];
				switch($row[0])
				{
					case 1:
						$Gastos1 = $Valor;
						break;
					case 2:
						$Gastos2 = $Valor;
						break;
					case 3:
						$Gastos3 = $Valor;
						break;
					case 4:
						$Gastos4 = $Valor;
						break;
					case 5:
						$Gastos5 = $Valor;
						break;
					case 6:
						$Gastos6 = $Valor;
						break;
					case 7:
						$Gastos7 = $Valor;
						break;
					case 8:
						$Gastos8 = $Valor;
						break;
					case 9:
						$Gastos9 = $Valor;
						break;
					case 10:
						$Gastos10 = $Valor;
						break;
					case 11:
						$Gastos11 = $Valor;
						break;
					case 12:
						$Gastos12 = $Valor;
						break;
					case 13:
						$Gastos13 = $Valor;
						break;
					case 14:
						$Gastos14 = $Valor;
						break;
				}
			}
		}
	}

	if ($ID == '' || $ID == 0)
	{
		$ID = LimpiarVariable($_POST["Legajo"]);
	}

	if ($accion == 'Editar Empleado')
	{
		print "Editando Empleado: $Apellido, $Nombre<br><br>";
	}
	else
	{
		print "Agregar Empleado<br><br>";
		
		///////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////
		//  INICIO DEL CODIGO QUE MUESTRA ULTIMOS LEGAJOS CARGADOS
		///////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////
		
		$sqlLeg = "SELECT ED.\"Legajo\""; 
		$sqlLeg.= " FROM \"tblEmpleadosDatos\" ED";
		
		$sqlLeg.= " INNER JOIN \"tblEmpleados\" E";
		$sqlLeg.= " ON ED.\"Legajo\" = E.\"Legajo\"";
		$sqlLeg.= " AND ED.\"EmpresaID\" = E.\"EmpresaID\"";
		$sqlLeg.= " AND ED.\"SucursalID\" = E.\"SucursalID\"";

		$sqlLeg.= " WHERE (ED.\"FechaIngreso\" IN (select MAX(\"FechaIngreso\")"; 
		$sqlLeg.= " FROM \"tblEmpleadosDatos\" ED))";
		$sqlLeg.= " AND E.\"TipoRelacion\" NOT IN (2)";

		$sqlLeg.= " UNION";

		$sqlLeg.= " SELECT ED.\"Legajo\""; 
		$sqlLeg.= " FROM \"tblEmpleadosDatos\" ED"; 
		$sqlLeg.= " INNER JOIN \"tblEmpleados\" E";
		$sqlLeg.= " ON ED.\"Legajo\" = E.\"Legajo\""; 
		$sqlLeg.= " AND ED.\"EmpresaID\" = E.\"EmpresaID\"";
		$sqlLeg.= " AND ED.\"SucursalID\" = E.\"SucursalID\"";

		$sqlLeg.= " WHERE (\"FechaIngreso\" IN (select MAX(\"FechaIngreso\")"; 
		$sqlLeg.= " FROM \"tblEmpleadosDatos\"))"; 
		$sqlLeg.= " AND";
		$sqlLeg.= " E.\"TipoRelacion\" in (2)";
			
		$rsLeg = pg_query($db, $sqlLeg);
		if ($rsLeg)
		{
			$ultLegajo1 = pg_fetch_array($rsLeg);
			$ultLegajo2 = pg_fetch_array($rsLeg);
			print "&nbsp;&nbsp;(&nbsp;Ultimos Legajos Cargados: &nbsp;" . $ultLegajo1[0] . "&nbsp; / &nbsp;" . $ultLegajo2[0] . "&nbsp;)";
			print "<br>";
		}
		
		print "<br>";
		
		///////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////
		//  FIN MOSTRAR ULTIMOS LEGAJOS CARGADOS
		///////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////
			
		$ID = $Legajo;
	}
	
	$CantEmpleados = LimpiarNumero($_POST["cantidad"]);
	print "<input type=hidden id=cantidad name=cantidad value=\"$CantEmpleados\">\n";
	$Pagina = LimpiarNumero($_POST["pagina"]);
	print "<input type=hidden id=pagina name=pagina value=\"$Pagina\">\n";
	print "<input type=hidden id=ID name=ID value=\"$ID\">\n";
	$Orden = LimpiarNumero($_POST["Orden"]);
	print "<input type=hidden id=Orden name=Orden value=\"$Orden\">\n";

	$iTab = 1;
	$accion2 = LimpiarVariable($_POST["accion2"]);

	if ($accion2 == 'BorrarFamiliar')
	{
		///////////////////////////////////////
		// BORRAR FAMILIAR
		///////////////////////////////////////
		$iTab = 6;
		$FamiliarID = LimpiarNumero($_POST["FamiliarID"]);
		
		$sql = "UPDATE \"tblEmpleadosFamiliares\""; 
		$sql.= " SET \"FechaBaja\" = now()::date";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"SucursalID\" = $SucursalID"; 
		$sql.= " AND \"Legajo\"='$ID'"; 
		$sql.= " AND \"FamiliarID\" = $FamiliarID";
		
		pg_exec($db, $sql);

		// Guardo sql para cancelar cambios
		
		$Undo = "UPDATE \"tblEmpleadosFamiliares\""; 
		$Undo.= " SET \"FechaBaja\" = NULL";
		$Undo.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$Undo.= " AND \"SucursalID\" = $SucursalID"; 
		$Undo.= " AND \"Legajo\"='$ID'"; 
		$Undo.= " AND \"FamiliarID\" = $FamiliarID;";

		$_SESSION["CancelarCambios"] = $Undo . $_SESSION["CancelarCambios"];
	}
	
	else if ($accion2 == 'BorrarEstudio')
	{
		///////////////////////////////////////
		// BORRAR ESTUDIO
		///////////////////////////////////////
		$iTab = 7;
		$EstudioID = LimpiarNumero($_POST["TipoEstudioID"]);
		
		$sql = "UPDATE \"tblEmpleadosEstudios\""; 
		$sql.= " SET \"FechaBaja\"=now()::date";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"SucursalID\" = $SucursalID"; 
		$sql.= " AND \"Legajo\"='$ID'"; 
		$sql.= " AND \"TipoEstudio\" = $EstudioID";
		
		pg_exec($db, $sql);

		// Guardo sql para cancelar cambios
		$Undo = "UPDATE \"tblEmpleadosEstudios\""; 
		$Undo.= " SET \"FechaBaja\" = NULL";
		$Undo.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$Undo.= " AND \"SucursalID\" = $SucursalID"; 
		$Undo.= " AND \"Legajo\"='$ID'"; 
		$Undo.= " AND \"TipoEstudio\" = $EstudioID;";

		$_SESSION["CancelarCambios"] = $Undo . $_SESSION["CancelarCambios"];
	}
	
	else if ($accion2 == 'BorrarAntecedente')
	{
		///////////////////////////////////////
		// BORRAR ANTECEDENTE
		///////////////////////////////////////
		$iTab = 5;
		$AntecedenteID = LimpiarNumero($_POST["AntecedenteID"]);
		
		$sql = "DELETE FROM \"tblEmpleadosAntecedentes\"";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"SucursalID\" = $SucursalID"; 
		$sql.= " AND \"Legajo\"='$ID'"; 
		$sql.= " AND \"AntecedenteID\" = $AntecedenteID";
		
		pg_exec($db, $sql);

		// TODO: tomar datos antes de borrar el antecedente y 
		// generar la cadena de cancelar cambios
	}
	
	else if ($accion2 == 'RecuperarFamiliar')
	{
		///////////////////////////////////////
		// RECUPERAR FAMILIAR
		///////////////////////////////////////
		$iTab = 6;
		$FamiliarID = LimpiarNumero($_POST["FamiliarID"]);
		
		$sql = "UPDATE \"tblEmpleadosFamiliares\""; 
		$sql.= " SET \"FechaBaja\" = NULL";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"SucursalID\" = $SucursalID"; 
		$sql.= " AND \"Legajo\"='$ID'"; 
		$sql.= " AND \"FamiliarID\" = $FamiliarID";
		
		pg_exec($db, $sql);

		// TODO: que tome la fecha de baja real
		// Guardo sql para cancelar cambios
		$Undo = "UPDATE \"tblEmpleadosFamiliares\""; 
		$Undo.= " SET \"FechaBaja\" = now()::date";
		$Undo.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$Undo.= " AND \"SucursalID\" = $SucursalID"; 
		$Undo.= " AND \"Legajo\"='$ID'"; 
		$Undo.= " AND \"FamiliarID\" = $FamiliarID;";

		$_SESSION["CancelarCambios"] = $Undo . $_SESSION["CancelarCambios"];
	}
	
	else if ($accion2 == 'RecuperarEstudio')
	{
		///////////////////////////////////////
		// RECUPERAR ESTUDIO
		///////////////////////////////////////
		$iTab = 7;
		$EstudioID = LimpiarNumero($_POST["TipoEstudioID"]);
		
		$sql = "UPDATE \"tblEmpleadosEstudios\""; 
		$sql.= " SET \"FechaBaja\" = NULL";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"SucursalID\" = $SucursalID"; 
		$sql.= " AND \"Legajo\"='$ID'"; 
		$sql.= " AND \"TipoEstudio\" = $EstudioID";
		
		pg_exec($db, $sql);
	
		// TODO: que tome la fecha de baja real
		// Guardo sql para cancelar cambios
		$Undo = "UPDATE \"tblEmpleadosEstudios\""; 
		$Undo.= " SET \"FechaBaja\" = now()::date";
		$Undo.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$Undo.= " AND \"SucursalID\" = $SucursalID"; 
		$Undo.= " AND \"Legajo\"='$ID'"; 
		$Undo.= " AND \"TipoEstudio\" = $EstudioID;";
		$_SESSION["CancelarCambios"] = $Undo . $_SESSION["CancelarCambios"];
	}
	
	else if ($accion2 == 'AgregarFamiliar' || $accion2 == 'EditarFamiliar')
	{
		///////////////////////////////////////
		// AGREGAR/EDITAR FAMILIAR
		///////////////////////////////////////
		$iTab = 6;
		$iError = 0;
		
		if ($accion2 == 'EditarFamiliar')
		{
			$cAdd = LimpiarNumero($_POST["FamiliarID"]);
		}
		else
		{
			$Legajo = LimpiarVariable($_POST["Legajo"]);
		}
		
		if ($accion == 'Agregar Empleado')
		{
			$sql = "SELECT em.\"Legajo\""; 
			$sql.= " FROM \"tblEmpleados\" em";
			$sql.= " WHERE em.\"Legajo\" = '$Legajo'";
		
			$rs = pg_query($db, $sql);
			if (!$rs)
			{
				exit;
			}
		
			$row = pg_fetch_array($rs);
			if ($row[0] != '')
			{
				// Ese legajo ya existe
				$iError = 4096;
				$accion2 = '';
				$iTab = 1;
				$ID = '';
			}
		}
		
		if ($iError == 0)
		{
			$selFamVinculo = LimpiarNumero($_POST["selFamVinculo$cAdd"]);
			if ($selFamVinculo < 1 || $selFamVinculo > 5)
				$iError += 1;
				
			$selFamSexo = LimpiarVariable($_POST["selFamSexo$cAdd"]);
			if ($selFamSexo != 'M' && $selFamSexo != 'F')
				$iError += 2;
				
			$famApellido = LimpiarVariable($_POST["famApellido$cAdd"]);
			if ($famApellido == '' || strlen($famApellido) > 64)
				$iError += 4;
				
			$famNombres = LimpiarVariable($_POST["famNombres$cAdd"]);
			if ($famNombres == '' || strlen($famNombres) > 128)
				$iError += 8;
				
			$selFamTipoDoc = LimpiarNumero($_POST["selFamTipoDoc$cAdd"]);
			if ($selFamTipoDoc < 1 || $selFamTipoDoc > 5)
				$iError += 16;
				
			$famNumDoc = LimpiarVariable($_POST["famNumDoc$cAdd"]);
			if ($famNumDoc == '' || strlen($famNumDoc) > 10)
				$iError += 32;
			
			$famFechaNac = LimpiarNumero($_POST["famFechaNac$cAdd"]);
			if ($famFechaNac == '')
				$iError += 64;
			else
				$famFechaNac = "'" . FechaWEB2SQL($famFechaNac) . "'";
				
			$selFamTrabaja = LimpiarNumero($_POST["selFamTrabaja$cAdd"]);
			if ($selFamTrabaja < 1 || $selFamTrabaja > 2)
				$iError += 128;
				
			$selFamCertEst = LimpiarNumero($_POST["selFamCertEst$cAdd"]);
			if ($selFamCertEst < 1 || $selFamCertEst > 2)
				$iError += 256;
				
			$selFamPoseeAsignacion = LimpiarNumero($_POST["selFamPoseeAsignacion$cAdd"]);
			if ($selFamPoseeAsignacion < 1 || $selFamPoseeAsignacion > 2)
				$iError += 512;
			$selFamDiscapacitado = LimpiarNumero($_POST["selFamDiscapacitado$cAdd"]);
			if ($selFamDiscapacitado < 1 || $selFamDiscapacitado > 2)
				$iError += 1024;
				
			$selFamACargo = LimpiarNumero($_POST["selFamACargo$cAdd"]);
			if ($selFamACargo < 1 || $selFamACargo > 2)
				$iError += 2048;
				
			$famObservaciones = LimpiarVariable($_POST["famObservaciones$cAdd"]);
			$selFamTrabaja = ($selFamTrabaja == '1' ? 'true' : 'false');
			$selFamCertEst = ($selFamCertEst == '1' ? 'true' : 'false');
			$selFamPoseeAsignacion = ($selFamPoseeAsignacion == '1' ? 'true' : 'false');
			$selFamDiscapacitado = ($selFamDiscapacitado == '1' ? 'true' : 'false');
			$selFamACargo = ($selFamACargo == '1' ? 'true' : 'false');
			
			if ($iError == 0)
			{
				if ($accion2 == 'EditarFamiliar')
				{
					$FamiliarID = LimpiarNumero($_POST["FamiliarID"]);
					
					$sql = "SELECT \"EditarFamiliar\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $FamiliarID,";
					$sql.= " $selFamVinculo,"; 
					$sql.= " '$selFamSexo'::char,"; 
					$sql.= " '$famApellido'::varchar,"; 
					$sql.= " '$famNombres'::varchar,"; 
					$sql.= " $selFamTipoDoc::int2,"; 
					$sql.= " '$famNumDoc'::varchar,"; 
					$sql.= " $famFechaNac,"; 
					$sql.= " $selFamTrabaja::boolean,"; 
					$sql.= " $selFamPoseeAsignacion::boolean,"; 
					$sql.= " 0::int4,";
					$sql.= " null,"; 
					$sql.= " $selFamCertEst::boolean,"; 
					$sql.= " $selFamDiscapacitado::boolean,"; 
					$sql.= " $selFamACargo::boolean,"; 
					$sql.= " '$famObservaciones'::varchar";
					$sql.= " )";
					
					$rs = pg_query($db, $sql);
				}
				else
				{
					$sql = "SELECT \"AgregarFamiliar\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $selFamVinculo,"; 
					$sql.= " '$selFamSexo'::char,"; 
					$sql.= " '$famApellido'::varchar,"; 
					$sql.= " '$famNombres'::varchar,"; 
					$sql.= " $selFamTipoDoc::int2,"; 
					$sql.= " '$famNumDoc'::varchar,"; 
					$sql.= " $famFechaNac,"; 
					$sql.= " $selFamTrabaja::boolean,"; 
					$sql.= " $selFamPoseeAsignacion::boolean,"; 
					$sql.= " 0::int4,"; 
					$sql.= " null,"; 
					$sql.= " $selFamCertEst::boolean,"; 
					$sql.= " $selFamDiscapacitado::boolean,"; 
					$sql.= " $selFamACargo::boolean,"; 
					$sql.= " '$famObservaciones'::varchar";
					$sql.= " )";
				
					$rs = pg_query($db, $sql);
				}
				
				$accion = 'Editar Empleado';
				if ($rs)
				{
					$row = pg_fetch_array($rs);
					$_SESSION["CancelarCambios"] = $row[0] . $_SESSION["CancelarCambios"];
				}
			}
			else
			{
				Alerta("Se produjo un error al actualizar la informacion<br>Error Numero: $iError");
			}
		}
	}
	
	else if ($accion2 == 'AgregarAntecedente' || $accion2 == 'EditarAntecedente')
	{
		///////////////////////////////////////
		// AGREGAR/EDITAR ANTECEDENTE
		///////////////////////////////////////
		$iTab = 5;
		$iError = 0;
		
		if ($accion2 == 'EditarAntecedente')
		{
			$cAdd = LimpiarNumero($_POST["AntecedenteID"]);
		}
		else
		{
			$Legajo = LimpiarVariable($_POST["Legajo"]);
		}
			
		if ($accion == 'Agregar Empleado')
		{
			$sql = "SELECT em.\"Legajo\""; 
			$sql.= " FROM \"tblEmpleados\" em";
			$sql.= " WHERE em.\"Legajo\" = '$Legajo'";
		
			$rs = pg_query($db, $sql);
			if (!$rs)
			{
				exit;
			}
				
			$row = pg_fetch_array($rs);
			if ($row[0] != '')
			{
				// Ese legajo ya existe
				$iError = 4096;
				$accion2 = '';
				$iTab = 1;
				$ID = '';
			}
		}
		
		if ($iError == 0)
		{
			$FDesde = ''; $FHasta = ''; $aAno = 0; $aMes = 0; $aDia = 0;
			$selTipoFecha = LimpiarNumero($_POST["selTipoFecha$cAdd"]);
			if ($selTipoFecha == '1')
			{
				$FDesde = FechaWEB2SQL(LimpiarNumero($_POST["antFechaDesde1$cAdd"]));
				$FHasta = FechaWEB2SQL(LimpiarNumero($_POST["antFechaHasta1$cAdd"]));
				if ($FDesde == '' || $FHasta == '')
				{
					$iError += 2;
				}
			}
			else if ($selTipoFecha == '2')
			{
				$FDesde = FechaWEB2SQL(LimpiarNumero($_POST["antFechaDesde2$cAdd"]));
				$aAno = LimpiarNumero($_POST["antAno2$cAdd"]);
				$aMes = LimpiarNumero($_POST["antMes2$cAdd"]);
				$aDia = LimpiarNumero($_POST["antDia2$cAdd"]);
				if ($aAno == '') $aAno = 0;
				if ($aMes == '') $aMes = 0;
				if ($aDia == '') $aDia = 0;
				if ($FDesde == '' || ($aAno == 0 && $aMes == 0 && $aDia == 0))
				{
					$iError += 4;
				}
			}
			else if ($selTipoFecha == '3')
			{
				$FHasta = FechaWEB2SQL(LimpiarNumero($_POST["antFechaHasta3$cAdd"]));
				$aAno = LimpiarNumero($_POST["antAno3$cAdd"]);
				$aMes = LimpiarNumero($_POST["antMes3$cAdd"]);
				$aDia = LimpiarNumero($_POST["antDia3$cAdd"]);
				if ($aAno == '') $aAno = 0;
				if ($aMes == '') $aMes = 0;
				if ($aDia == '') $aDia = 0;
				if ($FHasta == '' || ($aAno == 0 && $aMes == 0 && $aDia == 0))
				{
					$iError += 8;
				}
			}
			else
			{
				$iError += 1;
			}
			
			$FDesde = ParametroSQL($FDesde, 'date');
			$FHasta = ParametroSQL($FHasta, 'date');
			$bReconoceAnt = (LimpiarNumero($_POST["selRecAnt$cAdd"]) == '0' ? 'false' : 'true');
			$DescOrg = LimpiarVariable($_POST["DescripcionOrg"]);
			$DescOrg = ParametroSQL($DescOrg, 'varchar');

			if ($iError == 0)
			{
				if ($accion2 == 'EditarAntecedente')
				{
					$sql = "SELECT \"EditarAntecedente\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $cAdd,"; 
					$sql.= " $selTipoFecha,"; 
					$sql.= " $FDesde,"; 
					$sql.= " $FHasta,"; 
					$sql.= " $aAno,"; 
					$sql.= " $aMes,"; 
					$sql.= " $aDia,"; 
					$sql.= " $bReconoceAnt,"; 
					$sql.= " $DescOrg";
					$sql.= " )";
				
					$rs = pg_query($db, $sql);
				}
				else
				{
					$sql = "SELECT \"AgregarAntecedente\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $selTipoFecha,"; 
					$sql.= " $FDesde,"; 
					$sql.= " $FHasta,"; 
					$sql.= " $aAno,"; 
					$sql.= " $aMes,"; 
					$sql.= " $aDia,"; 
					$sql.= " $bReconoceAnt,"; 
					$sql.= " $DescOrg";
					$sql.= " )";
				
					$rs = pg_query($db, $sql);
				}
				
				$accion = 'Editar Empleado';
				if ($rs)
				{
					$row = pg_fetch_array($rs);
					if ($row[0] == 'A')
					{
						// Fechas que se cruzan
						Alerta('Las fechas ingresadas se solapan con otras ya ingresadas al sistema.');
					}
					else
					{
						$_SESSION["CancelarCambios"] = $row[0] . $_SESSION["CancelarCambios"];
					}
				}
			}
			else
			{
				Alerta("Se produjo un error al actualizar la informaci&oacute;n<br>Error Numero: $iError");
			}
		}
	}
	
	else if ($accion2 == 'AgregarEstudio' || $accion2 == 'EditarEstudio')
	{
		///////////////////////////////////////
		// AGREGAR/EDITAR ESTUDIO
		///////////////////////////////////////
		$iTab = 7;
		$iError = 0;
		if ($accion2 == 'EditarEstudio')
		{
			$cAdd = LimpiarNumero($_POST["TipoEstudioID"]);
		}
		else
		{
			$Legajo = LimpiarVariable($_POST["Legajo"]);
		}

		if ($accion == 'Agregar Empleado')
		{
			$sql = "SELECT em.\"Legajo\""; 
			$sql.= " FROM \"tblEmpleados\" em";
			$sql.= " WHERE em.\"Legajo\" = '$Legajo'";
		
			$rs = pg_query($db, $sql);
			if (!$rs)
			{
				exit;
			}
		
			$row = pg_fetch_array($rs);
			if ($row[0] != '')
			{
				// Ese legajo ya existe
				$iError = 4096;
				$accion2 = '';
				$iTab = 1;
				$ID = '';
			}
		}
		
		if ($iError == 0)
		{
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			/////////////////////////////////////////////////////////////////
			
			$estInstituto = LimpiarVariable($_POST["estInstituto$cAdd"]);
			if (strlen($estInstituto) > 64)
				$iError += 1;
				
			$estFechaFin = LimpiarNumero($_POST["estFechaFin$cAdd"]);
			if ($estFechaFin != '')
				$estFechaFin = "'" . FechaWEB2SQL($estFechaFin) . "'";
			else
				$estFechaFin = 'null';
				
			$estTitObt = LimpiarNumero($_POST["selTitObt$cAdd"]);
			if ($estTitObt == '')
				$estTitObt = '0';
				
			if ($iError == 0)
			{
				$TipoEstudioID = LimpiarNumero($_POST["TipoEstudioID"]);

				// Se trae por el formulario el valor del check de estudio Completado
				if (LimpiarVariable($_POST["chkCompletado$cAdd"]))
					$estCompletado = 1;
				else
					$estCompletado = 0;
					
				if ($accion2 == 'EditarEstudio')
				{
					$sql = "SELECT \"EditarEstudio\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $TipoEstudioID,"; 
					$sql.= " '$estInstituto'::varchar,"; 
					$sql.= " $estFechaFin,"; 
					$sql.= " $estTitObt,"; 
					$sql.= " $estCompletado";
					$sql.= " )";
				
					$rs = pg_query($db, $sql);
				}
				else
				{				
					$TipoEstudioID = LimpiarNumero($_POST["selEstTipo"]);
					
					$sql = "SELECT \"AgregarEstudio\"";
					$sql.= " (";
					$sql.= " $EmpresaID,"; 
					$sql.= " $SucursalID,"; 
					$sql.= " '$Legajo'::varchar,"; 
					$sql.= " $TipoEstudioID,"; 
					$sql.= " '$estInstituto'::varchar,"; 
					$sql.= " $estFechaFin,"; 
					$sql.= " $estTitObt,"; 
					$sql.= " $estCompletado";
					$sql.= " )";
					
					$rs = pg_query($db, $sql);
				}
				
				$accion = 'Editar Empleado';
				if ($rs)
				{
					$row = pg_fetch_array($rs);
					$_SESSION["CancelarCambios"] = $row[0] . $_SESSION["CancelarCambios"];
				}
			}
			else
			{
				Alerta("Se produjo un error al actualizar la informaci&oacute;n<br>Error Numero: $iError");
			}
		}
	}
?>
<div id=dvLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td nowrap="nowrap"><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Cargando Pagina</td></tr>
</table>
</div>
<div id=dvMenu style="display:none">
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="600" height="40">
	  <param name="movie" value="images/tabs.swf" />
	  <param name="flashvars" value="<? print "frame=$iTab";?>" />
	  <param name="quality" value="high" />
	  <embed src="images/tabs.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="600" height="40"></embed>
	</object><div id="navcontainer">&nbsp;<BR />
		<!-- CSS Tabs -->
			
<!-- SECCION DATOS DEL EMPLEADO -->
<div id=datosEmpleado style="<? print ($iTab == 1 ? print "display:block" : "display:none");?>">
<table class="datauser" align="left" border="0">
	<tr>
		<td class="izquierdo">Legajo:</td>
		<td class=derecho>
			<input type=text id=Legajo name=Legajo <? print ($accion == 'Editar Empleado' ? "readonly" : ""); ?> value="<?=$Legajo?>" size=11>
			<? if ($iError == 4096) print "<b><font color=red>Ese legajo ya existe</font></b>"; ?>
			<label id="lblNroLegajo" style="display:none; color:#FF0000;"><-- Mayor a 10000</label>
		</td>
		<td rowspan="10" valign="top" align="right"><img src="images/icon128_user.gif" width="128" height="128" hspace="10" ></td>
	</tr>
	<tr>
		<td class="izquierdo">Nombre:</td>
		<td class=derecho>
			<input type=text id=Nombre name=Nombre value="<?=$Nombre?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Apellido:</td>
		<td class=derecho>
			<input type=text id=Apellido name=Apellido value="<?=$Apellido?>">
		</td>
	</tr>
<?
	if ($Agrupamiento == '' || $Categoria == '' || $Cargo == '' || $HorasDiarias == '')
	{		
		$SueldoBasico = 'No Disponible';
	}
	else
	{
		$sql = "SELECT \"SueldoBasico\""; 
		$sql.= " FROM \"tblCategorias\""; 
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND \"Categoria\" = $Categoria"; 
		$sql.= " AND \"HorasDiarias\" = $HorasDiarias"; 
	
		$rs = pg_query($db, $sql);
		if (!$rs)
		{
			exit;
		}
		
		$row = pg_fetch_array($rs);
		if ($row)
			$SueldoBasico = $row[0];
		else
			$SueldoBasico = 'No Disponible';
	}
?>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Sueldo Basico:</td>
		<td class=derecho>
			<input type=text id=SueldoBasico name=SueldoBasico readonly="readonly" value="<?=$SueldoBasico?>">
			<label id="lblSueldoBasico" style="display:none; color:#FF0000;">&lt;-- Ingreso Manual</label>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Horas Diarias:</td>
		<td class=derecho>
			<input type=text id=HorasDiarias name=HorasDiarias value="<?=$HorasDiarias?>">
			<label id="lblHorasDiarias" style="color:#FF0000;">&lt;-- Ingreso Manual</label>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Sector:</td>
		<td class=derecho>
			<input type=text id=Sector name=Sector value="<?=$Sector?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Area:</td>
		<td class=derecho>
			<input type=text id=Area name=Area value="<?=$Area?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Centro De Costos:</td>
		<td class=derecho>
			<select id=selCentroCostosID name=selCentroCostosID>
				<?
				$sql = "SELECT cc.\"CentroDeCostoID\","; 
				$sql.= " cc.\"Descripcion\"";
				$sql.= " FROM \"tblCentroDeCostos\" cc";
				$sql.= " WHERE cc.\"EmpresaID\" = $EmpresaID"; 
				$sql.= " AND cc.\"SucursalID\" = $SucursalID";
				$sql.= " ORDER BY \"Descripcion\"";
			
				$rs = pg_query($db, $sql);
				if (!$rs)
				{
					exit;
				}
				
				while($row = pg_fetch_array($rs))
				{
					print "<option value=$row[0]";
					if ($CentroCostos == $row[0])
					{
						print " selected";
					}
					
					if ($row[1] == '')
					{
						print ">$row[0]</option>\n";
					}
					else
					{
						print ">$row[1]</option>\n";
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Tipo De Relaci&oacute;n:</td>
		<td class=derecho>
			<select id=selTipoRelacion name=selTipoRelacion onChange="javascript: TipoRelacion_OnChange();">
				<option value=1<? print ($TipoRelacion == 1 ? " selected" : ""); ?>>Mensualizado</option>
				<option value=2<? print ($TipoRelacion == 2 ? " selected" : ""); ?>>Jornalizado</option>
				<option value=3<? print ($TipoRelacion == 3 ? " selected" : ""); ?>>Contratado</option>
				<option value=4<? print ($TipoRelacion == 4 ? " selected" : ""); ?>>Locacion de Obra</option>
				<option value=5<? print ($TipoRelacion == 5 ? " selected" : ""); ?>>Pasantia</option>
			</select>

		</td>
	</tr>
	<tr>
		<td class="izquierdo">Lugar De Pago:</td>
		<td class=derecho>
			<select id=selLugarPago name=selLugarPago>
				<?
				$sql = "SELECT lp.\"LugarPago\","; 
				$sql.= " lp.\"Descripcion\"";
				$sql.= " FROM \"tblLugaresDePago\" lp";
				$sql.= " WHERE lp.\"EmpresaID\" = $EmpresaID"; 
				$sql.= " AND lp.\"Activo\" = true";
				$sql.= " ORDER BY 2";
			
				$rs = pg_query($db, $sql);
				if (!$rs)
				{
					exit;
				}
				print "<option value=0>No Seleccionado</option>\n";
				while($row = pg_fetch_array($rs))
				{
					print "<option value=$row[0]";
					if ($LugarPago == $row[0])
					{
						print " selected";
					}
					
					if ($row[1] == '')
					{
						print ">$row[0]</option>\n";
					}
					else
					{
						print ">$row[1]</option>\n";
					}
				}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">N&uacute;mero De Cuenta:</td>
		<td class=derecho>
			<input type=text id=NumeroCuenta name=NumeroCuenta value="<?=$NumeroCuenta?>">
		</td>
        
	</tr>
    <tr>
		<td class="izquierdo">Decreto Alta:</td>
		<td class=derecho>
			<input type=text id=AltaDecreto name=AltaDecreto value="<?=$AltaDecreto?>">
		</td>
        
	</tr>
</table>
</div>
<!-- FIN SECCION DATOS DEL EMPLEADO -->

<!-- SECCION DE DATOS GENERALES -->
<div id=datosGenerales style="display:none">
<table class="datauser" align="left">
	<tr>
		<td class="izquierdo">Tipo Documento:</td>
		<td class=derecho>
			<select id=selTipoDoc name=selTipoDoc>
				<option value="1"<? print ($TipoDocumento == 1 ? " selected" : ""); ?>>DNI</option>
				<option value="2"<? print ($TipoDocumento == 2 ? " selected" : ""); ?>>CI</option>
				<option value="3"<? print ($TipoDocumento == 3 ? " selected" : ""); ?>>PASAPORTE</option>
				<option value="4"<? print ($TipoDocumento == 4 ? " selected" : ""); ?>>LE</option>
				<option value="5"<? print ($TipoDocumento == 5 ? " selected" : ""); ?>>LC</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">N&uacute;mero Documento:</td>
		<td class=derecho>
			<input type=text id=NumDoc name=NumDoc value="<?=$NumeroDocumento?>">
		</td>
	</tr>
	<tr>	
		<td class="izquierdo">Fecha Ingreso:</td>
		<td class=derecho>
			<input type=text id=FechaIng name=FechaIng value="<?=$FechaIngreso?>" onfocus="showCalendarControl(this);" readonly size=11>
		</td>
	</tr>
	<tr>	
		<td class="izquierdo">Estado Civil:</td><td class=derecho>
			<select id=selEstadoCivil name=selEstadoCivil>
				<option value="1"<? print ($EstadoCivil == 1 ? " selected" : ""); ?>>Soltero/a</option>
				<option value="2"<? print ($EstadoCivil == 2 ? " selected" : ""); ?>>Casado/a</option>
				<option value="3"<? print ($EstadoCivil == 3 ? " selected" : ""); ?>>Viudo/a</option>
				<option value="4"<? print ($EstadoCivil == 4 ? " selected" : ""); ?>>Divorciado/a</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Sexo:</td>
		<td class=derecho>
			<select id=selSexo name=selSexo>
				<option value="M"<? print ($Sexo == 'M' ? " selected" : ""); ?>>Masculino</option>
				<option value="F"<? print ($Sexo == 'F' ? " selected" : ""); ?>>Femenino</option>
			</select>
		</td>
	</tr>	
	<tr>
		<td class="izquierdo">CUIT/CUIL:</td>
		<td class=derecho>
			<input type=text id=CUIT name=CUIT value="<?=$CUIT?>">
		</td>
	</tr>	
	<tr>
		<td class="izquierdo">Nacionalidad:</td>
		<td class=derecho>
			<input type=text id=Nacionalidad name=Nacionalidad value="<?=$Nacionalidad?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Pais Nacimiento:</td>
		<td class=derecho>
			<input type=text id=PaisNac name=PaisNac value="<?=$PaisNac?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Provincia Nacimiento:</td>
		<td class=derecho>
			<input type=text id=ProvNac name=ProvNac value="<?=$ProvNac?>">
		</td>
	</tr>
	<tr>	
		<td class="izquierdo">Localidad Nacimiento:</td>
		<td class=derecho>
			<input type=text id=LocNac name=LocNac value="<?=$LocalidadNac?>">
		</td>
	</tr>
	<tr>	
		<td class="izquierdo">Fecha Nacimiento:</td>
		<td class=derecho>
			<input type=text id=FechaNac name=FechaNac value="<?=$FechaNacimiento?>" onfocus="showCalendarControl(this);" readonly size=11>
		</td>
	</tr>	
</table>
</div>
<!-- FIN SECCION DE DATOS GENERALES -->

<!-- SECCION DE DOMICILIO -->
<div id=domicilio style="display:none">
<table class="datauser" align="left">
	<tr>
		<td class="izquierdo">Calle:</td><td class=derecho>
			<input type=text id=Calle name=Calle size=40 value="<?=$Calle?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Numero:</td>
		<td class=derecho>
			<input type=text id=Numero name=Numero size=7 value="<?=$Numero?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Piso:</td>
		<td class=derecho>
			<input type=text id=Piso name=Piso size=7 value="<?=$Piso?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Departamento:</td>
		<td class=derecho>
			<input type=text id=Departamento size=7 name=Departamento value="<?=$Departamento?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Localidad:</td>
		<td class=derecho>
			<input type=text id=LocalidadDom size=40 name=LocalidadDom value="<?=$LocalidadDom?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Telefono:</td>
		<td class=derecho>
			<input type=text id=Telefono name=Telefono value="<?=$Telefono?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Celular:</td>
		<td class=derecho>
			<input type=text id=Celular name=Celular value="<?=$Celular?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Email:</td>
		<td class=derecho>
			<input type=text id=Email name=Email value="<?=$Email?>">
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Codigo Postal:</td>
		<td class=derecho>
			<input type=text id=CodigoPostal name=CodigoPostal value="<?=$CodigoPostal?>">
		</td>
	</tr>
</table>
</div>
<!-- FIN SECCION DE DOMICILIO -->

<!-- SECCION DE ESTUDIOS -->
<div id=estudios style="<? print ($iTab == 7 ? print "display:block" : "display:none");?>">
<input type=hidden name=accion id=accion>
<input type=hidden name=accion2 id=accion2>
<input type=hidden name=TipoEstudioID id=TipoEstudioID>
<!--</div>-->
<div id=listaEstudios style="display:block">
	<a class="tecla" href="javascript:TeclaEstudio('agregarEstudio'); void(0);">
<img src="images/icon24_addf.gif" alt="Agregar Familiar" width="24" height="23" border="0" align="absmiddle">  Agregar Estudio </a><br /><br />
	<?
	$sql = "SELECT ee.\"TipoEstudio\","; 
	$sql.= " ee.\"Instituto\","; 
	$sql.= " ee.\"FechaFinalizacion\","; 
	$sql.= " ee.\"TituloObtenido\","; 
	$sql.= " tt.\"Descripcion\","; 
	$sql.= " ee.\"FechaBaja\","; 
	$sql.= " ee.\"Completado\"";
	$sql.= " FROM \"tblEmpleadosEstudios\" ee";
	$sql.= " LEFT JOIN \"tblTitulosObtenidos\" tt";
	$sql.= " ON tt.\"EmpresaID\" = ee.\"EmpresaID\""; 
	$sql.= " AND tt.\"TituloID\" = ee.\"TituloObtenido\"";
	$sql.= " WHERE ee.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND ee.\"SucursalID\" = $SucursalID";
	$sql.= " AND ee.\"Legajo\"='$ID'";
	$sql.= " ORDER BY \"TipoEstudio\"";

	$rs = pg_query($db, $sql);

	if (pg_numrows($rs) > 0)
	{
		$sql = "SELECT \"TituloID\",";
		$sql.= " \"Descripcion\"";
		$sql.= " FROM \"tblTitulosObtenidos\"";
		$sql.= " WHERE \"EmpresaID\" = $EmpresaID";
		$sql.= " ORDER BY 2";
	
		$rs1 = pg_query($db, $sql);
	$selTitObt = '';
	while($row = pg_fetch_array($rs1))
	{
		$selTitObt .= "<option value=\"$row[0]\">$row[1]</option>";
	}
	?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr align="center">
			<th>Tipo De Estudio</th>
			<th>Instituto</th>
			<th>Fecha Finalizacion</th>
			<th>Titulo Obtenido</th>
			<th width="24">Editar</th>
			<th width="24">Borrar</th>
		</tr>
		<?
		while($row = pg_fetch_array($rs))
		{
			$estTipoID = $row[0];
			if ($row[0] == '1')
				$estTipo = 'Primario';
			else if ($row[0] == '2')
				$estTipo = 'Secundario';
			else if ($row[0] == '3')
				$estTipo = 'Terciario';
			else if ($row[0] == '4')
				$estTipo = 'Universitario';
			else if ($row[0] == '4')
				$estTipo = 'Idiomas';
			else
				$estTipo = 'Otros';
				
			$estInstituto = $row[1];
			$estFechaFin = FechaSQL2WEB($row[2]);
			$estTitObtID = $row[3];
			
			if ($estTitObtID != '0')
				$estTitObt = $row[4];
			else
				$estTitObt = '';
			if ($estTitObtID == '')
				$estTitObtID = '0';
				
			$tdBorrado = ($row[5] == '' ? "" : "class=\"borrado\"");

			// Se obtiene el valor del campo Completado para el estudio Actual
			$estCompletado = $row[6];

		?>
		<tr align="center">
			<td <?=$tdBorrado?>><?=$estTipo?></td>
			<td <?=$tdBorrado?>><?=$estInstituto?></td>
			<td <?=$tdBorrado?>><?=$estFechaFin?></td>
			<td <?=$tdBorrado?>><?=$estTitObt?></td>
			<? if ($row[5] == '') { ?>
				<td>
					<a href="javascript:TeclaEstudio('editarEstudio<?=$estTipoID?>');void(0);">
					<img src="images/icon24_editar.gif" alt="Editar Estudio" 
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
				<td>
					<a href="javascript:BorrarEstudio(<?=$estTipoID?>);void(0);">
					<img src="images/icon24_borrar.gif" alt="Borrar Estudio" 
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
			<? } else { ?>
				<td colspan=2 <?=$tdBorrado?>><a href="javascript:RecuperarEstudio(<?=$estTipoID?>);void(0);">
					<img src="images/icon24_resucitar.gif" alt="Recuperar Estudio"
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
			<? } ?>
		</tr>
		<?
		$EditarEst = "<div id=editarEstudio$estTipoID style=\"display:none\">\n";
		$EditarEst.= "<table class='datauser'>";
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'>Tipo De Estudio:</td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<select id=selEstTipo$estTipoID name=selEstTipo$estTipoID disabled>\n";
		$EditarEst.= "<option value=1" . ($estTipo == 'Primario' ? " selected" : "") . ">Primario</option>";
		$EditarEst.= "<option value=2" . ($estTipo == 'Secundario' ? " selected" : "") . ">Secundario</option>";
		$EditarEst.= "<option value=3" . ($estTipo == 'Terciario' ? " selected" : "") . ">Terciario</option>";
		$EditarEst.= "<option value=4" . ($estTipo == 'Universitario' ? " selected" : "") . ">Universitario</option>";
		$EditarEst.= "<option value=5" . ($estTipo == 'Idiomas' ? " selected" : "") . ">Idiomas</option>";
		$EditarEst.= "<option value=6" . ($estTipo == 'Otros' ? " selected" : "") . ">Otros</option>";
		$EditarEst.= "</select>";
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>\n";
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'>Instituto:</td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<input type=text name=estInstituto$estTipoID id=estInstituto$estTipoID value=\"$estInstituto\" size=40>";
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>\n";
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'>Fecha Finalizacion:</td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<input type=text name=estFechaFin$estTipoID id=estFechaFin$estTipoID value=\"$estFechaFin\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>\n";
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'>Titulo Obtenido:</td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<select id=selTitObt$estTipoID name=selTitObt$estTipoID>$selTitObt</select>";
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>\n";
	
		// Se arma el codigo HTML del checkbox de estudio Completado y se presetea si esta tildado o no
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'>Completado:</td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<input type=\"checkbox\" id=chkCompletado$estTipoID name=chkCompletado$estTipoID value=1";
		
		if ($estCompletado == 1)
		{
			$EditarEst.= " checked >";
		}
		else
		{
			$EditarEst.= " unchecked >";
		}
			
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>\n";
		$EditarEst.= "<tr>";
		$EditarEst.= "<td class='izquierdo'></td>";
		$EditarEst.= "<td class=derecho>";
		$EditarEst.= "<a class=tecla href=\"javascript:EditarEstudio($estTipoID);void(0);\">";
		$EditarEst.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios";
		$EditarEst.= "</a>&nbsp;&nbsp;&nbsp;";
		$EditarEst.= "<a class=tecla href=\"javascript:VolverEstudio('editarEstudio$estTipoID');void(0);\">";
		$EditarEst.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
		$EditarEst.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver";
		$EditarEst.= "</a>";
		$EditarEst.= "</td>";
		$EditarEst.= "</tr>";
		$EditarEst.= "</table>\n";
		$EditarEst.= "<script>\n";
		$EditarEst.= "var i, oSel;\n";
		$EditarEst.= "oSel = document.getElementById('selTitObt$estTipoID');\n";
		$EditarEst.= "for(i=0;i<oSel.options.length;i++){\n";
		$EditarEst.= "if (oSel.options[i].value == $estTitObtID){\n";
		$EditarEst.= "oSel.options[i].selected = true; break; } }\n";
		$EditarEst.= "</script>\n";
		$EditarEst.= "</div>\n";
		}
		print "</table>\n";
	}
	else
	{
		Alerta('Este empleado no tiene estudios registrados');
	}
	
	$AgregarEst = "<div id=agregarEstudio style=\"display:none\">\n";
	$AgregarEst.= "<table class='datauser'>";
	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'>Tipo De Estudio:</td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<select id=selEstTipo name=selEstTipo>\n";
	$AgregarEst.= "<option value=1>Primario</option>";
	$AgregarEst.= "<option value=2>Secundario</option>";
	$AgregarEst.= "<option value=3>Terciario</option>";
	$AgregarEst.= "<option value=4>Universitario</option>";
	$AgregarEst.= "<option value=5>Idiomas</option>";
	$AgregarEst.= "<option value=6>Otros</option>";
	$AgregarEst.= "</select>";
	$AgregarEst.= "</td>";
	$AgregarEst.= "</tr>\n";
	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'>Instituto:</td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<input type=text name=estInstituto id=estInstituto size=40></td></tr>\n";
	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'>Fecha Finalizacion:</td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<input type=text name=estFechaFin id=estFechaFin size=11 onfocus=\"showCalendarControl(this);\" readonly>";
	$AgregarEst.= "</td>";
	$AgregarEst.= "</tr>\n";
	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'>Titulo Obtenido:</td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<select id=selTitObt name=selTitObt>$selTitObt</select>";
	$AgregarEst.= "</td>";
	$AgregarEst.= "</tr>\n";

// Se arma el codigo HTML del checkbox de estudio Completado y se presetea si esta tildado o no
	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'>Completado:</td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<input type=\"checkbox\" id=chkCompletado$estTipoID name=chkCompletado$estTipoID value=1 unchecked>";
	$AgregarEst.= "</td>";
	$AgregarEst.= "</tr>\n";

	$AgregarEst.= "<tr>";
	$AgregarEst.= "<td class='izquierdo'></td>";
	$AgregarEst.= "<td class=derecho>";
	$AgregarEst.= "<a class=tecla href=\"javascript:AgregarEstudio();void(0);\">";
	$AgregarEst.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios";
	$AgregarEst.= "</a>&nbsp;&nbsp;&nbsp;";
	$AgregarEst.= "<a class=tecla href=\"javascript:VolverEstudio('agregarEstudio');void(0);\">";
	$AgregarEst.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
	$AgregarEst.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver";
	$AgregarEst.= "</a>";
	$AgregarEst.= "</td>";
	$AgregarEst.= "</tr>";
	$AgregarEst.= "</table>\n";
	$AgregarEst.= "</div>\n";
?>
</div>
<!-- DIVS de editar estudios --> <?=$EditarEst?>
<!-- DIVS de agregar estudios --> <?=$AgregarEst?>
</div>
<!-- FIN SECCION DE ESTUDIOS -->

<!-- SECCION DE RAFAM -->
<div id=rafam style="display:none">
	<? $AnioPresup = date("Y"); ?>
	<table class="datauser" align="left" id="tblRafamError" style="display:none">
		<tr>
			<td class="centro" colspan="2">
				<div class=alerta>
					Los Datos de RAFAM no estan disponibles para empleados contratados en condicion de "Locacion de Obra".
				</div>
			</td>
		</tr>	
	</table>
	<table class="datauser" align="left" id="tblRafam">
		<tr>
			<td class="izquierdo">A&ntilde;o Presupuesto:</td>
			<td class=derecho>
				<input type=text id=AnioPresup name=AnioPresup value="<?=$AnioPresup?>" disabled>
			</td>
		</tr>
		<tr>
			<td class="izquierdo">Tipo De Planta:</td>
			<td class=derecho>
				<select id=selTipoPlanta name=selTipoPlanta>
					<option value=1 <? print ($TipoDePlanta == '1' ? "selected" : ""); ?>>Permanente</option>
					<option value=2 <? print ($TipoDePlanta == '2' ? "selected" : ""); ?>>Temporario</option>
					<option value=3 <? print ($TipoDePlanta == '3' ? "selected" : ""); ?>>Locacion de obra</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="izquierdo">Fuente de Financiamiento:</td>
			<td class=derecho>
				<select id=selFuenteFinanciamiento name=selFuenteFinanciamiento>
				<?
				$sql = "SELECT codigo_ff,"; 
				$sql.= " denominacion";
				$sql.= " FROM owner_rafam.fuen_fin";
				$sql.= " WHERE anio_presup = $AnioPresup AND totalizadora = 'N'";
				$sql.= " ORDER BY 1";
			
				$rs = pg_query($db, $sql);
				if (!$rs)
				{
					exit;
				}
				
				while($row = pg_fetch_array($rs))
				{
					print "<option value=$row[0]";
					if ($CodigoFF == $row[0])
					{
						print " selected";
					}
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
			<td class="izquierdo">Jurisdicci&oacute;n:</td>
			<td class=derecho>
				<select id=selJurisdiccion name=selJurisdiccion onchange="javascript:RafamCombos(this.options[selectedIndex].value, 0, 0, 0);">
					<option value=0>Elija jurisdicci&oacute;n</option>
					<?
					$sql = "SELECT jurisdiccion,"; 
					$sql.= " denominacion";
					$sql.= " FROM owner_rafam.jurisdicciones";
					$sql.= " WHERE seleccionable = 'S'";
					$sql.= " ORDER BY 1";
				
					$rs = pg_query($db, $sql);
					if (!$rs)
					{
						exit;
					}
					
					while($row = pg_fetch_array($rs))
					{
						print "<option value=$row[0]";
						if ($Jurisdiccion == $row[0])
						{
							print " selected";
						}
						if ($row[1] == '')
							print ">$row[0]</option>\n";
						else
							print ">$row[1]</option>\n";
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="rowAgrupamiento">
			<td class="izquierdo">Agrupamiento:</td>
			<td class=derecho>
				<select id=selAgrupamiento name=selAgrupamiento disabled onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, this.options[selectedIndex].value, 0, 0);">
					<option value=0>Elija un agrupamiento</option>
				</select>
			</td>
		</tr>
		<tr id="rowCategoria">
			<td class="izquierdo">Categoria:</td>
			<td class=derecho>
				<select id=selCategoria name=selCategoria disabled 
				onchange="javascript:RafamCombos(selJurisdiccion.options[selJurisdiccion.selectedIndex].value, 
					selAgrupamiento.options[selAgrupamiento.selectedIndex].value, this.options[selectedIndex].value, 0);">
					<option value=0>Elija una categoria</option>
				</select>
			</td>
		</tr>
		<tr id="rowCargo">
			<td class="izquierdo">Cargo:</td>
			<td class=derecho>
				<select id=selCargo name=selCargo disabled>
					<option value=0>Elija un cargo</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="izquierdo">Programa:</td>
			<td class=derecho>
				<input type=text id=Programa name=Programa value="<?=$Programa?>">
			</td>
		</tr>
		<tr>
			<td class="izquierdo">Actividad-Proyecto:</td>
			<td class=derecho>
				<input type=text id=Activ_Proy name=Activ_Proy value="<?=$Activ_Proy?>">
			</td>
		</tr>
		<tr>
			<td class="izquierdo">Actividad-Obra:</td>
			<td class=derecho>
				<input type=text id=Activ_Obra name=Activ_Obra value="<?=$Activ_Obra?>">
			</td>
		</tr>
	</table>
			<script>
function TipoRelacion_OnChange()
{
	var TipoRelacion = document.getElementById('selTipoRelacion').value;
		
	if (TipoRelacion == 4)
	{
		document.getElementById('tblRafam').style.display = 'block';
		document.getElementById('tblRafamError').style.display = 'none';
	
		document.getElementById('lblSueldoBasico').style.display = 'block';
		//document.getElementById('lblHorasDiarias').style.display = 'block';
		document.getElementById('lblNroLegajo').style.display = 'block';
		
		document.getElementById('SueldoBasico').disabled = false;
		//document.getElementById('HorasDiarias').disabled = false;
		document.getElementById('SueldoBasico').value = '';
		document.getElementById('HorasDiarias').value = '';
		
		document.getElementById('rowAgrupamiento').style.display = 'none';
		document.getElementById('rowCategoria').style.display = 'none';
		document.getElementById('rowCargo').style.display = 'none';
		//document.getElementById('Programa').value = '';
		//document.getElementById('Activ_Proy').value = '';
		//document.getElementById('Activ_Obra').value = '';
	}
	else
	{
		document.getElementById('tblRafam').style.display = 'block';
		document.getElementById('tblRafamError').style.display = 'none';
	
		document.getElementById('lblSueldoBasico').style.display = 'none';
		//document.getElementById('lblHorasDiarias').style.display = 'none';
		document.getElementById('lblNroLegajo').style.display = 'none';

		document.getElementById('SueldoBasico').disabled = true;
		//document.getElementById('HorasDiarias').disabled = true;
		document.getElementById('SueldoBasico').value = '<?=$SueldoBasico?>';	
		document.getElementById('HorasDiarias').value = '<?=$HorasDiarias?>';

		document.getElementById('rowAgrupamiento').style.display = 'block';
		document.getElementById('rowCategoria').style.display = 'block';
		document.getElementById('rowCargo').style.display = 'block';
	}
}

				TipoRelacion_OnChange();
			</script>
	<script>
		RafamCombos('<?=$Jurisdiccion?>', 0, 0, 0);
		RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', 0, 0);
		RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', '<?=$Categoria?>', 0);
		RafamCombos('<?=$Jurisdiccion?>', '<?=$Agrupamiento?>', '<?=$Categoria?>', '<?=$Cargo?>');
		
	</script>
</div>
<!-- FIN SECCION DE RAFAM -->

<!-- SECCION DE ANTECEDENTES -->
<div id=antecedentes style="<? print ($iTab == 5 ? print "display:block" : "display:none");?>">
<input type=hidden name=AntecedenteID id=AntecedenteID>
<div id=listaAntecedentes style="display:block">
		<a class="tecla" href="javascript:TeclaAntecedente('agregarAntecedente'); void(0);">
<img src="images/icon24_addf.gif" alt="Agregar Antecedente" width="24" height="23" border="0" align="absmiddle">  Agregar Antecedente </a><br /><br />
<?
	$sql = "SELECT an.\"AntecedenteID\","; 
	$sql.= " an.\"FechaDesde\","; 
	$sql.= " an.\"FechaHasta\","; 
	$sql.= " an.\"ReconoceAntiguedad\","; 
	$sql.= " an.\"DescripcionOrganismo\",";
	$sql.= " extract('year' from age(\"FechaHasta\",\"FechaDesde\")), extract('month' from age(\"FechaHasta\",\"FechaDesde\")),";
	$sql.= " extract('day' from age(\"FechaHasta\",\"FechaDesde\"))";
	$sql.= " FROM \"tblEmpleadosAntecedentes\" an";
	$sql.= " WHERE an.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND an.\"SucursalID\" = $SucursalID"; 
	$sql.= " AND an.\"Legajo\"='$ID'";
	$sql.= " ORDER BY 2";

	$rs = pg_query($db, $sql);
	
	if (pg_numrows($rs) > 0)
	{
?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr align="center">
			<th nowrap>Fecha Desde</th>
			<th nowrap>Fecha Hasta</th>
			<th>Reconoce Antiguedad</th>
			<th>Antiguedad</th>
			<th>Descripci&oacute;n</th>
			<th width="24">Editar</th>
			<th width="24">Borrar</th>
		</tr>
		<?
		while($row = pg_fetch_array($rs))
		{
			$antID = $row[0];
			$antFecDesde = FechaSQL2WEB($row[1]);
			$antFecHasta = FechaSQL2WEB($row[2]);
			$antReconoce = ($row[3] == 't' ? 'Si' : 'No');
			$antDescOrg = $row[4];
			$antAno = $row[5];
			$antMes = $row[6];
			$antDia = $row[7];
			$Ant = '';
			if ($antAno > 0)
				$Ant .= "$antAno a&ntilde;os ";
			if ($antMes > 0)
				$Ant .= "$antMes meses ";
			if ($antDia > 0)
				$Ant .= "$antDia d&iacute;as ";
		?>
		<tr align="center">
			<td><?=$antFecDesde?></td>
			<td><?=$antFecHasta?></td>
			<td><?=$antReconoce?></td>
			<td><?=$Ant?></td><td><?=$antDescOrg?></td>
			<td>
				<a href="javascript:TeclaAntecedente('editarAntecedente<?=$antID?>');void(0);">
				<img src="images/icon24_editar.gif" alt="Editar Antecedente" 
				align="absmiddle" width="24" height="24" border="0"></a>
			</td>
			<td>
				<a href="javascript:BorrarAntecedente(<?=$antID?>);void(0);">
				<img src="images/icon24_borrar.gif" alt="Borrar Antecedente" 
				align="absmiddle" width="24" height="24" border="0"></a>
			</td>
		</tr>
		<?
			$EditarAnt = "<div id=editarAntecedente$antID style=\"display:none\">\n";
			$EditarAnt.= "<table class='datauser'>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forma de Ingreso:</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<select id=selTipoFecha$antID name=selTipoFecha$antID onchange=\"CambioAntecedente(this.value, '$antID');\">";
			$EditarAnt.= "<option value=1>Desde - Hasta</option>";
			$EditarAnt.= "<option value=2 selected>Desde - Antiguedad</option>";
			$EditarAnt.= "<option value=3>Hasta - Antiguedad</option>";
			$EditarAnt.= "</select>";
			$EditarAnt.= "Elija la forma de ingresar la antiguedad</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "</table>\n";
			$EditarAnt.= "<div id=dvEdAnt1$antID style=\"display:none\">";
			$EditarAnt.= "<table class='datauser'>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Desde:";
			$EditarAnt.= "</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<input type=text name=antFechaDesde1$antID id=antFechaDesde1$antID value=\"$antFecDesde\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>\n";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>Fecha Hasta:</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<input type=text name=antFechaHasta1$antID id=antFechaHasta1$antID value=\"$antFecHasta\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "</table>";
			$EditarAnt.= "</div>\n";
			$EditarAnt.= "<div id=dvEdAnt2$antID style=\"display:block\">";
			$EditarAnt.= "<table class='datauser'>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Desde:</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<input type=text name=antFechaDesde2$antID id=antFechaDesde2$antID value=\"$antFecDesde\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>\n";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>Antiguedad:</td>";
			$EditarAnt.= "<td class=derecho2>A&ntilde;o";
			$EditarAnt.= "<input type=text name=antAno2$antID id=antAno2$antID value=\"$antAno\" size=5>"; 
			$EditarAnt.= " Mes"; 
			$EditarAnt.= "<input type=text name=antMes2$antID id=antMes2$antID value=\"$antMes\" size=5>"; 
			$EditarAnt.= " Dia"; 
			$EditarAnt.= "<input type=text name=antDia2$antID id=antDia2$antID value=\"$antDia\" size=5>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "</table>";
			$EditarAnt.= "</div>\n";
			$EditarAnt.= "<div id=dvEdAnt3$antID style=\"display:none\">";
			$EditarAnt.= "<table class='datauser'>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Hasta:</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<input type=text name=antFechaHasta3$antID id=antFechaHasta3$antID value=\"$antFecHasta\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>\n";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>Antiguedad:</td>";
			$EditarAnt.= "<td class=derecho2>A&ntilde;o"; 
			$EditarAnt.= "<input type=text name=antAno3$antID id=antAno3$antID value=\"$antAno\" size=5>"; 
			$EditarAnt.= " Mes"; 
			$EditarAnt.= "<input type=text name=antMes3$antID id=antMes3$antID value=\"$antMes\" size=5>"; 
			$EditarAnt.= " Dia"; 
			$EditarAnt.= "<input type=text name=antDia3$antID id=antDia3$antID value=\"$antDia\" size=5>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "</table>";
			$EditarAnt.= "</div>\n";
			$EditarAnt.= "<table class='datauser'>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>Reconoce Antiguedad:</td>";
			$EditarAnt.= "<td class=derecho>";
			$EditarAnt.= "<select name=selRecAnt$antID id=selRecAnt$antID>";
			$EditarAnt.= "<option value=0" . ($row[3] == 't' ? '' : ' selected') . ">No</option>";
			$EditarAnt.= "<option value=1" . ($row[3] == 't' ? ' selected' : '') . ">Si</option>";
			$EditarAnt.= "</select>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'>Descripci&oacute;n Org.:</td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<input type=text name=DescripcionOrg$antID id=DescripcionOrg$antID value=\"$antDescOrg\" size=58>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "<tr>";
			$EditarAnt.= "<td class='izquierdo'></td>";
			$EditarAnt.= "<td class=derecho2>";
			$EditarAnt.= "<a class=tecla href=\"javascript:EditarAntecedente($antID);void(0);\">";
			$EditarAnt.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios </a>&nbsp;&nbsp;&nbsp;";
			$EditarAnt.= "<a class=tecla href=\"javascript:VolverAntecedente('editarAntecedente$antID');void(0);\">";
			$EditarAnt.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
			$EditarAnt.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver </a>";
			$EditarAnt.= "</td>";
			$EditarAnt.= "</tr>";
			$EditarAnt.= "</table>\n";
			$EditarAnt.= "</div>\n";
		}
		print "</table>\n";
	}
	else
	{
		Alerta('Este empleado no tiene antecedentes registrados');
	}
	
	$AgregarAnt = "<div id=agregarAntecedente style=\"display:none\">\n";
	$AgregarAnt.= "<table class='datauser'>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Forma de Ingreso:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<select id=selTipoFecha name=selTipoFecha onchange=\"CambioAntecedente(this.value, '');\">";				
	$AgregarAnt.= "<option value=1>Desde - Hasta</option><option value=2 selected>Desde - Antiguedad</option>";	
	$AgregarAnt.= "<option value=3>Hasta - Antiguedad</option>";
	$AgregarAnt.= "</select>";
	$AgregarAnt.= "Elija la forma de ingresar la antiguedad</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "</table>\n";
	$AgregarAnt.= "<div id=dvAgAnt1 style=\"display:none\">";
	$AgregarAnt.= "<table class='datauser'>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Desde:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<input type=text name=antFechaDesde1 id=antFechaDesde1 size=11 onfocus=\"showCalendarControl(this);\" readonly>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>\n";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>Fecha Hasta:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<input type=text name=antFechaHasta1 id=antFechaHasta1 size=11 onfocus=\"showCalendarControl(this);\" readonly>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "</table>";
	$AgregarAnt.= "</div>\n";
	$AgregarAnt.= "<div id=dvAgAnt2 style=\"display:block\"><table class='datauser'>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Desde:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<input type=text name=antFechaDesde2 id=antFechaDesde2 size=11 onfocus=\"showCalendarControl(this);\" readonly>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>\n";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>Antiguedad:</td>";
	$AgregarAnt.= "<td class=derecho2>A&ntilde;o"; 
	$AgregarAnt.= "<input type=text name=antAno2 id=antAno2 size=5>"; 
	$AgregarAnt.= "Mes"; 
	$AgregarAnt.= "<input type=text name=antMes2 id=antMes2 size=5>"; 
	$AgregarAnt.= "Dia"; 
	$AgregarAnt.= "<input type=text name=antDia2 id=antDia2 size=5>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "</table>";
	$AgregarAnt.= "</div>\n";
	$AgregarAnt.= "<div id=dvAgAnt3 style=\"display:none\">";
	$AgregarAnt.= "<table class='datauser'>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fecha Hasta:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<input type=text name=antFechaHasta3 id=antFechaHasta3 size=11 onfocus=\"showCalendarControl(this);\" readonly>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>\n";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>Antiguedad:</td>";
	$AgregarAnt.= "<td class=derecho2>A&ntilde;o"; 
	$AgregarAnt.= "<input type=text name=antAno3 id=antAno3 size=5>"; 
	$AgregarAnt.= "Mes"; 
	$AgregarAnt.= "<input type=text name=antMes3 id=antMes3 size=5>";
	$AgregarAnt.= "Dia";
	$AgregarAnt.= "<input type=text name=antDia3 id=antDia3 size=5>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "</table>";
	$AgregarAnt.= "</div>\n";
	$AgregarAnt.= "<table class='datauser'>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>Reconoce Antiguedad:</td>";
	$AgregarAnt.= "<td class=derecho>";
	$AgregarAnt.= "<select name=selRecAnt id=selRecAnt>";
	$AgregarAnt.= "<option value=0>No</option>";
	$AgregarAnt.= "<option value=1>Si</option>";
	$AgregarAnt.= "</select>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>Descripci&oacute;n Org.:</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<input type=text name=DescripcionOrg id=DescripcionOrg size=58>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "<tr>";
	$AgregarAnt.= "<td class='izquierdo'>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "<td class=derecho2>";
	$AgregarAnt.= "<a class=tecla href=\"javascript:AgregarAntecedente();void(0);\">";
	$AgregarAnt.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios </a>&nbsp;&nbsp;&nbsp;";
	$AgregarAnt.= "<a class=tecla href=\"javascript:VolverAntecedente('agregarAntecedente');void(0);\">";
	$AgregarAnt.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
	$AgregarAnt.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver </a>";
	$AgregarAnt.= "</td>";
	$AgregarAnt.= "</tr>";
	$AgregarAnt.= "</table>\n";
	$AgregarAnt.= "</div>\n";
?>
</div>
<!-- DIVS de editar antecedentes --> <?=$EditarAnt?>
<!-- DIVS de agregar antecedentes --> <?=$AgregarAnt?>
</div>
<!-- FIN SECCION DE ANTECEDENTES -->

<!-- SECCION DE PRESUPUESTO -->
<?
?>
<div id=presupuesto style="display:none">
<table class="datauser" align="left">
	<tr>
		<td align=left>
			<br><b>Personal <? print  ($TipoDePlanta == '1' ? "Permanente" : "Temporario"); ?></b>
		</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="izquierdo">Retribuciones del cargo:</td>
		<td class=derecho>
			<input type=text id=Gastos1 name=Gastos1 value="<?=$Gastos1?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Retribuciones a personal directivo y de control:</td>
		<td class=derecho>
			<input type=text id=Gastos2 name=Gastos2 value="<?=$Gastos2?>" size=6 <? print ($TipoDePlanta == '2' ? 'disabled' : ''); ?>>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Retribuciones que no hacen al cargo:</td>
		<td class=derecho>
			<input type=text id=Gastos3 name=Gastos3 value="<?=$Gastos3?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Sueldo anual complementario:</td>
		<td class=derecho>
			<input type=text id=Gastos4 name=Gastos4 value="<?=$Gastos4?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Otros gastos en personal:</td>
		<td class=derecho>
			<input type=text id=Gastos5 name=Gastos5 value="<?=$Gastos5?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Contribucion Patronal IPS:</td>
		<td class=derecho>
			<input type=text id=Gastos6 name=Gastos6 value="<?=$Gastos6?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Contribucion Patronal IOMA:</td>
		<td class=derecho>
			<input type=text id=Gastos7 name=Gastos7 value="<?=$Gastos7?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Complementos:</td>
		<td class=derecho>
			<input type=text id=Gastos8 name=Gastos8 value="<?=$Gastos8?>" size=6>
		</td>
	</tr>
	<tr>
		<td align=left><br><b>Servicios extraordinarios</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="izquierdo">Retribuciones extraordinarias:</td>
		<td class=derecho>
			<input type=text id=Gastos9 name=Gastos9 value="<?=$Gastos9?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Sueldo anual complementario:</td>
		<td class=derecho>
			<input type=text id=Gastos10 name=Gastos10 value="<?=$Gastos10?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Contribuciones patronales:</td>
		<td class=derecho>
			<input type=text id=Gastos11 name=Gastos11 value="<?=$Gastos11?>" size=6>
		</td>
	</tr>
	<tr>
		<td align=left><br><b>Otros</b></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="izquierdo">Asignaciones familiares:</td>
		<td class=derecho>
			<input type=text id=Gastos12 name=Gastos12 value="<?=$Gastos12?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Asistencia social al personal:</td>
		<td class=derecho>
			<input type=text id=Gastos13 name=Gastos13 value="<?=$Gastos13?>" size=6>
		</td>
	</tr>
	<tr>
		<td class="izquierdo">Beneficios y compensaciones:</td>
		<td class=derecho>
			<input type=text id=Gastos14 name=Gastos14 value="<?=$Gastos14?>" size=6>
		</td>
	</tr>
</table>
</div>
<!-- FIN SECCION DE PRESUPUESTO -->

<!-- SECCION DE FAMILIARES -->
<table class="datauser" align="left" id="tblFamiliarError" style="display:none">
	<tr>
		<td class="centro" colspan="2">
			<div class=alerta>
				Debe cargar los datos del empleado para poder acceder a esta seccion.
			</div>
		</td
	></tr>	
</table>
<div id=familiares style="<? print ($iTab == 6 ? "display:block" : "display:none");?>">
<input type=hidden name=FamiliarID id=FamiliarID>
<div id=listaFamiliares style="display:block">
		<a class="tecla" href="javascript:TeclaFamiliar('agregarFamiliar'); void(0);">
<img src="images/icon24_addf.gif" alt="Agregar Familiar" width="24" height="23" border="0" align="absmiddle">  Agregar Familiar </a><br /><br />
	<?
	$sql = "SELECT \"TipoDeVinculo\","; 
	$sql.= " \"Sexo\","; 
	$sql.= " \"Apellido\","; 
	$sql.= " \"Nombres\","; 
	$sql.= " \"TipoDocumento\","; 
	$sql.= " \"NumeroDocumento\","; 
	$sql.= " \"FechaNacimiento\",";
	$sql.= " \"Trabaja\","; 
	$sql.= " \"PoseeAsignacion\","; 
	$sql.= " \"TipoAsignacion\","; 
	$sql.= " \"FechaVencimientoAsignacion\","; 
	$sql.= " \"CertificadoEstudios\",";
	$sql.= " \"Discapacitado\","; 
	$sql.= " \"ACargo\","; 
	$sql.= " \"Observaciones\","; 
	$sql.= " \"FamiliarID\","; 
	$sql.= " \"FechaBaja\"";
	$sql.= " FROM \"tblEmpleadosFamiliares\" ef";
	$sql.= " WHERE ef.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND ef.\"SucursalID\" = $SucursalID"; 
	$sql.= " AND ef.\"Legajo\"='$ID'";
	$sql.= " ORDER BY \"TipoDeVinculo\", \"FamiliarID\"";

	$rs = pg_query($db, $sql);
	if (pg_numrows($rs) > 0)
	{
	?>		
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr align="center">
			<th>Vinculaci&oacute;n</th>
			<th>Apellido y Nombre</th>
			<th width="24">Ver</th>
			<th width="24">Editar</th>
			<th width="24">Borrar</th>
		</tr>
		<?
		$VerFam = "";
		while($row = pg_fetch_array($rs))
		{
			if ($row[0] == '1')
				$famVinculo = 'Conyuge';
			else if ($row[0] == '2')
				$famVinculo = 'Hijo';
			else if ($row[0] == '3')
				$famVinculo = 'Padre';
			else if ($row[0] == '4')
				$famVinculo = 'Hermano';
			else
				$famVinculo = 'Otros';
				
			$famApellido = $row[2];
			$famNombres = $row[3];
			
			if ($row[1] == 'M')
				$famSexo = 'Masculino';
			else
				$famSexo = 'Femenino';
			if ($row[4] == '2')
				$famTipoDoc = 'CI';
			else if ($row[4] == '3')
				$famTipoDoc = 'PASAPORTE';
			else if ($row[4] == '4')
				$famTipoDoc = 'LE';
			else if ($row[4] == '5')
				$famTipoDoc = 'LC';
			else
				$famTipoDoc = 'DNI';
				
			$famNumDoc 			= $row[5];
			$famFechaNac 		= FechaSQL2WEB($row[6]);
			$famTrabaja 		= ($row[7] == 't' ? 'Si' : 'No');
			$famPoseeAsignacion = ($row[8] == 't' ? 'Si' : 'No');
			$famTipoAsigna 		= $row[9];
			$famFechaVecAsig 	= $row[10];
			$famCertificadoEst 	= ($row[11] == 't' ? 'Si' : 'No');
			$famDiscapacitado 	= ($row[12] == 't' ? 'Si' : 'No');
			$famACargo 			= ($row[13] == 't' ? 'Si' : 'No');
			$famObservacion 	= $row[14];
			$famFamiliarID 		= $row[15];
			$tdBorrado 			= ($row[16] == '' ? "" : "class=\"borrado\"");
			?>
			<tr align="center">
				<td <?=$tdBorrado?>><?=$famVinculo?></td>
				<td <?=$tdBorrado?>><?=$famApellido?>, <?=$famNombres?></td>
				<td <?=$tdBorrado?>>
					<a href="javascript:TeclaFamiliar('verFamiliar<?=$famFamiliarID?>');void(0);">
					<img src="images/icon24_ver.gif" alt="Ver Familiar" 
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
			<? if ($row[16] == '') { ?>
				<td>
					<a href="javascript:TeclaFamiliar('editarFamiliar<?=$famFamiliarID?>');void(0);">
					<img src="images/icon24_editar.gif" alt="Editar Familiar" 
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
				<td>
					<a href="javascript:BorrarFamiliar(<?=$famFamiliarID?>);void(0);">
					<img src="images/icon24_borrar.gif" alt="Borrar Familiar" 
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
			<? } else { ?>
				<td colspan=2 <?=$tdBorrado?>>
					<a href="javascript:RecuperarFamiliar(<?=$famFamiliarID?>);void(0);">
					<img src="images/icon24_resucitar.gif" alt="Recuperar Familiar"
					align="absmiddle" width="24" height="24" border="0"></a>
				</td>
			<? } ?>
			</tr>
			<?
			$VerFam.= "<div id=verFamiliar$famFamiliarID style=\"display:none\">\n";
			$VerFam.= "<table class='datauser'><tr>";
			$VerFam.= "<td class='izquierdo'>Vinculo:</td>";
			$VerFam.= "<td class=derecho>$famVinculo</td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Apellido:</td>";
			$VerFam.= "<td class=derecho>$famApellido</td>";
			$VerFam.= "</tr>";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Nombres:</td>";
			$VerFam.= "<td class=derecho>$famNombres</td>";
			$VerFam.= "</tr>";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Sexo:</td>";
			$VerFam.= "<td class=derecho>$famSexo</td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Tipo Documento:</td>";
			$VerFam.= "<td class=derecho>$famTipoDoc</td>";
			$VerFam.= "</tr>";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Numero Documento:</td>";
			$VerFam.= "<td class=derecho>$famNumDoc</td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Fecha Nacimiento:</td>";
			$VerFam.= "<td class=derecho>$famFechaNac</td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Trabaja?:</td>";
			$VerFam.= "<td class=derecho>$famTrabaja</td>";
			$VerFam.= "</tr>";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Certificado De Estudios?:</td>";
			$VerFam.= "<td class=derecho>$famCertificadoEst </td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Posee Asignacion?:</td>";
			$VerFam.= "<td class=derecho>$famPoseeAsignacion </td>";
			$VerFam.= "</tr>\n";
	//		$VerFam.= "<TR> <TD class='izquierdo'>Tipo Asignacion:</td><TD class=derecho>$famTipoAsigna</TD></TR>\n";
	//		$VerFam.= "<TR> <TD class='izquierdo'>Fecha Vencimiento Asignacion:</td><TD class=derecho>$famFechaVecAsig</TD></TR>\n";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Discapacitado?:</td>";
			$VerFam.= "<td class=derecho>$famDiscapacitado</td>";
			$VerFam.= "</tr>";
			$VerFam.= "<tr>";
			$VerFam.= "<td class='izquierdo'>Familiar A Cargo?:</td>";
			$VerFam.= "<td class=derecho> $famACargo</td>";
			$VerFam.= "</tr>\n";
			$VerFam.= "<tr>";
			$VerFam.= " <td class='izquierdo'>Observaciones:</td>";
			$VerFam.= " <td class=derecho>$famObservacion</td>";
			$VerFam.= " </tr>\n";
			$VerFam.= "<td class='izquierdo'></td>";
			$VerFam.= "<td>";
			$VerFam.= "<br>";
			$VerFam.= "<a class=tecla href=\"javascript:VolverFamiliar('verFamiliar$famFamiliarID');void(0);\">";
			$VerFam.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
			$VerFam.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver </a>";
			$VerFam.= "</td>";
			$VerFam.= "</tr>";
			$VerFam.= "</table>\n";
			$VerFam.= "</div>\n";
			
			$EditarFam.= "<div id=editarFamiliar$famFamiliarID style=\"display:none\">\n";
			$EditarFam.= "<table class='datauser'>";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Vinculo:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamVinculo$famFamiliarID name=selFamVinculo$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famVinculo == 'Conyuge' ? " selected" : "") . ">Conyuge</option>";
			$EditarFam.= "<option value=2" . ($famVinculo == 'Hijo' ? " selected" : "") . ">Hijo/a</option>";
			$EditarFam.= "<option value=3" . ($famVinculo == 'Padre' ? " selected" : "") . ">Padre/Madre</option>";
			$EditarFam.= "<option value=4" . ($famVinculo == 'Hermano' ? " selected" : "") . ">Hermano/a</option>";
			$EditarFam .= "<option value=5" . ($famVinculo == 'Otros' ? " selected" : "") . ">Otros</option>";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Apellido:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<input type=text name=famApellido$famFamiliarID id=famApellido$famFamiliarID value=\"$famApellido\" size=40>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Nombres:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<input type=text name=famNombres$famFamiliarID id=famNombres$famFamiliarID value=\"$famNombres\" size=40>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Sexo:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamSexo$famFamiliarID name=selFamSexo$famFamiliarID>\n";
			$EditarFam.= "<option value=M" . ($famSexo == 'Masculino' ? " selected" : "") . ">Masculino</option>\n";
			$EditarFam.= "<option value=F" . ($famSexo == 'Femenino' ? " selected" : "") . ">Femenino</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Tipo Documento:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamTipoDoc$famFamiliarID name=selFamTipoDoc$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famTipoDoc == 'DNI' ? " selected" : "") . ">DNI</option>\n";
			$EditarFam.= "<option value=2" . ($famTipoDoc == 'CI' ? " selected" : "") . ">CI</option>\n";
			$EditarFam.= "<option value=3" . ($famTipoDoc == 'PASAPORTE' ? " selected" : "") . ">PASAPORTE</option>\n";
			$EditarFam.= "<option value=4" . ($famTipoDoc == 'LE' ? " selected" : "") . ">LE</option>\n";
			$EditarFam.= "<option value=5" . ($famTipoDoc == 'LC' ? " selected" : "") . ">LC</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Numero Documento:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<input type=text name=famNumDoc$famFamiliarID id=famNumDoc$famFamiliarID value=\"$famNumDoc\" size=15>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Fecha Nacimiento:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<input type=text name=famFechaNac$famFamiliarID id=famFechaNac$famFamiliarID value=\"$famFechaNac\" size=11 onfocus=\"showCalendarControl(this);\" readonly>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Trabaja?:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamTrabaja$famFamiliarID name=selFamTrabaja$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famTrabaja == 'Si' ? " selected" : "") . ">Si</option>\n";
			$EditarFam.= "<option value=2" . ($famTrabaja == 'No' ? " selected" : "") . ">No</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Certificado De Estudios?:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamCertEst$famFamiliarID name=selFamCertEst$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famCertificadoEst == 'Si' ? " selected" : "") . ">Si</option>\n";
			$EditarFam.= "<option value=2" . ($famCertificadoEst == 'No' ? " selected" : "") . ">No</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Posee Asignacion?:</td>";
			$EditarFam.= "<td class=derecho><select id=selFamPoseeAsignacion$famFamiliarID name=selFamPoseeAsignacion$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famPoseeAsignacion == 'Si' ? " selected" : "") . ">Si</option>\n";
			$EditarFam.= "<option value=2" . ($famPoseeAsignacion == 'No' ? " selected" : "") . ">No</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
	//		$EditarFam .= "<TR><TD class='izquierdo'>Tipo Asignacion:</td><TD class=derecho>$famTipoAsigna</TD></TR>\n";
	//		$EditarFam .= "<TR><TD class='izquierdo'>Fecha Vencimiento Asignacion:</td><TD class=derecho>$famFechaVecAsig </TD></TR>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Discapacitado?:</td>";
			$EditarFam.= "<td class=derecho><select id=selFamDiscapacitado$famFamiliarID name=selFamDiscapacitado$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famDiscapacitado == 'Si' ? " selected" : "") . ">Si</option>\n";
			$EditarFam.= "<option value=2" . ($famDiscapacitado == 'No' ? " selected" : "") . ">No</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Familiar A Cargo?:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<select id=selFamACargo$famFamiliarID name=selFamACargo$famFamiliarID>\n";
			$EditarFam.= "<option value=1" . ($famACargo == 'Si' ? " selected" : "") . ">Si</option>\n";
			$EditarFam.= "<option value=2" . ($famACargo == 'No' ? " selected" : "") . ">No</option>\n";
			$EditarFam.= "</select>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'>Observaciones:</td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<textarea id=famObservaciones$famFamiliarID name=famObservaciones$famFamiliarID value=\"$famObservacion\">\n";
			$EditarFam.= "</textarea>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>\n";
			$EditarFam.= "<tr>";
			$EditarFam.= "<td class='izquierdo'></td>";
			$EditarFam.= "<td class=derecho>";
			$EditarFam.= "<a class=tecla href=\"javascript:EditarFamiliar($famFamiliarID);void(0);\">";
			$EditarFam.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios </a>&nbsp;&nbsp;&nbsp;";
			$EditarFam.= "<a class=tecla href=\"javascript:VolverFamiliar('editarFamiliar$famFamiliarID');void(0);\">";
			$EditarFam.= "<img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
			$EditarFam.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver </a>";
			$EditarFam.= "</td>";
			$EditarFam.= "</tr>";
			$EditarFam.= "</table>\n";
			$EditarFam.= "</div>\n";
		}
		print "</table>\n";
		
	}
	else
	{
		Alerta('Este empleado no tiene familiares registrados');
	}
	
	$AgregarFam = "<div id=agregarFamiliar style=\"display:none\">\n";
	$AgregarFam.= "<table class='datauser'>";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Vinculo:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamVinculo name=selFamVinculo>\n";
	$AgregarFam.= "<option value=1>Conyuge</option>";
	$AgregarFam.= "<option value=2>Hijo/a</option>";
	$AgregarFam.= "<option value=3>Padre/Madre</option>";
	$AgregarFam.= "<option value=4>Hermano/a</option>";
	$AgregarFam.= "<option value=5>Otros</option>";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Apellido:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<input type=text name=famApellido id=famApellido size=40>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Nombres:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<input type=text name=famNombres id=famNombres size=40>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Sexo:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamSexo name=selFamSexo>\n";
	$AgregarFam.= "<option value=M>Masculino</option>\n";
	$AgregarFam.= "<option value=F>Femenino</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Tipo Documento:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamTipoDoc name=selFamTipoDoc>\n";
	$AgregarFam.= "<option value=1>DNI</option>\n";
	$AgregarFam.= "<option value=2>CI</option>\n";
	$AgregarFam.= "<option value=3>PASAPORTE</option>\n";
	$AgregarFam.= "<option value=4>LE</option>\n";
	$AgregarFam.= "<option value=5>LC</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Numero Documento:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<input type=text name=famNumDoc id=famNumDoc size=15>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Fecha Nacimiento:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<input type=text name=famFechaNac id=famFechaNac onfocus=\"showCalendarControl(this);\" readonly size=11>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Trabaja?:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamTrabaja name=selFamTrabaja>\n";
	$AgregarFam.= "<option value=1>Si</option>\n";
	$AgregarFam.= "<option value=2>No</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Certificado De Estudios?:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamCertEst name=selFamCertEst>\n";
	$AgregarFam.= "<option value=1>Si</option>\n";
	$AgregarFam.= "<option value=2>No</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Posee Asignacion?:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamPoseeAsignacion name=selFamPoseeAsignacion>\n";
	$AgregarFam.= "<option value=1>Si</option>\n";
	$AgregarFam.= "<option value=2>No</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	//$AgregarFam .= "<tr> <TD class='izquierdo'>Tipo Asignacion:</td><TD class=derecho></TD></tr>\n";
	//$AgregarFam .= "<tr> <TD class='izquierdo'>Fecha Vencimiento Asignacion:</td><TD class=derecho></TD></tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Discapacitado?:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamDiscapacitado name=selFamDiscapacitado>\n";
	$AgregarFam.= "<option value=1>Si</option>\n";
	$AgregarFam.= "<option value=2>No</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Familiar A Cargo?:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<select id=selFamACargo name=selFamACargo>\n";
	$AgregarFam.= "<option value=1>Si</option>\n";
	$AgregarFam.= "<option value=2>No</option>\n";
	$AgregarFam.= "</select>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>Observaciones:</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<textarea id=famObservaciones name=famObservaciones>\n";
	$AgregarFam.= "</textarea>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>\n";
	$AgregarFam.= "<tr>";
	$AgregarFam.= "<td class='izquierdo'>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "<td class=derecho>";
	$AgregarFam.= "<a class=tecla href=\"javascript:AgregarFamiliar();void(0);\">";
	$AgregarFam.= "<img src=\"images/icon24_grabar.gif\" alt=\"Guardar Cambios\" align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Guardar Cambios </a>&nbsp;&nbsp;&nbsp;";		
	$AgregarFam.= "<a class=tecla href=\"javascript:VolverFamiliar('agregarFamiliar');void(0);\">";
	$AgregarFam.= " <img src=\"images/icon24_prev.gif\" alt=\"Volver\" ";
	$AgregarFam.= "align=\"absmiddle\" width=\"24\" height=\"24\" border=\"0\"> Volver </a>";
	$AgregarFam.= "</td>";
	$AgregarFam.= "</tr>";
	$AgregarFam.= "</table>";
	$AgregarFam.= "</div>\n";
?>
</div>
<!-- DIVS de ver familiares --> <?=$VerFam?>
<!-- DIVS de editar familiares --> <?=$EditarFam?>
<!-- DIVS de agregar familiar --> <?=$AgregarFam?>
</div>
<!-- FIN SECCION DE FAMILIARES -->
</div>
	<br>
	<input type=button id=aceptarCambios value="Aceptar Cambios"
		onclick="AceptarCambios(1);">
	<input type=button id=cancelarCambios value="Cancelar Cambios"
		onclick="AceptarCambios(0);">
</div>
<script>
	document.getElementById('dvLoading').style.display = 'none';
	document.getElementById('dvMenu').style.display = 'block';
</script>
<?	
}

if ($accion == 'Cancelar' || $accion == 'Buscar' || $accion == '')
{
	if ($accion == 'Cancelar')
	{
		$ID = LimpiarVariable($_POST["ID"]);
		if ($_SESSION["CancelarCambios"] != '')
		{
			$sqls = explode(';', $_SESSION["CancelarCambios"]);
			for($i=0;$i<count($sqls);$i++)
			{
				//print "Undo $i:$sqls[$i]<br>";
				if ($sqls[$i] != '')
				{
					pg_exec($db, $sqls[$i]);
				}
			}
		}
		$_SESSION["CancelarCambios"] = '';
	}
	
	$CantEmpleados = LimpiarNumero($_POST["cantidad"]);
	if ($CantEmpleados == '' || $CantEmpleados == 0)
	{
		//Calcula la cantidad de empleados
		
		$sql = "SELECT count(1) AS \"Cantidad\""; 
		$sql.= " FROM \"tblEmpleados\" em";
		$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
		$sql.= " AND em.\"FechaEgreso\" IS NULL";
		
		$rs = pg_query($db, $sql);
		if (!$rs)
		{
			pg_close($db);
		}
		$row = pg_fetch_array($rs);
		$CantEmpleados = $row[0];
	}
	print "<input type=hidden id=cantidad name=cantidad value=\"$CantEmpleados\">\n";

	$TotalPaginas = intval($CantEmpleados/10);
	if ($TotalPaginas != $CantEmpleados/10)
		$TotalPaginas++;
		
	$Pagina = LimpiarNumero($_POST["pagina"]);
	if ($Pagina == '' || $Pagina <= 0 || $Pagina > $TotalPaginas)
		$Pagina = 1;
		
	print "<input type=hidden id=pagina name=pagina value=\"$Pagina\">\n";
	$OffSet = ($Pagina-1)*10;
	$Orden = LimpiarNumero($_POST["Orden"]);

	if ($Orden == '' || $Orden == 2)
	{
		$Order = ' ORDER BY 3 ';
		$nOrd = 3;
	}
	else if ($Orden == 1)
	{
		$Order = ' ORDER BY 2 ';
		$nOrd = 3;
	}
	else if ($Orden == 3)
	{
		$Order = ' ORDER BY 4, 3 ';
		$nOrd = 3;
	}
	else if ($Orden == 4)
	{
		$Order = ' ORDER BY 2 DESC ';
		$nOrd = 0;
	}
	else if ($Orden == 5)
	{
		$Order = ' ORDER BY 3 DESC ';
		$nOrd = 0;
	}
	else if ($Orden == 6)
	{
		$Order = ' ORDER BY 4 DESC, 3 ';
		$nOrd = 0;
	}

	if ($accion == 'Buscar')
	{
		if ($bBusquedaError)
		{
			Alerta('La busqueda no dio ningun resultado');
			
			$sql = "SELECT em.\"SucursalID\","; 
			$sql.= " $sqlLegajo,"; 
			$sql.= " em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\","; 
			$sql.= " em.\"TipoRelacion\""; 
			$sql.= " FROM \"tblEmpleados\" em"; 
			$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
			$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
			$sql.= " AND em.\"FechaEgreso\" IS NULL";
			$sql.= " $Order";
			$sql.= " LIMIT 10 OFFSET $OffSet";
			
			$rs = pg_query($db, $sql);
		}
		else
		{
			if ($busqLegajo != '')
			{
				$sql = "SELECT em.\"SucursalID\","; 
				$sql.= " $sqlLegajo,"; 
				$sql.= " em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\","; 
				$sql.= " em.\"TipoRelacion\""; 
				$sql.= " FROM \"tblEmpleados\" em"; 
				$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
				$sql.= " AND em.\"SucursalID\" = $SucursalID";
				$sql.= " AND em.\"FechaEgreso\" IS NULL";
				$sql.= " AND em.\"Legajo\" = '$busqLegajo'";
				$sql.= " $Order";
			
				$rs = pg_query($db, $sql);
			}
			else
			{
				$sql = "SELECT em.\"SucursalID\","; 
				$sql.= " $sqlLegajo,"; 
				$sql.= " em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\","; 
				$sql.= " em.\"TipoRelacion\""; 
				$sql.= " FROM \"tblEmpleados\" em"; 
				$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
				$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
				$sql.= " AND em.\"FechaEgreso\" IS NULL";
				$sql.= " AND lower(em.\"Nombre\") LIKE '%$busqNombre%'"; 
				$sql.= " AND lower(em.\"Apellido\") like '%$busqApellido%'";
				$sql.= " $Order";
				$sql.= " LIMIT 10";
			
				$rs = pg_query($db, $sql);
			}
			
			$iCant = pg_numrows($rs);
			Alerta("Se encontraron $iCant coincidencias");
		}
	}
	else
	{
		$sql = "SELECT em.\"SucursalID\","; 
		$sql.= " $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\",";
		$sql.= " em.\"TipoRelacion\""; 
		$sql.= " FROM \"tblEmpleados\" em"; 
		$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
		$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
		$sql.= " AND em.\"FechaEgreso\" IS NULL";
		$sql.= " $Order";
		$sql.= " LIMIT 10 OFFSET $OffSet";
	
		$rs = pg_query($db, $sql);
	}
	if (!$rs)
	{
		exit;
	}
	?>
	<input type=hidden id=ID name=ID>
	<input type=hidden id=FechaEgr name=FechaEgr>
    <input type=hidden id=BajaDecreto name=BajaDecreto>
    <input type=hidden id=BajaMotivo name=BajaMotivo>
	<input type=hidden id=Orden name=Orden value="<?=$Orden?>">
	<div id=listaEmpleados style="display:block">
	
	<input type=hidden name=accion id=accion>
	<a href="javascript:Accion(1,'0');void(0);" class="tecla"> <img src="images/icon24_add.gif" alt="Agregar Empleado" width="24" height="23" border="0" 
		align="absmiddle">  Agregar Empleado </a><br />
	<br />
	<script>
	function Busqueda()
	{
		var oBusq = document.getElementById('dvBusqueda');
		if (oBusq.style.display == 'none')
			oBusq.style.display = 'block';
		else
			oBusq.style.display = 'none';
	}
	
	function Buscar()
	{
		if (document.getElementById('busqLegajo').value.length < 1 && 
			document.getElementById('busqNombre').value.length < 1 &&
			document.getElementById('busqApellido').value.length < 1)
		{
			alert('Debe completar 1 de los 3 criterios de busqueda');
			return false;
		}
		document.getElementById('accion').value = 'Buscar';
		document.frmEmpleados.submit();
	}

	function Resetear()
	{
		document.getElementById('accion').value = '';
		document.frmEmpleados.submit();
	}
	</script>
	<font size=3><b>Busqueda </b></font><input type=button id=busqVer value="  \/  " onclick="javascript:Busqueda();">
	<div id=dvBusqueda style="display:<? print ($accion == 'Buscar' ? "block" : "none"); ?>">
	<br><br><b>Llene 1 de los 3 criterios de busqueda y presione el boton buscar.</b><br><br>
	<table border="0" cellpadding="5" cellspacing="1" class="datauser">
    	<tr align="left">
			<td class=izquierdo>Legajo:</td>
			<td class=derecho>
				<input type=text id=busqLegajo name=busqLegajo size=10 value="<?=$busqLegajo?>">
			</td>
		</tr>
		<tr>
			<td class=izquierdo>Nombre:</td>
			<td class=derecho>
				<input type=text id=busqNombre name=busqNombre value="<?=$busqNombre?>">
			</td>
		</tr>
		<tr>
			<td class=izquierdo>Apellido:</td>
			<td class=derecho>
				<input type=text id=busqApellido name=busqApellido value="<?=$busqApellido?>">
			</td>
		</tr>
		<tr>
			<td class=izquierdo></td>
			<td class=derecho>
				<input type=button id=busqBoton value="Buscar" onclick="javascript:Buscar();">
				<input type=button id=busqReset value="Resetear Busqueda" onclick="javascript:Resetear();">
			</td>
		</tr>
	</table>
	</div>
	<br><br>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr align="center">
			<th nowrap><a href=# onclick="document.getElementById('Orden').value = '<?=$nOrd+1?>'; document.frmEmpleados.submit();">Legajo</a>
<? if ($nOrd == 0){?>
<img src="images/icon16_arrow_down.gif" align="absmiddle" border=0 width=16 height=16>
<?}else{?>
<img src="images/icon16_arrow_up.gif" align="absmiddle" border=0 width=16 height=16>
<?}?></th>
			<th><a href=# onclick="document.getElementById('Orden').value = '<?=$nOrd+2?>'; document.frmEmpleados.submit();">Apellido y Nombre</a>
<? if ($nOrd == 0){?>
<img src="images/icon16_arrow_down.gif" align="absmiddle" border=0 width=16 height=16>
<?}else{?>
<img src="images/icon16_arrow_up.gif" align="absmiddle" border=0 width=16 height=16>
<?}?></th>
			<th><a href=# onclick="document.getElementById('Orden').value = '<?=$nOrd+3?>'; document.frmEmpleados.submit();">Tipo De Relacion</a>
<? if ($nOrd == 0){?>
<img src="images/icon16_arrow_down.gif" align="absmiddle" border=0 width=16 height=16>
<?}else{?>
<img src="images/icon16_arrow_up.gif" align="absmiddle" border=0 width=16 height=16>
<?}?></th>
			<th width="24">Editar</th>
			<th width="24">Borrar</th>
		</tr>
	<?
	while($row = pg_fetch_array($rs))
	{
		$SucursalID = $row[0];
		$Legajo 	= $row[1];
		$ApeyNom 	= $row[2];
		$TipoRel 	= $row[3];
		
		if ($TipoRel == 1)
			$TipoRelacion = 'Mensualizado';
		else if ($TipoRel == 2)
			$TipoRelacion = 'Jornalizado';
		else if ($TipoRel == 3)
			$TipoRelacion = 'Contratado';
		else if ($TipoRel == 4)
			$TipoRelacion = 'Locacion De Obra';						
	?>
		<tr bgcolor="#FFFFFF" align="center">
			<td><?=$Legajo?></td>
			<td align="left"><?=$ApeyNom?></td>
			<td><?=$TipoRelacion?></td>
			<td>
				<a href="javascript:Accion(2,'<?=$Legajo?>');void(0);"><img src="images/icon24_editar.gif" 
				alt="Editar Empleado" align="absmiddle" border="0" width="24" height="24"></a>
			</td>
			<td>
				<a href="javascript:Accion(3,'<?=$Legajo?>');void(0);"><img src="images/icon24_borrar.gif" 
				alt="Borrar Empleado" align="absmiddle" border="0" width="24" height="24"></a>
			</td>
		</tr>
	<?
	}
	
	print "</table><br>\n";
	if ($accion != 'Buscar')
	{
		print "<div align=center>\n";
		if ($Pagina-4 < 1)
		{
			$iIni = 1;
			$iFin = 9;
			$AntPag = $Pagina - 1;
			$SigPag = $Pagina + 1;
			
			if ($Pagina > 1)
			{
				$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit();void(0);\">|< Primero</a> &nbsp;&nbsp;";
				$ant.= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit();void(0);\"><< Anterior</a> &nbsp;&nbsp;";
			}
			else
			{
				$ant = "|< Primero &nbsp;&nbsp;";
				$ant.= "<< Anterior &nbsp;&nbsp;";
			}
			$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
			$sig.= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
		}
		
		else if ($Pagina+5 > $TotalPaginas)
		{
			$iIni 	= $TotalPaginas - 8;
			$iFin 	= $TotalPaginas;
			$AntPag = $Pagina - 1;
			$SigPag = $Pagina + 1;
			
			$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit();void(0);\">|< Primero &nbsp;&nbsp;</a> ";
			$ant.= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit();void(0);\"><< Anterior &nbsp;&nbsp;</a> ";
			
			if ($SigPag > $TotalPaginas)
			{
				$sig = " &nbsp;&nbsp; Siguiente >>";
				$sig .= " &nbsp;&nbsp; Ultimo  >|";
			}
			else
			{
				$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
				$sig .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
			}
		}
		
		else
		{
			$iIni 	= $Pagina-4;
			$iFin 	= $Pagina+4;
			$AntPag = $Pagina - 1;
			$SigPag = $Pagina + 1;
			
			$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit(); void(0);\">|< Primero &nbsp;&nbsp;</a> ";
			$ant .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit(); void(0);\"><< Anterior &nbsp;&nbsp;</a> ";
			$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit(); void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
			$sig .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit(); void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
		}
		print $ant;
		for($i=$iIni;$i<=$iFin;$i++)
		{
			if ($Pagina == $i)
			{
				print " $i ";
			}
			else
			{
				?><a href="javascript:document.frmEmpleados.pagina.value = <?=$i?>; document.frmEmpleados.submit(); void(0);"><?=$i?></a><?
			}
		}
		print $sig;
		?></div><?
	}
	?></div><?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
