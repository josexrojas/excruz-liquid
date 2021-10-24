<?php

include ('header.php');

function conectarBase($anio,$mes){

	if($mes > 9){
		return pg_connect("dbname=Sueldos" .  $anio . $mes . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
	}else{
		return  pg_connect("dbname=Sueldos" .  $anio . "0" .  $mes . " user=" . getenv("DB_USER"). " password=" . getenv("DB_PWD") . " host=" . getenv("DB_HOST"));
	}
}

function getDatos($db){

	return pg_query($db, "
	select c.\"Legajo\", e.\"Nombre\" || ' ' || e.\"Apellido\" as \"Empleado\", c.\"Categoria\", c.\"Grupo\", c.\"Jornada\" 
	from \"tblCategoriasEmpleado\" c 
	inner join \"tblEmpleados\" e on c.\"Legajo\"=e.\"Legajo\"");
}

function getMeses($anio) {

	$meses = 0;

	$anio_act = date("Y");

	if($anio != $anio_act) {
		
		$meses = 12;

		return $meses;
	} else {

		// Obtenemos un mes menos al actual, ya que la base de mes actual no se encontratria montada
		$mes_act = date("m");
		$mes_act--;

		return $mes_act;
	}
}

function mostrarInfo($data){
	foreach($data as  $clave=> $valor){  ?>
		<tr>
			<?php foreach($valor as  $clave2=> $valor2){ ?> 
			<td> 
				<?php echo $valor2; ?>
			</td>
			<?php } ?>
		</tr>	
<?php }
}

?>
<form name="" action="listadoHCG.php"  method="post">

<?php

	$accion = LimpiarVariable($_POST["accion"]);
		if ($accion == 'Ver Listado'){

			$anio = $_POST["anio"];
			$meses = getMeses($anio);

?>			
			<br>
			<H1>Listado HCG</H1>
			<p> A&ntilde;o: <?php echo $anio ?></p>
<?php
			$data = array();

			for ($i=1; $i <= $meses; $i++) {	

				$db = conectarBase($anio,$i);

				if (!$db){ // Si no se pudo conectar a la BD

					echo "Error al conectar la bd mes: " . $i . " anio: " . $anio ;
					echo "<br>";

					continue;
				}

				$results = getDatos($db);
		
				if (!$results) {
					echo "Ocurrió un error al obtener los datos.\n";
					continue;
				}

				$data = array();
				while($row = pg_fetch_row($results))
					array_push($data, $row);
?>
				
				<div>
					<tr>
						<p> Mes: <?php echo Mes($i);?></p>
					</tr>
					<table  class="datagrid">
						<tr>
							<th>Legajo</th>
							<th>Nombre</th>
							<th>Categoria</th>
							<th>Grupo</th>
							<th>Carga Horaria</th>
						</tr>
					
					<?php mostrarInfo($data); ?>
					
					</table>
				</div>
<?php
			}		
?>
			<div>
				<br>
				<br>
				<tr>
					<td class="centro">
						<input type="button" value="Volver" onclick="javascript:window.history.back();">
					</td>
				</tr>
			</div>
</form>
<?php
		}
		if($accion == ''){
?>
			<br>
			<H1>Listado HCG</H1>
				<table  class="datauser" align="left">
					<tr>
						<td class="izquierdo">A&ntilde;o:</td>
					  	<td class="derecho">
							<select name="anio" id= "anio">
								<option value= ""> </option>
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
		if (document.getElementById('anio').value.length < 1){
			alert('Debe completar el Anio');
			document.getElementById('anio').focus();
			return false;
		}
	}
</script>