<?
require_once('funcs.php');
EstaLogeado();
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Bajar Listado')
{
	$arch = LimpiarVariable($_POST["listado"]);
	EnviarArchivo('../listados/', $arch);
	exit;
}

include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

if ($_SESSION["LegajoNumerico"] == '1')
{
	$sqlLegajo = "to_number(em.\"Legajo\", '999999') AS \"Legajo\"";
}
else
{
	$sqlLegajo = "em.\"Legajo\"";
}

?>

<script>
	function BajarListado(sArch)
	{
		document.getElementById('accion').value = 'Bajar Listado';
		document.getElementById('listado').value = sArch;
		document.frmCajasAhorro.submit();
	}
	
	function MM_openBrWindow(theURL,winName,features) 
	{ //v2.0
	  window.open(theURL,winName,features);
	}
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmCajasAhorro id=frmCajasAhorro action=listadoAltasCajasAhorro.php method=post>
<input type=hidden name=accion id=accion>
<input type=hidden name=listado id=listado>

<?
// Si la accion es parqa generar un disco de datos
if ($accion == 'Generar Disco')
{
	$Total = LimpiarNumero($_POST['Total']);
	if ($Total < 1 || $Total > 1000)
		$Total = 1000;
		
	$i = 1;
	$strLeg = '';
	
	do 
	{
		$Legajo = $_POST['chkLEG'.$i];
		if ($Legajo != '') 
		{
			if ($strLeg != '')
				$strLeg .= ',';
				
			if ($_SESSION["LegajoNumerico"] == '1')
			{
				$strLeg .= "'".LimpiarNumero($Legajo)."'";
			}
			else
			{
				$strLeg .= "'".LimpiarVariable($Legajo)."'";
			}
		}
		$i++;
		
	} while($i <= $Total);
	
	$Sucursal = LimpiarNumero($_POST['selSucDest']);

	if ($Sucursal == '' || $strLeg == '') 
	{
		exit;
	}

	$dAno = date("Y");
	$dMes = date("m");

	$sql = "SELECT ed.\"CUIT\",";
	$sql.= " em.\"Apellido\",";
	$sql.= " em.\"Nombre\",";
	$sql.= " ed.\"Sexo\",";
	$sql.= " ed.\"TipoDocumento\",";
	$sql.= " ed.\"NumeroDocumento\",";
	$sql.= " $sqlLegajo,"; 
	$sql.= " ed.\"FechaNacimiento\","; 
	$sql.= " ed.\"LocalidadNac\","; 
	$sql.= " eo.\"Calle\","; 
	$sql.= " eo.\"Numero\","; 
	$sql.= " eo.\"Piso\","; 
	$sql.= " eo.\"Departamento\",";
	$sql.= " eo.\"Localidad\","; 
	$sql.= " eo.\"CodigoPostal\","; 
	$sql.= " eo.\"Telefono\","; 
	$sql.= " ed.\"EstadoCivil\","; 
	
	$sql.= " (";
	$sql.= " SELECT ef.\"Apellido\" ||' '|| ef.\"Nombres\""; 
	$sql.= " FROM \"tblEmpleadosFamiliares\" ef"; 
	$sql.= " WHERE ef.\"EmpresaID\" = em.\"EmpresaID\""; 
	$sql.= " AND ef.\"SucursalID\" = em.\"SucursalID\""; 
	$sql.= " AND ef.\"Legajo\" = em.\"Legajo\""; 
	$sql.= " AND ef.\"TipoDeVinculo\" = 1"; 
	$sql.= " AND ef.\"FechaBaja\" IS NULL";
	$sql.= " ) as \"ApeConyuge\","; 
	
	$sql.= " (";
	$sql.= " SELECT ef.\"Apellido\" ||' '|| ef.\"Nombres\""; 
	$sql.= " FROM \"tblEmpleadosFamiliares\" ef"; 
	$sql.= " WHERE ef.\"EmpresaID\" = em.\"EmpresaID\"";
	$sql.= " AND ef.\"SucursalID\" = em.\"SucursalID\""; 
	$sql.= " AND ef.\"Legajo\" = em.\"Legajo\""; 
	$sql.= " AND ef.\"TipoDeVinculo\" = 3"; 
	$sql.= " AND ef.\"Sexo\" = 'M'"; 
	$sql.= " AND ef.\"FechaBaja\" IS NULL";
	$sql.= " ) as \"ApePadre\",";
	
	$sql.= " (";
	$sql.= " SELECT ef.\"Apellido\" ||' '|| ef.\"Nombres\""; 
	$sql.= " FROM \"tblEmpleadosFamiliares\" ef"; 
	$sql.= " WHERE ef.\"EmpresaID\" = em.\"EmpresaID\"";
	$sql.= " AND ef.\"SucursalID\" = em.\"SucursalID\""; 
	$sql.= " AND ef.\"Legajo\" = em.\"Legajo\""; 
	$sql.= " AND ef.\"TipoDeVinculo\" = 3"; 
	$sql.= " AND ef.\"Sexo\" = 'F'"; 
	$sql.= " AND ef.\"FechaBaja\" IS NULL";
	$sql.= " ) as \"ApeMadre\",";
	
	$sql.= " ed.\"FechaIngreso\","; 
	
	/*
	$sql.= " (";
	$sql.= " SELECT SUM(\"Haber1\")+SUM(\"Haber2\")-SUM(\"Descuento\")"; 
	$sql.= " FROM \"tblRecibos\" re";
	$sql.= " WHERE re.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND re.\"SucursalID\" = $SucursalID";
	$sql.= " AND extract('year' from re.\"Fecha\") = $dAno"; 
	$sql.= " AND extract('month' from re.\"Fecha\") = $dMes";
	$sql.= " AND re.\"ConceptoID\" = 99"; 
	$sql.= " AND re.\"Legajo\" = em.\"Legajo\"";
	$sql.= " ) as \"SueldoNeto\"";
	*/

	$sql.= " CASE WHEN em.\"TipoRelacion\" = 4 THEN em.\"SueldoBasico\" ELSE";
	$sql.= " (";
	$sql.= " SELECT SUM(\"Haber1\")+SUM(\"Haber2\")-SUM(\"Descuento\")"; 
	$sql.= " FROM \"tblRecibos\" re";
	$sql.= " WHERE re.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND re.\"SucursalID\" = $SucursalID";
	$sql.= " AND extract('year' from re.\"Fecha\") = $dAno"; 
	$sql.= " AND extract('month' from re.\"Fecha\") = $dMes";
	$sql.= " AND re.\"ConceptoID\" = 99"; 
	$sql.= " AND re.\"Legajo\" = em.\"Legajo\"";
	$sql.= " )"; 
	$sql.= " END-100000 AS \"SueldoNeto\"";
	
	$sql.= " FROM \"tblEmpleados\" em";
	
	$sql.= " INNER JOIN \"tblEmpleadosDatos\" ed";
	$sql.= " ON em.\"EmpresaID\" = ed.\"EmpresaID\"";
	$sql.= " AND em.\"SucursalID\" = ed.\"SucursalID\""; 
	$sql.= " AND em.\"Legajo\" = ed.\"Legajo\"";
	
	$sql.= " INNER JOIN \"tblEmpleadosDomicilio\" eo";
	$sql.= " ON em.\"EmpresaID\" = eo.\"EmpresaID\""; 
	$sql.= " AND em.\"SucursalID\" = eo.\"SucursalID\""; 
	$sql.= " AND em.\"Legajo\" = eo.\"Legajo\"";

	$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
	$sql.= " AND em.\"FechaEgreso\" IS NULL"; 
	$sql.= " AND ed.\"NumeroCuenta\" IS NULL"; 
	$sql.= " AND em.\"Legajo\" IN ($strLeg)";
	
	$sql.= " ORDER BY 4";
	
	$rs = pg_query($db, $sql);
	
	if (!$rs)
	{
		exit;
	}

	$CalleLab 	= 'RIVADAVIA      ';
	$NroLab 	= '00411';
	$PisoLab 	= '00';
	$OfLab 		= '      ';
	$LocLab 	= 'Capilla Se'.chr(164).'or';
	$CodPosLab 	= '2812';
	$CodProLab 	= '02';
	$TeLab 		= '0491359';

	$Fecha 	= date("ymd");
	$arch 	= "1246".date("md").".emp";
?>
<H1><img src="images/icon64_banco.gif" width="64" height="64" align="absmiddle" /> Altas de Cajas de Ahorro del Banco Provincia</H1>
	<a class="tecla" href="javascript:BajarListado('<?=$arch?>'); void(0);"> 
	<img src="images/icon24_bajarlistado.gif" alt="Bajar Listado" width="24" height="24" border="0" align="absmiddle"> 
	Bajar Listado</a>
<!--	<a class="tecla" href='#' onclick="MM_openBrWindow('listadoBancoPrint.php?FechaPeriodo=<?=$FechaPeriodo?>&NumeroLiquidacion=<?=$NumeroLiquidacion?>','printpreview','width=872,height=750')"> 
	<img src="images/icon24_printlistado.gif" alt="Imprimir Listado" width="24" height="24" border="0" align="absmiddle">
	Imprimir Listado</a>&nbsp;<BR />&nbsp;-->
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr><th>CUIL</th><th>NOV</th><th>Apellido y Nombre</th><th>Sexo</th><th>Documento</th><th>Legajo</th><th nowrap>Fecha Nac.</th><th>Direccion</th><th>Localidad</th><th>Cod Pos</th><th>Est Civ</th><th>IVA</th><th>Gan</th><th>Ing Bru</th></tr>
<?
	$Header  = 'HRNOVED-EMPLE';	// Identificacion
	$Header .= '1246';			// Codigo del ente
	$Header .= $Sucursal;		// Sucursal del banco
	$Header .= "$Fecha";		// Fecha del Proceso aammdd
	$Header .= str_repeat(' ', 303);
	$TotalReg = 0;
	$TotalPesos = 0;
	
	while($row = pg_fetch_array($rs))
	{
		$CUIL 		= str_pad(substr(LimpiarNumero($row[0]), 0, 11), 11, '0', STR_PAD_LEFT);
		$Apellido 	= str_pad(substr($row[1], 0, 25), 25, ' ', STR_PAD_RIGHT);
		$Nombre 	= str_pad(substr($row[2], 0, 20), 20, ' ', STR_PAD_RIGHT);
		$ApeYNom 	= $Apellido . $Nombre;
		$Sexo 		= $row[3];
		
		if ($row[4] == '1')
			$TipoDoc = '1';
		elseif ($row[4] == '4')
			$TipoDoc = '2';
		elseif ($row[4] == '5')
			$TipoDoc = '3';
		else
			$TipoDoc = ' ';
			
		$EstCiv = $row[16];
		if ($EstCiv == '3')
			$EstCiv = '5';

		$NumDoc 	= str_pad(LimpiarNumero($row[5]), 8, '0', STR_PAD_LEFT);
		$Legajo 	= str_pad($row[6], 10, '0', STR_PAD_LEFT);
		$FechaNac 	= LimpiarNumero(FechaSQL2WEB($row[7]));
		$LocNac 	= str_pad(substr($row[8], 0, 13), 13, ' ', STR_PAD_LEFT);
		$Calle 		= str_pad(substr($row[9], 0, 15), 15, ' ', STR_PAD_LEFT);
		$Numero 	= str_pad(substr(LimpiarNumero($row[10]), 0, 5), 5, '0', STR_PAD_LEFT);
		$Piso 		= str_pad(substr($row[11], 0, 2), 2, ' ', STR_PAD_LEFT);
		$Depart 	= str_pad(substr($row[12], 0, 2), 2, ' ', STR_PAD_LEFT);
		$Loc 		= str_pad(substr($row[13], 0, 13), 13, ' ', STR_PAD_LEFT);
		$CodPos 	= str_pad(substr($row[14], 0, 4), 4, '0', STR_PAD_LEFT);
		$Tel 		= str_pad(substr($row[15], 0, 7), 7, '0', STR_PAD_LEFT);
		$ApeCony 	= str_pad(substr($row[17], 0, 30), 30, ' ', STR_PAD_LEFT);
		$ApePadre 	= str_pad(substr($row[18], 0, 30), 30, ' ', STR_PAD_LEFT);
		$ApeMadre 	= str_pad(substr($row[19], 0, 30), 30, ' ', STR_PAD_LEFT);
		$FechaIng 	= $row[20];

		$TipoMov = '03';			// Tipo Movimiento 03-Alta 01-Baja

		$Detalle .= $CUIL. $TipoMov . $ApeYNom . $Sexo . $TipoDoc . $NumDoc;
		$Detalle .= $CalleLab . $NroLab . $PisoLab . $OfLab . $LocLab;
		$Detalle .= $CodPosLab . $CodProLab . $TeLab . $Legajo . $Sucursal;
		$Detalle .= $FechaNac;
		$Detalle .= $LocNac;
		$Detalle .= $Calle . $Numero . $Piso . $Depart . $Loc . $CodPos;
		$Detalle .= '02';			// Codigo de provincia
		$Detalle .= '000';
		$Detalle .= '0000000'; //$Tel; //por ahora se anula el teelfono ya q solo acepta numeros
		$Detalle .= $EstCiv;
		$Detalle .= '014';
		$Detalle .= $ApeCony . $ApePadre . $ApeMadre;
		$Detalle .= '5';		//IVA
		$Detalle .= '1';		//Ganancias
		$Detalle .= '1';		//Ing. Brutos
		$Detalle .= substr(LimpiarNumero($FechaIng), 2);
		
		/*$i = strpos($Neto, '.');
		if ($i === false)
		{
			$Neto = str_pad($row[21], 8, '0', STR_PAD_LEFT) . '00';
		}
		else
		{
			$decimal = substr($row[21] . '00', $i+1, 2);
			$Neto = substr($row[21], 0, $i);
			$Neto = str_pad($Neto, 8, '0', STR_PAD_LEFT) . $decimal;
		}*/
		$Neto = str_pad("1", 8, '0', STR_PAD_LEFT) . '00';
		
		$Detalle .= $Neto;
		$Detalle .= '0000';
		$Detalle .= '   ';
		$Detalle .= "\r\n";

		$TotalRegs++;
		$TotalPesos+=1;
?>
	<tr><td><?=$row[0]?></td><td>03</td><td><?=$row[1].' '.$row[2]?></td><td><?=$Sexo?></td><td><?=LimpiarNumero($row[5])?></td><td><?=$row[6]?></td><td><?=FechaSQL2WEB($row[7])?></td><td><?=$row[9]?></td><td><?=$row[13]?></td><td><?=$row[14]?></td><td><?=$EstCiv?></td><td>5</td><td>1</td><td>1</td></tr>
<?
	}
	
	$i = strpos($TotalPesos, '.');
	if ($i == false)
	{
		$TotalP = str_pad($TotalPesos, 12, '0', STR_PAD_LEFT) . '00';
	}
	else
	{
		$decimal 	= substr($TotalPesos . '00', $i+1, 2);
		$TotalP 	= substr($TotalPesos, 0, $i);
		$TotalP 	= str_pad($TotalP, 12, '0', STR_PAD_LEFT) . $decimal;
	}
	
	$Cierre .= 'TRNOVED-EMPLE';		// Identificacion del registro de cierre
	$Cierre .= str_pad($TotalRegs+2, 8, '0', STR_PAD_LEFT);	// Cantidad de registros procesados
	$Cierre .= $TotalP;										// Importe involucrado
	$Cierre .= str_repeat(' ', 295);
	$Cierre .= "\r\n";
	$Header .= "\r\n";
	print "</table>\n";
	$fp = fopen('../listados/'.$arch, 'wb');
	fputs($fp, $Header);
	fputs($fp, $Detalle);
	fputs($fp, $Cierre);
	fclose($fp);
} 

// Si la accion es para ver un listado
else if ($accion == 'Previsualizar Listado')
{
	$sql = "SELECT \"LugarPago\"";
	$sql.= " FROM \"tblLugaresDePago\""; 
	$sql.= " WHERE \"TipoPago\" = 1"; 
	$sql.= " AND \"Activo\" = true"; 
	$sql.= " AND \"EmpresaID\" = $EmpresaID";

	$rs = pg_query($db, $sql);
	
	if (!$rs)
	{
		exit;
	}
	
	$LP = '';
	while($row = pg_fetch_array($rs))
	{
		if ($_POST['chkLP'.$row[0]] == $row[0]) 
		{
			if ($LP != '')
				$LP .= ',';
			$LP .= $row[0];
		}
	}

	if ($LP == '')
	{
		exit;
	}
	
	$dAno = date("Y");
	$dMes = date("m");

	$sql = "SELECT ed.\"CUIT\",";
	$sql.= " em.\"Apellido\","; 
	$sql.= " em.\"Nombre\",";
	$sql.= " $sqlLegajo,"; 
	$sql.= " ed.\"FechaIngreso\","; 

	/*
	$sql.= " (";
	$sql.= " SELECT SUM(\"Haber1\")+SUM(\"Haber2\")-SUM(\"Descuento\")"; 
	$sql.= " FROM \"tblRecibos\" re";
	$sql.= " WHERE re.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND re.\"SucursalID\" = $SucursalID"; 
	$sql.= " AND extract('year' from re.\"Fecha\") = $dAno"; 
	$sql.= " AND extract('month' from re.\"Fecha\") = $dMes";
	$sql.= " AND re.\"ConceptoID\" = 99 and re.\"Legajo\" = em.\"Legajo\"";
	$sql.= " ) as \"SueldoNeto\"";
	*/

	$sql.= " CASE WHEN em.\"TipoRelacion\" = 4 THEN em.\"SueldoBasico\" ELSE";
	$sql.= " (";
	$sql.= " SELECT SUM(\"Haber1\")+SUM(\"Haber2\")-SUM(\"Descuento\")"; 
	$sql.= " FROM \"tblRecibos\" re";
	$sql.= " WHERE re.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND re.\"SucursalID\" = $SucursalID";
	//$sql.= " AND extract('year' from re.\"Fecha\") = $dAno"; 
	//$sql.= " AND extract('month' from re.\"Fecha\") = $dMes";
	$sql.= " AND re.\"ConceptoID\" = 99"; 
	$sql.= " AND re.\"Legajo\" = em.\"Legajo\"";
	$sql.= " )"; 
	$sql.= " END-100000 AS \"SueldoNeto\"";

	$sql.= " FROM \"tblEmpleados\" em";
	
	$sql.= " INNER JOIN \"tblEmpleadosDatos\" ed";
	$sql.= " ON em.\"EmpresaID\" = ed.\"EmpresaID\""; 
	$sql.= " AND em.\"SucursalID\" = ed.\"SucursalID\""; 
	$sql.= " AND em.\"Legajo\" = ed.\"Legajo\""; 
	//$sql.= " AND ed.\"LugarPago\" in ($LP)";
	
	$sql.= " INNER JOIN \"tblEmpleadosDomicilio\" eo";
	$sql.= " ON em.\"EmpresaID\" = eo.\"EmpresaID\""; 
	$sql.= " AND em.\"SucursalID\" = eo.\"SucursalID\""; 
	$sql.= " AND em.\"Legajo\" = eo.\"Legajo\"";

	$sql.= " WHERE em.\"EmpresaID\" = $EmpresaID"; 
	$sql.= " AND em.\"SucursalID\" = $SucursalID"; 
	$sql.= " AND em.\"FechaEgreso\" IS NULL"; 
	$sql.= " AND ed.\"FechaNacimiento\" IS NOT NULL"; 
	$sql.= " AND ed.\"NumeroCuenta\" IS NULL";
	
	$sql.= " ORDER BY 4";

	$rs = pg_query($db, $sql);

?>
	<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
		<tr><th><input type=checkbox onclick="javascript:selTodos(true);" /></th><th>Legajo</th><th>Apellido y Nombre</th><th>CUIT</th><th>Fecha Ingreso</th><th>Sueldo Neto</th></tr>
<?
	$i = 1;
	while($row = pg_fetch_array($rs))
	{
		$Legajo 	= $row[3];
		$FechaIng 	= $row[4];
		$Neto 		= $row[5];
		if ($Neto <= 0) $Neto = 1;
		$CUIT 		= $row[0];
		$ApeYNom 	= $row[2]. ' ' .$row[1];
?>
		<tr>
		<td>
		<input type=checkbox name="chkLEG<?=$i?>" id="chkLEG<?=$i?>" value="<?=$Legajo?>" />
		</td>
		<td><?=$Legajo?></td><td><?=$ApeYNom?></td><td><?=$CUIT?></td><td><?=FechaSQL2WEB($FechaIng)?></td><td><?=$Neto?></td></tr>
<?
		$i++;
	}
?>
	</table>
	<br />
	<input type=hidden name=selSucDest id=selSucDest value="<?=$_POST['selSucDest']?>" />
	<input type=hidden name=Total id=Total value="<?=$i-1?>" />
	<input type=button value="Seleccionar todos" onclick="javascript:selTodos(true);">
	<input type=button value="Invertir seleccion" onclick="javascript:invertirTodos();">
	<input type=button value="Generar Disco" onclick="javascript:checkearTodos();">
<?
}

// Si todavia no se eligio la accion
if ($accion == '')
{
	$sql = "SELECT \"LugarPago\",";
	$sql.= " \"Descripcion\""; 	
	$sql.= " FROM \"tblLugaresDePago\""; 
	$sql.= " WHERE \"TipoPago\" = 1"; 
	$sql.= " AND \"Activo\" = TRUE"; 
	$sql.= " AND \"EmpresaID\" = $EmpresaID";

	$rs = pg_query($db, $sql);
	if (!$rs)
	{
		exit;
	}
?>
	<table class="datauser" align="left">
	<tr>
		<td>Seleccione Lugares de Pago:</td>
	</tr>
	<tr>
		<td><br />
<?
	while($row = pg_fetch_array($rs))
	{
?>
	<input type=checkbox name="chkLP<?=$row[0]?>" id="chkLP<?=$row[0]?>" value="<?=$row[0]?>" /><?=$row[1]?><br />
<?
	}
?><br />
	<tr>
		<td><br />Sucursal de destino: <select name=selSucDest id=selSucDest>
<?
	$rs = pg_query($db, "select distinct \"SucursalBanco\" from \"tblLugaresDePago\" 
	where \"TipoPago\"=2 and \"Activo\"=true and \"EmpresaID\" = $EmpresaID");
	if (!$rs)
	{
		exit;
	}
	while($row = pg_fetch_array($rs))
	{
?>
	<option value="<?=$row[0]?>"><?=$row[0]?></option>
<?
	}
?>		</select>
		<br /><br /></td>
	</tr>
	<tr>
		<td><input type=submit id=accion name=accion value="Previsualizar Listado"></td>
	</tr>
	</table>
<?
}
pg_close($db);
?>
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';
	function selTodos(bValor) 
	{
		var i, Chk;
		i = 1;
		do
		{
			Chk = document.getElementById('chkLEG'+i);
			if (Chk != null)
				Chk.checked = bValor;
			i++;
		} while(Chk != null)
	}
	
	function invertirTodos() 
	{
		var i, Chk;
		i = 1;
		do
		{
			Chk = document.getElementById('chkLEG'+i);
			if (Chk != null)
				Chk.checked = !Chk.checked;
			i++;
		} while(Chk != null)
	}
	
	function checkearTodos() 
	{
		var i, Chk;
		i = 1;
		do
		{
			Chk = document.getElementById('chkLEG'+i);
			if (Chk != null && Chk.checked) 
			{
				document.getElementById('accion').value = 'Generar Disco';
				document.getElementById('frmCajasAhorro').submit();
				return;
			}
			i++;
		} while(Chk != null)
		alert('Debe seleccionar al menos un empleado');
	}
</script>
<? include("footer.php"); ?>
