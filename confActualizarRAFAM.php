<?
include("header.php");

if (!($db = Conectar()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];

$SEGURIDAD_MODULO_ID = 8;

include 'seguridad.php';

?>
<H1><img src="images/icon64_liquidacion.gif" width="64" height="64" align="absmiddle" /> Sincronizar Desde RAFAM</H1>
<form name=frmConfActualizarRafam action=confActualizarRAFAM.php method=post>
<input type=hidden name=accion id=accion>
<?
$accion = LimpiarVariable($_POST["accion"]);

if ($accion == 'Actualizar'){
?>
	<p id=txtMensajes class=alerta></p>
	<div id=dvVolver style="display:none">
	<a class=tecla href="configuracion.php">
	<img src="images/icon24_prev.gif" alt="Volver" align="absmiddle" width="24" height="24" border="0"> Volver </a>
	</div>
<?
	include 'footer.php';

	if (!ChequearSeguridad(3))
		exit;

	$rs = pg_query($db, "
SELECT sr.\"Host\", sr.\"Oracle_SID\", sr.\"Usuario\", sr.\"Password\"
FROM \"tblServidorRAFAM\" sr
WHERE sr.\"EmpresaID\" = $EmpresaID AND sr.\"SucursalID\" = $SucursalID");
	if (!$rs)
		exit;

	$row = pg_fetch_array($rs);
	$Host = $row[0];
	$SID = $row[1];
	$Usuario = $row[2];
	$Password = $row[3];
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Conectando...';
	</script>
<?
	ob_flush();
	flush();

	$dbRAFAM = OCILogon($Usuario, $Password, "//$Host/$SID");
	if (!$dbRAFAM) {
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Error al conectar con el servidor RAFAM';
		document.getElementById('dvVolver').style.display = 'block';
	</script>
<?
		pg_close($db);
		exit;
	}
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Conectado';
	</script>
<?
	ob_flush();
	flush();
	sleep(1);
	$AnoPresup = date("Y");
	// Definimos los querys para traer la informacion desde RAFAM
	$qrys = array(8);
	$qrys[0] = "SELECT jurisdiccion, denominacion, seleccionable, vigente_desde, vigente_hasta 
FROM owner_rafam.jurisdicciones";
	$qrys[1] = "SELECT jurisdiccion, agrupamiento, denominacion, detalle FROM owner_rafam.agrupamientos 
WHERE ejercicio = $AnoPresup";
	$qrys[2] = "SELECT jurisdiccion, agrupamiento, categoria, denominacion, detalle FROM owner_rafam.categorias
WHERE ejercicio = $AnoPresup";
	$qrys[3] = "SELECT jurisdiccion, agrupamiento, categoria, cargo, denominacion, detalle FROM owner_rafam.cargos
WHERE ejercicio = $AnoPresup";
	$qrys[4] = "SELECT anio_presup, jurisdiccion, programa, activ_proy, activ_obra, desagrega, denominacion, denominacion_ab,
codigo_ue FROM owner_rafam.estruc_prog WHERE anio_presup = $AnoPresup";
	$qrys[5] = "SELECT anio_presup, codigo_ff, denominacion, totalizadora FROM owner_rafam.fuen_fin
WHERE anio_presup = $AnoPresup";
	$qrys[6] = "SELECT anio_presup, inciso, par_prin, par_parc, par_subp, denominacion, denominacion_ab, totalizadora 
FROM owner_rafam.gastos WHERE anio_presup = $AnoPresup";
	$qrys[7] = "SELECT codigo_ue, denominacion, denominacion_ab FROM owner_rafam.uni_ejec";

	// Definimos las tablas en la base de datos local
	$tbls = array(8);
	$tbls[0] = 'owner_rafam.jurisdicciones';
	$tbls[1] = 'owner_rafam.agrupamientos';
	$tbls[2] = 'owner_rafam.categorias';
	$tbls[3] = 'owner_rafam.cargos';
	$tbls[4] = 'owner_rafam.estruc_prog';
	$tbls[5] = 'owner_rafam.fuen_fin';
	$tbls[6] = 'owner_rafam.gastos';
	$tbls[7] = 'owner_rafam.uni_ejec';

	$i = 0;
	$bAbortarTransaccion = false;

	// Inicializa la transaccion de sincronizacion
	pg_exec($db, "BEGIN");

	while($i < count($qrys) && !$bAbortarTransaccion){
		$id = PrepararQuery($dbRAFAM, $qrys[$i]);
		if (!$id){
			$bAbortarTransaccion = true;
			break;
		}
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Actualizando tabla <?=$tbls[$i]?>';
	</script>
<?
		ob_flush();
		flush();
		sleep(1);
		if (!$bAbortarTransaccion && !pg_exec($db, 'DELETE FROM ' . $tbls[$i]))
			$bAbortarTransaccion = true;

		if (!$bAbortarTransaccion){
			$copyQry = '';
			while ($succ = OCIFetchInto($id, $row, OCI_ASSOC+OCI_RETURN_NULLS)) {
				if ($copyQry == ''){
					$copyQry = 'COPY ' . $tbls[$i] . ' (';
					foreach ($row as $campo => $valor){
						$copyQry .= "$campo, ";
					}
					$copyQry = substr($copyQry, 0, -2);
					$copyQry .= ") FROM stdin";
					//print "$copyQry\n";
					if (!pg_exec($db, $copyQry)){
						$bAbortarTransaccion = true;
						break;
					}
				}
				$putLine = '';
				foreach ($row as $valor){
					if ($valor == '')
						$valor = '\N';
					$putLine .= "$valor\t";
				}
				if ($putLine != ''){
					$putLine = substr($putLine, 0, -1) . "\n";
					//print $putLine;
					if (!pg_put_line($db, $putLine))
						$bAbortarTransaccion = true;
				}
			}
			if (!$bAbortarTransaccion){
				if (!pg_put_line($db, "\\.\n"))
					$bAbortarTransaccion = true;
				else{
					if (!pg_end_copy($db))
						$bAbortarTransaccion = true;
				}
			}
		}
		$i++;
	}

	if ($bAbortarTransaccion){
		// Ocurrio un error
		pg_exec($db, "ROLLBACK");
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Ocurrio un error transaccional al sincronizar las bases de datos';
		document.getElementById('dvVolver').style.display = 'block';
	</script>
<?
		pg_close($db);
		exit;
	}
	
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'Finalizando transacci&oacute;n';
	</script>
<?
	ob_flush();
	flush();
	sleep(1);
	pg_exec($db, "COMMIT");
	OCILogoff($dbRAFAM);
/*	$e = OCIError($id);
	Alerta($e['message']);
	exit;*/
?>
	<script>
		document.getElementById('txtMensajes').innerHTML = 'La sincronizaci&oacute;n fue realizada existosamente';
		document.getElementById('dvVolver').style.display = 'block';
	</script>
<?
}

if ($accion == ''){
?>
	<script>
		function Aceptar(){
			if (!ChequearSeguridad(3))
				return false;
			document.getElementById('accion').value = 'Actualizar';
			document.frmConfActualizarRafam.submit();
		}
	</script>
	<center><b>&iquest;Est&aacute; seguro que quiere realizar la sincronizaci&oacute;n?</b><br><br>
	<a class=tecla href="javascript:Aceptar();void(0);">
	<img src="images/icon24_grabar.gif" alt="Aceptar" align="absmiddle" width="24" height="24" border="0"> Aceptar </a>&nbsp;&nbsp;&nbsp;
	<a class=tecla href="configuracion.php">
	<img src="images/icon24_prev.gif" alt="Volver" align="absmiddle" width="24" height="24" border="0"> Volver </a></center>
<?
	include 'footer.php';
}

pg_close($db);

function PrepararQuery($dbRAFAM, $qry){
	$id = OCIParse($dbRAFAM, $qry);
	if (!$id)
		return false;

	$rs = OCIExecute($id, OCI_DEFAULT);
	if (!$rs)
		return false;

	return $id;
}
?> 
