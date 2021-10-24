<? include("header.php");

if (!($db = Conectar()))
	exit;

$AdelantoID	= LimpiarNumero($_REQUEST['AdelantoID']);

$SEGURIDAD_MODULO_ID = 6;

include 'seguridad.php';

$sql = "SELECT \"EliminarAdelanto\"($AdelantoID)";
pg_query($db, $sql);

header('Location: adelantoVerPendiente.php');
?>
