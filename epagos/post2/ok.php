<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<?php


//print_r($_POST);
//print_r($_GET);
//print_r($_REQUEST);
//exit;

 if($_REQUEST['id_resp']== '02002'){?>

        <div class="alert alert-success" role="alert">
        	<b>Proceso realizado con exito, descargue el instructivo con los pasos a seguir</b><br>
        	 <? print($_REQUEST["respuesta"]); ?>
        </div>


<?php }else{ ?>

        <div class="alert alert-success" role="alert">
       		<b>Su pago ha sido procesado correctamente. Los cambios se veran reflejados en 24hs</b><br>
        </div>


<?php
}
?>



<!--<form method="post" action="pdf.php"> -->
<!-- gov -->
<form method="post" action="https://www.exaltaciondelacruz.gob.ar/index.php?option=com_serverwrapper&Itemid=151"> 

<?php

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/

include("../api/epagos_api.class.php");

//test:
/*
define("ID_ORGANISMO", "24"); //TODO: ccmpletar con el ID de organismo proporcionado
define("ID_USUARIO",   "38"); //TODO: ccmpletar con el ID de usuario proporcionado
define("PASSWORD",     "3d0dded8c4eebcd2021d7ca0e0603e4a"); //TODO: ccmpletar con el password proporcionado
define("HASH",         "67777aca3e1fec5243f34b2588937629"); //TODO: ccmpletar con el hash proporcionado
*/

//produccion:

define("ID_ORGANISMO", "24"); //TODO: ccmpletar con el ID de organismo proporcionado
define("ID_USUARIO",   "78"); //TODO: ccmpletar con el ID de usuario proporcionado
define("PASSWORD",     "98cb2b62354cecd88efbf48078c226c4"); //TODO: ccmpletar con el password proporcionado
define("HASH",         "cb03c57cf1256713b1e7a5a7c91b1edc"); //TODO: ccmpletar con el hash proporcionado


$epagos = new epagos_api(ID_ORGANISMO, ID_USUARIO);

//$epagos->set_entorno(EPAGOS_ENTORNO_SANDBOX);
$epagos->set_entorno(EPAGOS_ENTORNO_PRODUCCION);

$respuesta = $epagos->obtener_token_post(PASSWORD, HASH);
$epagos->obtener_token(PASSWORD, HASH);


$criterios = array(
      "CodigoUnicoTransaccion" => $_REQUEST['id_transaccion']
    );

$devolucion=$epagos->obtener_pagos($criterios);

$pago= $devolucion['pago'];

if($_REQUEST['pdf']){

                if($_REQUEST['id_resp']== '02002'){ ?>

                   <div class="text-center">
                   <button type="submit" class="btn btn-info">Descargar Instructivo</button>
                   </div>

          <?php }else{ ?>
                   <div class="text-center">
                   <button type="submit" class="btn btn-info">Descargar Factura</button>
                   </div>
          <?php }
 }else{ ?>
                   <div class="text-center">
                   <a href="<?print($pago[0]->Recibo);?>" class="btn btn-info" >Descargar Comprobante</a>
                   </div>
<?
}
?>

<?php

$contenido_salida=base64_decode($_REQUEST['pdf']);
$directoriofichero= sys_get_temp_dir();
$tempfile=md5(rand(0,1000000));


$gestor=fopen('/tmp/'.$tempfile.'.pdf','w');
fwrite($gestor,$contenido_salida);
fclose($gestor);

?>
<input type="hidden" name="token" value="<?php echo $tempfile; ?>" />
<input type="hidden" name="dire" value="<?php echo $pago[0]->Recibo; ?>" />
</form>

