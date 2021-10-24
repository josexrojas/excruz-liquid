<? include("header.php");

if (!($db = Conectar()))
	exit;

$SEGURIDAD_MODULO_ID = 1;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

if ($_SESSION["LegajoNumerico"] == '1'){
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}else{
	$sqlLegajo = "em.\"Legajo\"";
}

$accion = LimpiarVariable($_POST["accion"]);
$accion2 = LimpiarVariable($_POST["accion2"]);

include 'seguridad.php';

$bBusquedaError = false;
if ($accion == 'Buscar'){
	$busqLegajo = LimpiarVariable($_POST["busqLegajo"]);
	$busqNombre = strtolower(LimpiarVariable($_POST["busqNombre"]));
	$busqApellido = strtolower(LimpiarVariable($_POST["busqApellido"]));
	if ($busqLegajo != ''){
		// Busqueda por legajo
		$rs = pg_query("
SELECT em.\"Legajo\" FROM \"tblEmpleados\" em
WHERE em.\"Legajo\" = '$busqLegajo' AND \"FechaEgreso\" IS NOT NULL");
		if (!$rs)
			exit;
		$row = pg_fetch_array($rs);
		if ($row[0] == $busqLegajo){
			// Existe
			$busqNombre = '';
			$busqApellido = '';
		}else{
			// No Existe
			$bBusquedaError = true;
		}
	}else{
		// Busqueda por nombre o apellido
		$rs = pg_query("
SELECT em.\"Legajo\" FROM \"tblEmpleados\" em
WHERE lower(em.\"Nombre\") like '%$busqNombre%' AND lower(em.\"Apellido\") like '%$busqApellido%'
AND \"FechaEgreso\" IS NOT NULL LIMIT 10");
		if (!$rs)
			exit;
		$iCant = 0;
		while($row = pg_fetch_array($rs)){
			$busqLegajo = $row[0];
			$iCant++;
		}
		if ($iCant >= 1){
			$busqLegajo = '';
		}else if ($iCant <= 0){
			// No Existe
			$bBusquedaError = true;
		}
	}
}
?>
<H1><img src="images/icon64_empleados.gif" width="64" height="64" align="absmiddle" /> Recuperar Empleados</H1>

<script language="JavaScript">
	function RecuperarEmpleado(ID)
	{
		if (!ChequearSeguridad(6))
			return false;
		if (!confirm("\u00bfEsta seguro que quiere recuperar este empleado?"))
			return false;
		document.getElementById('accion').value = 'Recuperar Empleado';
		document.getElementById("ID").value = ID;
		document.frmEmpleados.submit();
	}
</script>

<form name=frmEmpleados action=emplRecuperar.php method=post>
<?
$iError = 0;

if ($accion == 'Recuperar Empleado')
{
	if (!ChequearSeguridad(6))
		exit;
	$ID = LimpiarVariable($_POST["ID"]);
	if (!pg_exec($db, "
UPDATE \"tblEmpleados\" SET \"FechaEgreso\" = null
WHERE \"Legajo\" = '$ID'")){
		Alerta('Se produjo un error interno al recuperar el empleado');
	}else{
		Alerta('El empleado se recupero con exito');
	}
	$accion = '';
}

if ($accion == '' || $accion == 'Buscar'){
	$CantEmpleados = LimpiarNumero($_POST["cantidad"]);
	if ($CantEmpleados == '' || $CantEmpleados == 0){
		//Calcula la cantidad de empleados
		$rs = pg_query($db, "
SELECT count(1) AS \"Cantidad\" FROM \"tblEmpleados\" em
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NOT NULL");
		if (!$rs){
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

	if ($Orden == '' || $Orden == 2){
		$Order = ' ORDER BY 3 ';
		$nOrd = 3;
	}else if ($Orden == 1){
		$Order = ' ORDER BY 2 ';
		$nOrd = 3;
	}else if ($Orden == 3){
		$Order = ' ORDER BY 4, 3 ';
		$nOrd = 3;
	}else if ($Orden == 4){
		$Order = ' ORDER BY 2 DESC ';
		$nOrd = 0;
	}else if ($Orden == 5){
		$Order = ' ORDER BY 3 DESC ';
		$nOrd = 0;
	}else if ($Orden == 6){
		$Order = ' ORDER BY 4 DESC, 3 ';
		$nOrd = 0;
	}

	if ($accion == 'Buscar'){
		if ($bBusquedaError){
			Alerta('La busqueda no dio ningun resultado');
			$rs = pg_query($db, "
SELECT em.\"SucursalID\", $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\", em.\"TipoRelacion\" 
FROM \"tblEmpleados\" em 
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NOT NULL
$Order
LIMIT 10 OFFSET $OffSet");
		}else{
			if ($busqLegajo == '')
				$rs = pg_query($db, "
SELECT em.\"SucursalID\", $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\", em.\"TipoRelacion\" 
FROM \"tblEmpleados\" em 
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NOT NULL
AND lower(em.\"Nombre\") like '%$busqNombre%' AND lower(em.\"Apellido\") like '%$busqApellido%'
$Order
LIMIT 10");
			else
				$rs = pg_query($db, "
SELECT em.\"SucursalID\", $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\", em.\"TipoRelacion\" 
FROM \"tblEmpleados\" em 
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NOT NULL
AND em.\"Legajo\" = '$busqLegajo'
$Order
LIMIT 10");
			$iCant = pg_numrows($rs);
			Alerta("Se encontraron $iCant coincidencias");
		}
	}else{
		$rs = pg_query($db, "
SELECT em.\"SucursalID\", $sqlLegajo, em.\"Apellido\" || ', ' || em.\"Nombre\" AS \"ApeYNom\", em.\"TipoRelacion\" 
FROM \"tblEmpleados\" em 
WHERE em.\"EmpresaID\" = $EmpresaID AND em.\"SucursalID\" = $SucursalID AND em.\"FechaEgreso\" IS NOT NULL
$Order
LIMIT 10 OFFSET $OffSet");
	}
	if (!$rs)
		exit;
	?>

	<input type=hidden id=ID name=ID>
	<input type=hidden id=Orden name=Orden value="<?=$Orden?>">
	<div id=listaEmpleados style="display:block">
	
	<input type=hidden name=accion id=accion>
	<br />
	<script>
		function Busqueda(){
			var oBusq = document.getElementById('dvBusqueda');
			if (oBusq.style.display == 'none')
				oBusq.style.display = 'block';
			else
				oBusq.style.display = 'none';
		}
		function Buscar(){
			if (document.getElementById('busqLegajo').value.length < 1 && 
				document.getElementById('busqNombre').value.length < 1 &&
				document.getElementById('busqApellido').value.length < 1){
				alert('Debe completar 1 de los 3 criterios de busqueda');
				return false;
			}
			document.getElementById('accion').value = 'Buscar';
			document.frmEmpleados.submit();
		}
		function Resetear(){
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
	<td class=derecho><input type=text id=busqLegajo name=busqLegajo size=10 value="<?=$busqLegajo?>"></td></tr>
	<tr><td class=izquierdo>Nombre:</td>
	<td class=derecho><input type=text id=busqNombre name=busqNombre value="<?=$busqNombre?>"></td></tr>
	<tr><td class=izquierdo>Apellido:</td>
	<td class=derecho><input type=text id=busqApellido name=busqApellido value="<?=$busqApellido?>"></td></tr>
	<tr><td class=izquierdo></td>
	<td class=derecho><input type=button id=busqBoton value="Buscar" onclick="javascript:Buscar();">
	<input type=button id=busqReset value="Resetear Busqueda" onclick="javascript:Resetear();"></td>
	</tr></table>
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
			<th width="24">Recuperar</th>
		</tr>
	<?
	while($row = pg_fetch_array($rs))
	{
		$SucursalID = $row[0];
		$Legajo = $row[1];
		$ApeyNom = $row[2];
		$TipoRel = $row[3];
		if ($TipoRel == 1)
			$TipoRelacion = 'Mensualizado';
		else if ($TipoRel == 2)
			$TipoRelacion = 'Jornalizado';
		else if ($TipoRel == 3)
			$TipoRelacion = 'Contratado';
	?>

		<tr bgcolor="#FFFFFF" align="center">
			<td><?=$Legajo?></td><td align="left"><?=$ApeyNom?></td><td><?=$TipoRelacion?></td><td>
			<a href="javascript:RecuperarEmpleado('<?=$Legajo?>');void(0);"><img src="images/icon24_resucitar.gif" 
				alt="Recuperar Empleado" align="absmiddle" border="0" width="24" height="24"></a></td>
		</tr>
	<?
	}
	print "</table><br><DIV ALIGN=center>\n";
	if ($Pagina-4 < 1){
		$iIni = 1;
		$iFin = 9;
		$AntPag = $Pagina - 1;
		$SigPag = $Pagina + 1;
		if ($Pagina > 1){
			$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit();void(0);\">|< Primero</a> &nbsp;&nbsp;";
			$ant .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit();void(0);\"><< Anterior</a> &nbsp;&nbsp;";
		}else{
			$ant = "|< Primero &nbsp;&nbsp;";
			$ant .= "<< Anterior &nbsp;&nbsp;";
		}
		$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
		$sig .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
	}else if ($Pagina+5 > $TotalPaginas){
		$iIni = $TotalPaginas - 8;
		$iFin = $TotalPaginas;
		$AntPag = $Pagina - 1;
		$SigPag = $Pagina + 1;
		$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit();void(0);\">|< Primero &nbsp;&nbsp;</a> ";
		$ant .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit();void(0);\"><< Anterior &nbsp;&nbsp;</a> ";
		if ($SigPag > $TotalPaginas){
			$sig = " &nbsp;&nbsp; Siguiente >>";
			$sig .= " &nbsp;&nbsp; Ultimo  >|";
		}else{
			$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
			$sig .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit();void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
		}
	}else{
		$iIni = $Pagina-4;
		$iFin = $Pagina+4;
		$AntPag = $Pagina - 1;
		$SigPag = $Pagina + 1;
		$ant = "<a href=\"javascript:document.frmEmpleados.pagina.value = 1; document.frmEmpleados.submit(); void(0);\">|< Primero &nbsp;&nbsp;</a> ";
		$ant .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $AntPag; document.frmEmpleados.submit(); void(0);\"><< Anterior &nbsp;&nbsp;</a> ";
		$sig = "<a href=\"javascript:document.frmEmpleados.pagina.value = $SigPag; document.frmEmpleados.submit(); void(0);\"> &nbsp;&nbsp; Siguiente >></a> ";
		$sig .= "<a href=\"javascript:document.frmEmpleados.pagina.value = $TotalPaginas; document.frmEmpleados.submit(); void(0);\"> &nbsp;&nbsp; Ultimo >|</a> ";
	}
	print $ant;
	for($i=$iIni;$i<=$iFin;$i++){
	if ($Pagina == $i){
		print " $i ";
	}else{
?>
	<a href="javascript:document.frmEmpleados.pagina.value = <?=$i?>; document.frmEmpleados.submit(); void(0);"><?=$i?></a> 
<?
	}
	}
	print $sig;
	?>
	</div>
	</div>
<?
}
pg_close($db);
?>
</form>
<? include("footer.php"); ?>
