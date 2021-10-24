<?php
require_once('funcs.php');
EstaLogeado();

$periodo = LimpiarVariable($_GET["periodo"]);

$_SESSION['instancia'] = $periodo;

?>
Se ha cambiado la instancia a <?php echo $periodo ?>

<a href="main.php">Continuar</a>

