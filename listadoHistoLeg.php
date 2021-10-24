<?php

include ('header.php');

function conectarBase($anio,$mes){

return pg_connect("dbname=Sueldos" .  $anio .  $mes . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
}

function montar($anio,$mes){


	
	if($mes==12){
		$anio_sig = $anio + 1;
		if (!file_exists("/home/sitios/liquid/SQLBackup/". $anio_sig . "-01-09.sql.gz"))
			return false;

					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-01-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";

					$resultado = exec($comando,$error);

					if(!$resultado){
					return $resultado;
					}else{
					return conectarBase($anio,$mes);
					}
	}else{
				if($mes == 9 || $mes == 10 || $mes == 11){
					$mes_sig  = $mes + 1;
					$anio_sig = $anio;

			if (!file_exists("/home/sitios/liquid/SQLBackup/". $anio_sig . "-" . $mes_sig . "-09.sql.gz"))
				return false;
					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-" . $mes_sig . "-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";

				  $resultado = exec($comando,$error);

					if(!$resultado){
					return $db;
					}else{
					return conectarBase($anio,$mes);
					}
				}else{
					$mes_sig  = $mes + 1;
					$anio_sig = $anio;

			if (!file_exists("/home/sitios/liquid/SQLBackup/". $anio_sig . "-0" . $mes_sig . "-09.sql.gz"))
				return false;
					$comando = "createdb Sueldos" .  $anio .  $mes . " -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/". $anio_sig . "-0" . $mes_sig . "-09.sql.gz | pg_restore -Ft -c | psql -dSueldos".  $anio .  $mes . " -USueldos";

					$resultado = exec($comando,$error);

					if(!$resultado){
					return $db;
					}else{
					return conectarBase($anio,$mes);
					}
				}
    }
}
function getDatos($db,$legajo){

	return pg_query($db, "
												SELECT * FROM \"tblCategoriasEmpleado\"
													WHERE \"Legajo\" = '$legajo'
											");
}
function setData($row,$i,$data){

	 if(!$row){
		 $data['mes'][$i]      	  = $i;
		 $data['dependencia'][$i] = "-";
		 $data['grupo'][$i]       = "-";
		 $data['planta'][$i]      = "-";
		 $data['categoria'][$i]   = "-";
		 $data['jornada'][$i]     = "-";
		 $data['sueldo'][$i]      = "-";

	 }else{

		 $data['mes'][$i]     	  = $i;
		 $data['dependencia'][$i] = $row['Dependencia'];
		 $data['grupo'][$i]       = $row['Grupo'];
		 $data['planta'][$i]      = $row['Planta'];
		 $data['categoria'][$i]   = $row['Categoria'];
		 $data['jornada'][$i]     = $row['Jornada'];
		 $data['sueldo'][$i]      = "$ " . $row['Sueldo'];

		}

return $data;
}
//----------------------------------------------------------------------------------------
?>
<form name=""  action="listadoHistoLeg.php"  method="post">

<?php

	$accion = LimpiarVariable($_POST["accion"]);
		if ($accion == 'Ver Listado'){

				$data   = array();
				$anio   = $_POST["anio"];
				$legajo = $_POST["legajo"];
?>
			<br>
			<H1>Listado Hist&oacute;rico de Legajo</H1>

			<?php

			print "<br><b>Legajo: " . $legajo . "</b></br>";
			print "<br><b>Periodo: " . $anio . "</b></br>";

					for ($i=1; $i < 13; $i++) {

							if($i<10){
								$mes = "0" .  $i;
							}else{
									$mes = $i;
							}

						$db = conectarBase($anio,$mes);

							if (!$db){

								$db = montar($anio,$mes);

									if(!$db){
									continue;
									}else{

											$rs  = getDatos($db,$legajo);
											$row = pg_fetch_array($rs);
											$data =	setData($row);

											pg_close($db);
									continue;
									}
	            }

						$rs   = getDatos($db,$legajo);
						$row  = pg_fetch_array($rs);
						$data =	setData($row,$i,$data);

						pg_close($db);

			  }

				?>
								<div>
									<table  class="datagrid">

										<tr>
											<th>Mes</th>
											<th>Dependencia</th>
											<th>Grupo</th>
											<th>Planta</th>
											<th>Categoria</th>
											<th>Jornada</th>
											<th>Sueldo</th>
										</tr>

										<br>
										<br>

										<?php	foreach ($data as  $clave=> $valor){  ?>
										 <td>

															<?php foreach ($valor as  $clave2=> $valor2){  ?>
																		<br>
																							<?php print($valor2); ?>
																		<br>
															<?php } ?>

										 </td>
										<?php }	?>

									</table>
								</div>

								<div>
									<br>
									<br>
										<tr>
											<td class="centro">
												<input type="button" value="Volver" onclick="javascript:window.history.back();">
											</td>
										</tr>
								</div>
	<?php
		}
		if ($accion == ''){
	?>
			</br>
			<H1>Listado Hist&oacute;rico de Legajo</H1>
				<table  class="datauser" align="left">

					<tr>
						<td class="izquierdo">Legajo:</td>
						<td class="derecho"><input type=text name="legajo" id="legajo" size=5> </td>
					</tr>
				  <tr>
						<td class="izquierdo">A&ntilde;o:</td>
					  	<td class="derecho">
								<select name="anio" id= "anio">
							     <option value= ""> </option>
			  					 <option value="2015">2015</option>
							     <option value="2016">2016</option>
							     <option value="2017">2017</option>
							     <option value="2018">2018</option>
							     <option value="2019">2019</option>
							     <option value="2020">2020</option>
								 <option value="2021">2021</option>
						    </select>
							</td>
					 </tr>
					 <tr>
							<td class="izquierda"></td>
								<td class="derecho">
									<input type="submit" id="accion" name="accion" value="Ver Listado" onclick="return valida()">
									<input type="button" value="Volver" onclick="javascript:window.history.back();">
							  </td>
			     </tr>

				</table>
			</form>
			<?
		}

include("footer.php");

?>
<script language="JavaScript">
function valida(){

	if (document.getElementById('legajo').value.length < 1){
		alert('Debe completar el legajo del empleado');
		document.getElementById('legajo').focus();
	return false;
	}else{
				var ingreso =  document.getElementById("legajo").value.charCodeAt();
					if(!(ingreso >= 48 && ingreso <= 57)){
						alert('Debe ingresar un legajo valido');
		        document.getElementById('legajo').value="";
						document.getElementById('legajo').focus();
					return false;
					}
  }
	if (document.getElementById('anio').value.length < 1){
		  alert('Debe completar el Anio');
			document.getElementById('anio').focus();
	return false;
	}
}
</script>
