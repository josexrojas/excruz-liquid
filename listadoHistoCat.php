<?php
//ver bien lo de NOTICE
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include ('header.php');

if (!($db = Conectar()))
	exit;

?>
<form name=""  action="listadoHistoCat.php"  method="post">

	<?php


	$accion = LimpiarVariable($_POST["accion"]);
		if ($accion == 'Ver Listado'){

		$data = [];


		if($_POST["anio"] == 2015){

			$db = pg_connect("dbname=" . Sueldos201501 . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
			if (!$db)
				exit;




			//consulta de datos
/*
			pg_close($db);

			$db = pg_connect("dbname=" . Sueldos201602 . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
			if (!$db)
				exit;

			//consulta de datos

			pg_close($db);


*/
		}else if( $_POST["anio"] == 2016){

			$db = pg_connect("dbname=" . Sueldos2016 . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
			if (!$db)
				exit;
echo "hola1";

#if me devolvio algo o no
			//$data['categoria']=

$rs = pg_query($db, "
SELECT jurisdiccion, case when codigo_ff is null then 110 else codigo_ff end as codigo_ff, inciso, par_prin, par_parc, par_subp, activ_prog, activ_proy, case when activ_obra is null then 0 else activ_obra end as activ_obra,
SUM(\"Importe\") AS \"Importe\"
FROM \"tblImputacionesRafam\"
WHERE \"EmpresaID\" = $EmpresaID AND \"SucursalID\" = $SucursalID AND \"Fecha\" = '$FechaPeriodo'
AND \"NumeroLiquidacion\" = $NumeroLiquidacion
GROUP BY jurisdiccion, codigo_ff, inciso, par_prin, par_parc, par_subp, activ_prog, activ_proy, case when activ_obra is null then 0 else activ_obra end
ORDER BY 1,2,3,4,5,6,7,8,9
");
if (!$rs){
	exit;
}







pg_close($db);


$db = pg_connect("dbname=" . Sueldos20160927 . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
if (!$db)
	exit;

echo "hola2";
pg_close($db);


$db = pg_connect("dbname=" . Sueldos20161129 . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
if (!$db)
	exit;

echo "hola3";
pg_close($db);







					}else{


					}
	?>
<div>
					<table>

					</table>
					</div>








	<?php

}
		if ($accion == ''){
		echo "2";
	?>

</br>
<H1>Listado Historico de Categorias</H1>
	<table  class="datauser" align="left">

		<TR>
			<TD class="izquierdo">Legajo:</TD>
			<TD class="derecho"><input type=text name="txtLegajo" size=5 required> </TD>
		</TR>
	  <TR>
			<TD class="izquierdo">Anio:</TD>
		  	<TD class="derecho">
					<select name="anio" required>
				     <option value= ""> </option>
  					 <option value="2015">2015</option>
				     <option value="2016">2016</option>
				     <option value="2017">2017</option>
				     <option value="2018">2018</option>
			    </select>
				</TD>
		 </TR>
		 <TR>
				<TD class="izquierda"></TD>
					<TD class="derecho">
						<input type="submit" id="accion" name="accion" value="Ver Listado">
						<input type="button" value="Volver" onclick="javascript:window.history.back();">
				  </TD>
     </TR>

	</table>
</form>
<?
}//if

include("footer.php");

?>
