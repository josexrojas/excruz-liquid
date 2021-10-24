<? include("header.php");

if (!($db = Conectar()))
	exit;

$AdelantoID	= LimpiarNumero($_REQUEST['AdelantoID']);
$Estado	= LimpiarNumero($_REQUEST['Estado']);


$SEGURIDAD_MODULO_ID = 6;

//include 'seguridad.php';

if ($Estado == 1)
 {
	$sql = "UPDATE \"tblAdelantos\" SET \"Estado\" = 1 WHERE \"AdelantoID\" = ".$AdelantoID;
 }
 else
 {
	 $sql = "UPDATE \"tblAdelantos\" SET \"Estado\" = 0 WHERE \"AdelantoID\" = ".$AdelantoID;
 }
pg_query($db, $sql);

header('Location: adelantoVerPendiente.php');
?>
