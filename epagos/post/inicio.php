<?php

//print_r($_GET);
//exit;

/**
 * Probador de la generación de Token para la página de POST
 * Pasos:
 *   1 - Generar el token
 *   2 - Invocar al formulario de POST con ese token y los otros parámetros necesarios,
 *       puede invocarse al formulario intermedio de tests/inicio.php para completar
 *       los otros parámetros desde una interfaz más cómoda.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include("../api/epagos_api.class.php");

/*
define("ID_ORGANISMO", "24"); //TODO: ccmpletar con el ID de organismo proporcionado
define("ID_USUARIO",   "38"); //TODO: ccmpletar con el ID de usuario proporcionado
define("PASSWORD",     "3d0dded8c4eebcd2021d7ca0e0603e4a"); //TODO: ccmpletar con el password proporcionado
define("HASH",         "67777aca3e1fec5243f34b2588937629"); //TODO: ccmpletar con el hash proporcionado
*/


define("ID_ORGANISMO", "24"); //TODO: ccmpletar con el ID de organismo proporcionado
define("ID_USUARIO",   "78"); //TODO: ccmpletar con el ID de usuario proporcionado
define("PASSWORD",     "98cb2b62354cecd88efbf48078c226c4"); //TODO: ccmpletar con el password proporcionado
define("HASH",         "cb03c57cf1256713b1e7a5a7c91b1edc"); //TODO: ccmpletar con el hash proporcionado



//TODO: reemplazar por su URL para el caso de pago correcto (no implica acreditado)

//define("URL_OK",       "http://192.168.5.53/epagos/post2/ok.php");
define("URL_OK",       "https://www.exaltaciondelacruz.gob.ar/index.php?option=com_serverwrapper&Itemid=150");
//define("URL_OK",       "http://www.exaltaciondelacruz.gov.ar/epagos/post2/ok.php");


//TODO: reemplazar por su URL para el caso de pago con errores
//define("URL_ERROR",    "http://192.168.5.53/epagos/post2/error.php");
define("URL_ERROR",    "https://www.exaltaciondelacruz.gob.ar/index.php?option=com_serverwrapper&Itemid=144");

$epagos = new epagos_api(ID_ORGANISMO, ID_USUARIO);

//
// el SDK soporta dos entornos:
// Testing    -> EPAGOS_ENTORNO_SANDBOX
// Producción -> EPAGOS_ENTORNO_PRODUCCION
//
try {
//$epagos->set_entorno(EPAGOS_ENTORNO_SANDBOX);
$epagos->set_entorno(EPAGOS_ENTORNO_PRODUCCION);

  $respuesta = $epagos->obtener_token_post(PASSWORD, HASH);

  // control de error en la respuesta
  if (empty($respuesta->token)) {
    echo 'Error: <b>obtener_token_post</b>: ';
    echo "<pre>";
    print_r($respuesta);
    echo '</pre>';
 exit;
  }

$token = $respuesta->token;

$arr = array();

$monto_operacion = 0;

for ($i=0; $i<count($_GET['id_item']);$i++)
{
        $arr2 = array();
        $arr2['id_item'] = $_GET['id_item'][$i];
        $arr2['desc_item'] = "Tasa municipal"; //$_GET['desc_item'][$i];
        $arr2['monto_item'] = number_format($_GET['monto_item'][$i], 2, ".", "");
        $arr2['cantidad_item'] = $_GET['cantidad_item'][$i];

        $monto_operacion += $_GET['monto_item'][$i];
        array_push($arr, $arr2);
}

  //TODO: personalizar con sus valores y detalles a enviar, esto es opcional, puede no enviarlo

  $detalle_op = urlencode(json_encode($arr));
  $datos_post = array(
    'numero_operacion' => $_GET['id_item'][0], 
    'id_moneda_operacion' => '1',
    'monto_operacion' =>number_format($monto_operacion, 2, ".", ""),   // TODO: reemplazar por el importe de la operación
    'detalle_operacion' => $detalle_op,
    //'convenio' => '36589',
    'convenio' => '10024',
    //
    // En el caso de que se desee mostrar el usuario el detalle en la pantalla de pago, incluir:
    //'detalle_operacion_visible' => 1,
    //
    'ok_url' => URL_OK,     //TODO: reemplazar por sus URL
    'error_url' => URL_ERROR,  //TODO: reemplazar por sus URL
    //
    // Usos avanzados
    //
    // - Para restringir que solo se vean determinados medios de pago:
    'fp_permitidas' => serialize(array(1, 2, 3, 9, 10, 11, 14,15,28,4,5,8,16,13,18,19,20,21,22,23,33,32,31,30,29,17,25,6,7,26,27,38)),
    //'fp_excluidas' => serialize([23, 19]),
    //
    // - Para excluir determinados medios de pago:
    // $datos_post['fp_excluidas'] = serialize([1, 2, 3]);
     'email_pagador'=> $_GET['email_pagador'],

  );

  $epagos->solicitud_pago_post($datos_post);

} catch (EPagos_Exception $e){
         echo "Error: ".$e->getMessage();
} /* try, catch */
                                                                                        
