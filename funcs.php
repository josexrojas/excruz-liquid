<?
function Servidor(){
	return 'liquid.excruz.gov';
}

function VersionReporte(){
	return '1,5,0,9';
}

function Conectar()
{
	$dbname = getenv("DB_NAME");
	if (isset($_SESSION['instancia']))
	{
		$dbname = "Sueldos".$_SESSION['instancia'];
		print "Instancia: ".$_SESSION['instancia']."<br>";
	}
	$db = pg_connect("dbname=$dbname user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
	if (!$db){
		return false;
	}
	return $db;
}

function ConectarSimulador()
{
	$db = pg_connect("dbname=Sueldos2 user=Sueldos");
	if (!$db){
		return false;
	}
	return $db;
}

function EstaLogeado()
{
	session_start();

	if ($_SESSION["logged"] != '1')
		header("Location: /login.php");
}

function ChequearUsuario($db, $usuario, $password)
{
	$rs = pg_query($db, "SELECT \"UsuarioID\", \"Admin\", \"EmpresaID\", \"SucursalID\" FROM \"tblUsuarios\" WHERE \"Nombre\" = '$usuario' AND \"Password\" = '$password' AND \"Activo\" = true");
	if (!$rs || pg_num_rows($rs) < 1)
	{
		pg_close($db);
		return false;
	}

	session_start();

	$row = pg_fetch_array($rs);
	$ID = $row[0];
	$bAdmin = $row[1];
	$EmpID = $row[2];
	$SucID = $row[3];

	$_SESSION["logged"] = '1';
	$_SESSION["ID"] = $ID;
	$_SESSION["Admin"] = $bAdmin;
	$_SESSION["EmpresaID"] = $EmpID;
	$_SESSION["SucursalID"] = $SucID;
	$_SESSION["LegajoNumerico"] = '1';

	$rs = pg_query($db, "
SELECT li.\"Liquida\", pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\", 1
FROM \"tblPeriodos\" pe
INNER JOIN \"tblTipoLiquidacion\" li
ON li.\"EmpresaID\" = pe.\"EmpresaID\" AND li.\"TipoLiquidacionID\" = pe.\"TipoLiquidacionID\"
WHERE pe.\"EmpresaID\" = $EmpID AND pe.\"SucursalID\" = $SucID AND pe.\"Estado\" in (1,3) and pe.\"Activa\" = true
UNION
SELECT li.\"Liquida\", pe.\"FechaPeriodo\", pe.\"NumeroLiquidacion\", 2
FROM \"tblPeriodos\" pe
INNER JOIN \"tblTipoLiquidacion\" li
ON li.\"EmpresaID\" = pe.\"EmpresaID\" AND li.\"TipoLiquidacionID\" = pe.\"TipoLiquidacionID\"
WHERE pe.\"EmpresaID\" = $EmpID AND pe.\"SucursalID\" = $SucID AND pe.\"Estado\" in (1,3) and pe.\"Activa\" = false
ORDER BY 4
");
	// Tomo la liquidacion activa o la primera que no este activada
	if ($rs) {
		$row = pg_fetch_array($rs);
		$_SESSION["Liquida"] = $row[0];
		$_SESSION["FechaPeriodo"] = $row[1];
		$_SESSION["NumeroLiquidacion"] = $row[2];
	}else{
		$_SESSION["Liquida"] = '';
		$_SESSION["FechaPeriodo"] = '';
		$_SESSION["NumeroLiquidacion"] = '';
	}
	return true;
}

function Limpiar($variable, $possiblesChars)
{
	$buf = "";
	for($i=0;$i<strlen($variable);$i++)
	{
		$c = substr($variable, $i, 1);
		if (strpos($possiblesChars, $c) === false)
			$c = $c;
		else
			$buf .= $c;
	}
	return $buf;
}

function LimpiarVariable($variable)
{
	$possiblesChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890 .,_-/@()%$";
	$possiblesChars .= chr(241) . chr(209) . chr(126) . chr(176);
	return Limpiar($variable, $possiblesChars);
}

function LimpiarNumero($variable)
{
	$possiblesChars = "1234567890";
	return Limpiar($variable, $possiblesChars);
}

function LimpiarNumero2($variable)
{
	$possiblesChars = "1234567890.,-";
	return Limpiar($variable, $possiblesChars);
}

function FechaSQL2WEB($fecha)
{
	if ($fecha == '')
		return '';
	$Ano = substr($fecha, 0, 4);
	$Mes = substr($fecha, 5, 2);
	$Dia = substr($fecha, 8, 2);
	return "$Dia-$Mes-$Ano";
}

function FechaWEB2SQL($fecha)
{
	if ($fecha == '')
		return '';
	$Dia = substr($fecha, 0, 2);
	$Mes = substr($fecha, 2, 2);
	$Ano = substr($fecha, 4, 4);
	return "$Ano-$Mes-$Dia";
}

function Volver()
{
	print "<input type=button value=\"Volver\" onclick=\"javascript:window.history.back();\">";
}

function Jurisdiccion($db, $Jur)
{
	$rs = pg_query($db, "SELECT denominacion FROM owner_rafam.jurisdicciones WHERE jurisdiccion = '$Jur' AND seleccionable = 'S'");
	if (!$rs)
		return $Jur;
	$row = pg_fetch_array($rs);
	return $Jur . " (" . trim($row[0]) . ")";
}

function CaracterEspecial($Cadena){
	$Cadena = str_replace(chr(209), chr(165), $Cadena);
	$Cadena = str_replace(chr(241), chr(164), $Cadena);
	$Cadena = str_replace(chr(186), chr(167), $Cadena);
	return $Cadena;
}

function EnviarArchivo($Ubicacion, $Archivo){
	$lTam = filesize($Ubicacion . $Archivo);
	//header("Content-Type: application/octet-stream\r\n");
	header("Content-Type: application/x-unknown\r\n");
	header("Content-Disposition: attachment; filename=\"$Archivo\"\r\n");
	header("Content-Length: $lTam\r\n\r\n");
	$fp = fopen($Ubicacion . $Archivo, 'rb');
	while(!feof($fp)){
		set_time_limit(0);
		echo fread($fp, 8192);
		flush();
	}
	fclose($fp);
}

function ParametroSQL($Parametro, $Tipo)
{
	if ($Parametro == ''){
		return "null";
	}else{
		if ($Tipo == '')
			return "'" . $Parametro . "'";
		else if ($Tipo == 'int4' || $Tipo == 'int2' || $Tipo == 'float' || $Tipo == 'float8')
			return $Parametro . "::$Tipo";
		else
			return "'" . $Parametro . "'::$Tipo";
	}
}

function Alerta($sMensaje){
	print "<div class=alerta>$sMensaje</div><br>";
}

function FormatearImporte($Importe){
	if ($Importe == '')
		return '';
	$iPos = strpos($Importe, '.');
	if ($iPos === false){
		return $Importe . '.00';
	}else{
		return substr($Importe . '00', 0, $iPos+3);
	}
}

function Moneteo($Importes){
	$TotalAPagar = 0;
	$Billetes = Array("100"=>0, "50"=>0, "20"=>0, "10"=>0, "5"=>0, "2"=>0, "1"=>0);
	for($i = 0; $i<count($Importes); $i++){
		$Paga = $Importes[$i];
		if ($Paga == '')
			$Paga = 0;
		if ($Paga > 0){
			$TotalAPagar += $Paga;
			foreach($Billetes as $Nominacion=>$Cantidad){
				while($Paga >= $Nominacion){
					$Paga -= $Nominacion;
				$Cantidad++;
				}
				$Billetes[$Nominacion] = $Cantidad;
			}
		}
	}
	if ($TotalAPagar > 0){
		print "\n<br><b>Diferencia A Pagar en Mano: $TotalAPagar<br><br>\n";
		print "MONETEO<br></b>\n";
		$Nomi = '';
		$Cant = '';
		foreach($Billetes as $Nominacion=>$Cantidad){
			$Nomi .= "<td>$Nominacion</td>";
			$Cant .= "<td>$Cantidad</td>";
		}
		print "<table class=datagrid width=200><tr><td>Nominaciones</td>$Nomi</tr>";
		print "<tr><td>Cantidades</td>$Cant</tr></table>\n";
	}
}

function Mes($dMes){
	switch($dMes){
	case 1:
		return "ENERO";
	case 2:
		return "FEBRERO";
	case 3:
		return "MARZO";
	case 4:
		return "ABRIL";
	case 5:
		return "MAYO";
	case 6:
		return "JUNIO";
	case 7:
		return "JULIO";
	case 8:
		return "AGOSTO";
	case 9:
		return "SEPTIEMBRE";
	case 10:
		return "OCTUBRE";
	case 11:
		return "NOVIEMBRE";
	case 12:
		return "DICIEMBRE";
	}
}

function MesCorto($dMes){
        switch($dMes){
        case 1:
                return "Ene";
        case 2:
                return "Feb";
        case 3:
                return "Mar";
        case 4:
                return "Abr";
        case 5:
                return "May";
        case 6:
                return "Jun";
        case 7:
                return "Jul";
        case 8:
                return "Ago";
        case 9:
                return "Sep";
        case 10:
                return "Oct";
        case 11:
                return "Nov";
        case 12:
                return "Dic";
        }
}

function Unidad($Un){
	switch($Un){
	case 1:
		return "Uno";
	case 2:
		return "Dos";
	case 3:
		return "Tres";
	case 4:
		return "Cuatro";
	case 5:
		return "Cinco";
	case 6:
		return "Seis";
	case 7:
		return "Siete";
	case 8:
		return "Ocho";
	case 9:
		return "Nueve";
	}
}

function Decena($De, $Un){
	if ($De == 0){
		return Unidad($Un);
	}else if ($De == 1){
		switch($Un){
		case 0:
			return "Diez";
		case 1:
			return "Once";
		case 2:
			return "Doce";
		case 3:
			return "Trece";
		case 4:
			return "Catorce";
		case 5:
			return "Quince";
		case 6:
			return "Dieciseis";
		case 7:
			return "Diecisiete";
		case 8:
			return "Dieciocho";
		case 9:
			return "Diecinueve";
		}
	}else if ($De == 2){
		if ($Un == 0){
			return "Veinte";
		}else{
			return "Veinti" . strtolower(Unidad($Un));
		}
	}else if ($De == 3){
		if ($Un == 0){
			return "Treinta";
		}else{
			return "Treinta Y " . Unidad($Un);
		}
	}else if ($De == 4){
		if ($Un == 0){
			return "Cuarenta";
		}else{
			return "Cuarenta Y " . Unidad($Un);
		}
	}else if ($De == 5){
		if ($Un == 0){
			return "Cincuenta";
		}else{
			return "Cincuenta Y " . Unidad($Un);
		}
	}else if ($De == 6){
		if ($Un == 0){
			return "Sesenta";
		}else{
			return "Sesenta Y " . Unidad($Un);
		}
	}else if ($De == 7){
		if ($Un == 0){
			return "Setenta";
		}else{
			return "Setenta Y " . Unidad($Un);
		}
	}else if ($De == 8){
		if ($Un == 0){
			return "Ochenta";
		}else{
			return "Ochenta Y " . Unidad($Un);
		}
	}else if ($De == 9){
		if ($Un == 0){
			return "Noventa";
		}else{
			return "Noventa Y " . Unidad($Un);
		}
	}
}

function Centena($Ce, $De, $Un){
	if ($Ce == 0){
		return Decena($De, $Un);
	}else if ($Ce == 1){
		if ($De == 0 && $Un == 0){
			return "Cien";
		}else{
			return "Ciento " . Decena($De, $Un);
		}
	}else if ($Ce == 2){
		if ($De == 0 && $Un == 0){
			return "Doscientos";
		}else{
			return "Doscientos " . Decena($De, $Un);
		}
	}else if ($Ce == 3){
		if ($De == 0 && $Un == 0){
			return "Trescientos";
		}else{
			return "Trescientos " . Decena($De, $Un);
		}
	}else if ($Ce == 4){
		if ($De == 0 && $Un == 0){
			return "Cuatrocientos";
		}else{
			return "Cuatrocientos " . Decena($De, $Un);
		}
	}else if ($Ce == 5){
		if ($De == 0 && $Un == 0){
			return "Quinientos";
		}else{
			return "Quinientos " . Decena($De, $Un);
		}
	}else if ($Ce == 6){
		if ($De == 0 && $Un == 0){
			return "Seiscientos";
		}else{
			return "Seiscientos " . Decena($De, $Un);
		}
	}else if ($Ce == 7){
		if ($De == 0 && $Un == 0){
			return "Setecientos";
		}else{
			return "Setecientos " . Decena($De, $Un);
		}
	}else if ($Ce == 8){
		if ($De == 0 && $Un == 0){
			return "Ochocientos";
		}else{
			return "Ochocientos " . Decena($De, $Un);
		}
	}else if ($Ce == 9){
		if ($De == 0 && $Un == 0){
			return "Novecientos";
		}else{
			return "Novecientos " . Decena($De, $Un);
		}
	}
}

function NumeroALetras($iNum){
	if ($iNum == 0){
		return "Cero";
	}else if ($iNum < 10){
		return Unidad($iNum);
	}else if ($iNum < 100){
		$De = substr($iNum, 0, 1);
		$Un = substr($iNum, 1, 1);
		return Decena($De, $Un);
	}else if ($iNum < 1000){
		$Ce = substr($iNum, 0, 1);
		$De = substr($iNum, 1, 1);
		$Un = substr($iNum, 2, 1);
		return Centena($Ce, $De, $Un);
	}else if ($iNum < 10000){
		$Mi = substr($iNum, 0, 1);
		$Ce = substr($iNum, 1, 1);
		$De = substr($iNum, 2, 1);
		$Un = substr($iNum, 3, 1);
		if ($Mi == 1){
			return "Mil " . Centena($Ce, $De, $Un);
		}else{
			return Unidad($Mi) . "mil " . Centena($Ce, $De, $Un);
		}
	}
}

function ComboGrupos($db, $GID, $Boton, $EmpresaID, $SucursalID)
{
	$rs = pg_query($db, "
SELECT gr.\"GrupoID\", gr.\"Descripcion\"
FROM \"tblGruposLiquidacion\" gr
WHERE gr.\"EmpresaID\" = $EmpresaID AND gr.\"SucursalID\" = $SucursalID
ORDER BY 2");
	if (!$rs)
	{
		pg_close($db);
		exit;
	}
?>
Grupo: <select id=selGrupo>
<?
while($row = pg_fetch_array($rs))
{
	$GrupoID = $row[0];
	$Descripcion = $row[1];
	?>
	<option value="<?=$GrupoID?>"
	<?
	if ($GrupoID == $GID)
		print " selected";
	print ">$Descripcion</option>";

}?>
</select>
<input type=submit id=accion name=accion value="<?=$Boton?>"
	onclick="javascript:document.getElementById('GID').value = document.getElementById('selGrupo').options[document.getElementById('selGrupo').selectedIndex].value;">
<?
}
?>
