<?php
include ('header.php');

if (!($db = Conectar()))
	exit;

function getDatos($db,$legajo,$anio){
//print("
return pg_query($db,"
SELECT
\"Legajo\"
,CASE
WHEN EXTRACT(MONTH FROM \"Fecha\") = 1 THEN 'Enero'
WHEN  EXTRACT(MONTH FROM \"Fecha\") = 2 THEN 'Febrero'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 3 THEN 'Marzo'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 4 THEN 'Abril'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 5 THEN 'Mayo'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 6 THEN 'Junio'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 7 THEN 'Julio'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 8 THEN 'Agosto'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 9 THEN 'Septiembre'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 10 THEN 'Octubre'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 11 THEN 'Noviembre'
WHEN EXTRACT(MONTH FROM \"Fecha\") = 12 THEN 'Diciembre'
END AS \"Mes\"
,\"AliasID\"
,\"Descripcion\"
,SUM(\"Descuento\") AS \"Descuento\"
FROM \"tblRecibos\"
WHERE \"ConceptoID\" in (36)
      AND \"Fecha\" >=  '$anio-01-01'::DATE
      AND \"Fecha\" <   '$anio-12-31'::DATE
      AND \"Legajo\" = '$legajo'
GROUP BY \"Legajo\",\"Fecha\",\"AliasID\",\"Descripcion\"
ORDER BY \"Fecha\",\"AliasID\"
");
//exit();
}

//----------------------------------------------------------------------------------------
?>
<form name=""  action="listadoRetencionesLegajo.php"  method="post">

<?php

	 $accion = LimpiarVariable($_POST["accion"]);
	 if ($accion == 'Ver Listado'){

			$data   = array();
			$anio   = $_POST["anio"];
			$legajo = $_POST["legajo"];
?>
			<br>
			<H1>Listado Retenciones por Legajo</H1>
<?php
		   print "<br><b>Legajo: " . $legajo . "</b></br>";
			 print "<br><b>Periodo: " . $anio . "</b></br>";

	     $rs  = getDatos($db,$legajo,$anio);

       $total_1 = 0;
       $total_2 = 0;
       $total_3 = 0;
       $total_4 = 0;
       $total_5 = 0;
       $total_6 = 0;
       $total_7 = 0;
       $total_8 = 0;
       $total_9 = 0;
       $total_10 = 0;
       $total_11 = 0;
       $total_12 = 0;
       $total_total = 0;
?>
			<div>
				<table  class="datagrid">
					<tr>
							<th>Mes</th>
							<th>Descripcion</th>
							<th>Descuento</th>
					</tr>
				  <br>

<?php
        while($row = pg_fetch_array($rs)){
?>
         <tr><td><?=$row['Mes']?></td><td><?=$row['Descripcion']?></td><td><?="$ ". $row['Descuento']?></td></tr>
<?
          if ($row['Mes'] == 'Enero' )
            $total_1 +=  $row['Descuento'];

          if ($row['Mes'] == 'Febrero' )
            $total_2 +=  $row['Descuento'];

          if ($row['Mes'] == 'Marzo' )
            $total_3 +=  $row['Descuento'];

          if ($row['Mes'] == 'Abril' )
            $total_4 +=  $row['Descuento'];

          if ($row['Mes'] == 'Mayo' )
            $total_5 +=  $row['Descuento'];

          if ($row['Mes'] =='Junio' )
            $total_6 +=  $row['Descuento'];

          if ($row['Mes'] == 'Julio' )
            $total_7 +=  $row['Descuento'];

          if ($row['Mes'] == 'Agosto' )
            $total_8 +=  $row['Descuento'];

          if ($row['Mes'] == 'Septiembre' )
            $total_9 +=  $row['Descuento'];

          if ($row['Mes'] == 'Octubre' )
            $total_10 +=  $row['Descuento'];

          if ($row['Mes'] == 'Noviembre' )
            $total_11 +=  $row['Descuento'];

          if ($row['Mes'] == 'Diciembre' )
            $total_12 +=  $row['Descuento'];

          $total_total += $row['Descuento'];
        }
?>
</table>
<?
	if($total_1>0)
		print "<br><b>Total Enero: $ " . $total_1 . "</b></br>";
	if($total_2>0)
		print "<br><b>Total Febrero: $ " . $total_2 . "</b></br>";
	if($total_3>0)
		print "<br><b>Total Marzo: $ " . $total_3 . "</b></br>";
	if($total_4>0)
		print "<br><b>Total Abril: $ " . $total_4 . "</b></br>";
	if($total_5>0)
		print "<br><b>Total Mayo: $ " . $total_5 . "</b></br>";
	if($total_6>0)
		print "<br><b>Total Junio: $ " . $total_6 . "</b></br>";
	if($total_7>0)
		print "<br><b>Total Julio: $ " . $total_7 . "</b></br>";
	if($total_8>0)
		print "<br><b>Total Agosto: $ " . $total_8 . "</b></br>";
	if($total_9>0)
		print "<br><b>Total Septiembre: $ " . $total_9 . "</b></br>";
	if($total_10>0)
		print "<br><b>Total Octubre: $ " . $total_10 . "</b></br>";
	if($total_11>0)
		print "<br><b>Total Noviembre: $ " . $total_11 . "</b></br>";
	if($total_12>0)
		print "<br><b>Total Diciembre: $ " . $total_12 . "</b></br>";

print "<br><b>Total Anual: $ " . $total_total . "</b></br>";
?>
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
  <H1>Listado Retenciones por Legajo</H1>
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

      //include("footer.php");
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

