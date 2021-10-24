<?
include("header.php");

if (!($db = Conectar()))
	exit;
	
if (!($db2 = ConectarSimulador()))
	exit;

$EmpresaID = $_SESSION["EmpresaID"];
$SucursalID = $_SESSION["SucursalID"];
$Periodo = ($_REQUEST['Periodo'] != '') ? $_REQUEST['Periodo'] : '2015-01-01';

$accion = LimpiarVariable($_POST["accion"]);

if ($accion == "recalcular")
{
	$Jornal6 = $_REQUEST['Jornal6'];
	$Jornal8 = $_REQUEST['Jornal8'];
	$Jornal9 = $_REQUEST['Jornal9'];
	
	$query = "UPDATE \"tblConceptosParametrosValores\" SET \"Valor\" = $Jornal6 WHERE \"AliasID\" = 121";
	$query2 = "UPDATE \"tblNovedades\" SET \"Valores\"[2] = $Jornal6 WHERE \"AliasID\" = 121";
	pg_query($db2, $query);
	pg_query($db2, $query2);
	
	$query = "UPDATE \"tblConceptosParametrosValores\" SET \"Valor\" = $Jornal8 WHERE \"AliasID\" = 138";
	$query2 = "UPDATE \"tblNovedades\" SET \"Valores\"[2] = $Jornal8 WHERE \"AliasID\" = 138";
	pg_query($db2, $query);
	pg_query($db2, $query2);

	$query = "UPDATE \"tblConceptosParametrosValores\" SET \"Valor\" = $Jornal9 WHERE \"AliasID\" = 141";
	$query2 = "UPDATE \"tblNovedades\" SET \"Valores\"[2] = $Jornal9 WHERE \"AliasID\" = 141";	
	pg_query($db2, $query);
	pg_query($db2, $query2);
	
	
	$query = "SELECT \"LiquidacionTotal\"('$Periodo')";
	pg_query($db2, $query);
	
	header('location: simulador.php?Periodo='.$Periodo);
	exit;
}

$query = "select
sum(case when \"AliasID\" = 121 then \"Valor\" else 0 end) as \"Jornal6\",
sum(case when \"AliasID\" = 138 then \"Valor\" else 0 end) as \"Jornal8\",
sum(case when \"AliasID\" = 141 then \"Valor\" else 0 end) as \"Jornal9\"
from \"tblConceptosParametrosValores\" where \"AliasID\" in (121, 138, 141)
group by \"ConceptoID\"";

$rs = pg_query($db2, $query);
$rowJornal = pg_fetch_array($rs);

print_r($rowJornal);

?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script> 
<style>

.datagrid td,th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	text-align:center;
	font-size: 9px!important;
	color:#666666;
	}
	
	.datagrid a {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	text-align:center;
	font-size: 9px!important;
	color:#666666;
	text-decoration:underline;
	}


</style>
<script language="javascript">
document.getElementById('barMenu').style.display = 'none';
</script>

<div id=divLoading style="display:block">
<table height=100% align=center valign=center>
<tr><td><img src="images/icon32_process.gif" align=absmiddle width=32 height=32 border=0> Generando Listado</td></tr>
</table>
</div>
<form name=frmListadoRetenciones method=post style="margin-left:-160px;">
	<input type="hidden" name="accion" value="recalcular" />
<?

	$query = "

SELECT 

  c.categoria AS \"CategoriaID\",
  c.cargo AS \"CargoID\",
  c.\"Categoria\",
  c.\"Cargo\",
  COUNT(DISTINCT \"Legajo\") AS \"Empleados\",
  AVG(c.\"Basico\") AS \"Basico\",
  AVG(c.\"Antiguedad\") AS \"Antiguedad\",
  MIN(c.\"Bruto\") AS \"BrutoMin\", 
  AVG(c.\"Bruto\") AS \"BrutoProm\", 
  MAX(c.\"Bruto\") AS \"BrutoMax\",
  c.agrupamiento AS \"AgrupamientoID\"

  FROM (

   SELECT
    er.categoria, 
    er.cargo,
    rc.detalle as \"Categoria\",
    rca.detalle as \"Cargo\",
    r.\"Legajo\",
    SUM(CASE WHEN r.\"AliasID\" IN (1, 2, 11, 121, 138, 141) THEN r.\"Haber1\" ELSE 0 END) AS \"Basico\",
    SUM(CASE WHEN r.\"AliasID\" IN (3, 4, 134, 150, 170, 171, 172) THEN r.\"Haber1\" ELSE 0 END) AS \"Antiguedad\",
    SUM(CASE WHEN r.\"AliasID\" IN (15) THEN r.\"Haber1\" + r.\"Haber2\" ELSE 0 END) AS \"Bruto\",
	er.agrupamiento
	
    FROM \"tblEmpleados\" e
    LEFT JOIN \"tblEmpleadosDatos\" ed ON e.\"EmpresaID\" = ed.\"EmpresaID\" AND e.\"SucursalID\" = ed.\"SucursalID\" AND e.\"Legajo\" = ed.\"Legajo\"
    LEFT JOIN \"tblEmpleadosRafam\" er ON e.\"EmpresaID\" = er.\"EmpresaID\" AND e.\"SucursalID\" = er.\"SucursalID\" AND e.\"Legajo\" = er.\"Legajo\"
    LEFT JOIN owner_rafam.categorias rc ON er.agrupamiento = rc.agrupamiento AND er.categoria = rc.categoria AND rc.jurisdiccion = '1110100000'
    LEFT JOIN owner_rafam.cargos rca ON er.agrupamiento = rca.agrupamiento AND er.categoria = rca.categoria AND er.cargo = rca.cargo AND rca.jurisdiccion = '1110100000'
    INNER JOIN \"tblRecibos\" r ON e.\"EmpresaID\" = r.\"EmpresaID\" AND e.\"SucursalID\" = r.\"SucursalID\" AND e.\"Legajo\" = r.\"Legajo\" AND r.\"Fecha\" = '$Periodo' AND r.\"NumeroLiquidacion\" IN (1, 2)

    WHERE (e.\"FechaEgreso\" > '$Periodo' OR e.\"FechaEgreso\" IS NULL)
    AND (ed.\"FechaIngreso\" <= '$Periodo')
    AND r.\"Descripcion\" NOT LIKE 'AJ.%' AND r.\"ConceptoID\" NOT IN (91)
    AND (
       \"AliasID\" IN (1, 2, 11, 121, 138, 141)
    OR \"AliasID\" IN (3, 4, 134, 150, 170, 171, 172)
    OR \"AliasID\" IN (15)
    )

    AND \"AliasID\" NOT IN (27, 19, 22, 35, 29, 30, 31, 7, 36, 32, 174, 25, 143, 144, 37, 8, 34, 74, 23, 26, 23, 75, 81, 156, 78, 156, 69, 70, 78, 84, 73, 146, 80, 5, 6, 13, 90, 169, 163, 118, 66, 79, 130, 149, 166, 9, 56, 10, 82, 67, 165, 50, 51, 137, 86, 155, 145, 168, 14, 126, 24, 139, 140, 164, 124, 125, 129, 132, 131, 12, 158, 142, 114, 122, 123, 46, 47)

    GROUP BY er.categoria, er.cargo, er.agrupamiento, rc.detalle, rca.detalle, r.\"Legajo\"

  ) c
  GROUP BY c.categoria, c.cargo, c.\"Categoria\", c.\"Cargo\", c.agrupamiento
  ORDER BY c.categoria, c.cargo;

";

	$rs = pg_query($db, $query);
	$rs2 = pg_query($db2, $query);

	if (!$rs || !$rs2){
		exit;
	}
?>
<H1> Simulador</H1>
<p>
Para poder simular la nueva masa salarial debe colocar los valores de Sueldo Básico sobre cada una de las categorías y luego presionar el botón
Recalcular que se encuentra al lado, inmediatemente luego se actualizará la fila y permitirá comparar el Antes y el Después. 
Recuerde también que debe actualizar el jornal de 6, 8 y 9hs. 
</p>
<p>
Todas aquellas filas que presenten un indicador naranja serán aquellas en donde el promedio del sueldo bruto nuevo es menor al sueldo bruto anterior, y seguramente deba ser revisado.
</p>
<p>
Se puede ver el detalle de los empleados que conforman la categoría haciendo clic sobre el nombre de la misma.
</p>
<label>Periodo </label> <select name="Periodo" onChange="javascript: this.form.submit();">
	<option value="2015-01-01" <?=($Periodo == '2015-01-01') ? "selected=selected" : "";?>>Enero 2015</option>
</select>
<br />

<label>Basico Jornal 6 Hs</label> <input type="text" name="Jornal6" value="<?=$rowJornal['Jornal6']?>" /><br />
<label>Basico Jornal 8 Hs</label> <input type="text" name="Jornal8" value="<?=$rowJornal['Jornal8']?>" /><br />
<label>Basico Jornal 9 Hs</label> <input type="text" name="Jornal9" value="<?=$rowJornal['Jornal9']?>" /><br /><br />

<input type="submit" value="Actualizar Jornales" />

<div style="float:right; margin: -117px 0px 0px 325px;" id="divMasaSalarial">

</div>	

<br><br>

<table width="100%" border="0" cellpadding="1" cellspacing="1" class="datagrid">
<tr>
	<th>Categoria</th>
    <th>Cargo</th>
    <th>Empleados</th>
    <th>B&aacute;sico</th>
    <th>Antiguedad</th>
    <th>Bruto MIN</th>	
    <th>Bruto PROM</th>
    <th>Bruto MAX</th>
    <th>SB Nuevo</th>
    <th>Bruto MIN</th>	
    <th>Bruto PROM</th>
    <th>Bruto MAX</th>
</tr>

<?
	$i = 0;
	$brutoProm = 0;
	
	$data = array();

	while($row = pg_fetch_array($rs))
		$data[$row['CategoriaID'].$row['CargoID']][0] = $row;

	while($row = pg_fetch_array($rs2))
		$data[$row['CategoriaID'].$row['CargoID']][1] = $row;
	
	foreach($data as $dattum)
	{
		$row = $dattum[0];
		$row2 = $dattum[1];
		
		$color = ($row['BrutoProm'] > $row2[8]) ? "bgcolor=\"#FF9900\"" : "";
		$color2 = ($row['BrutoMax'] > $row2[9]) ? "bgcolor=\"#FFFF00\"" : "";
		?>
		<tr>
        	<td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>&AgrupamientoID=<?=$row['AgrupamientoID']?>"><?=$row['Categoria']?></a></td>
            <td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>&AgrupamientoID=<?=$row['AgrupamientoID']?>"><?=$row['Cargo']?></a></td>
            <td nowrap><?=$row['Empleados']?></td>
            <td nowrap>$ <?=number_format($row['Basico'])?></td>
            <td nowrap>$ <?=number_format($row['Antiguedad'])?></td>
            <td nowrap>$ <?=number_format($row['BrutoMin'])?></td>
            <td nowrap><b>$ <?=number_format($row['BrutoProm'])?></b></td>
            <td nowrap>$ <?=number_format($row['BrutoMax'])?></td>
            <td nowrap><input type="text" size="3" id="id<?=$row['CategoriaID'].$row['CargoID']?>" value="<?=$row2[5]?>"><input type="button" class="recalc" data-text="id<?=$row['CategoriaID'].$row['CargoID']?>" data-categoria="<?=$row['CategoriaID']?>" data-cargoid="<?=$row['CargoID']?>" data-cargo="<?=$row['Cargo']?>" data-periodo="<?=$Periodo?>" value="Recalcular" /></td>
            <td nowrap>$ <?=number_format($row2[7])?></td>
            <td nowrap <?=$color?>><b>$ <?=number_format($row2[8])?></b></td>
            <td nowrap <?=$color2?>>$ <?=number_format($row2[9])?></td>
        </tr>
<?
		$i++;
	}
	
?>
</table>



<h1>Categorías nuevas</h1>

<!-- 
<table width="100%" border="0" cellpadding="1" cellspacing="1" class="datagrid">
<tr>
	<th>Categoria</th>
    <th>Cargo</th>
    <th>Empleados</th>
    <th>B&aacute;sico</th>
    <th>Antiguedad</th>
    <th>Bruto MIN</th>	
    <th>Bruto PROM</th>
    <th>Bruto MAX</th>
    <th>SB Nuevo</th>
    <th>Bruto MIN</th>	
    <th>Bruto PROM</th>
    <th>Bruto MAX</th>
</tr>

<?
	foreach($data as $dattum)
	{
		$row = $dattum[1];
		$row2 = $dattum[0];
		
		if (!$row)
			continue;
		
		$color = ($row['BrutoProm'] > $row2[8]) ? "bgcolor=\"#FF9900\"" : "";
		$color2 = ($row['BrutoMax'] > $row2[9]) ? "bgcolor=\"#FFFF00\"" : "";
		?>
		<tr>
        	<td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>"><?=$row['Categoria']?></a></td>
            <td nowrap><a href="simulador2.php?CategoriaID=<?=$row['CategoriaID']?>&CargoID=<?=$row['CargoID']?>&Periodo=<?=$Periodo?>"><?=$row['Cargo']?></a></td>
            <td nowrap><?=$row['Empleados']?></td>
            <td nowrap>$ <?=number_format($row['Basico'])?></td>
            <td nowrap>$ <?=number_format($row['Antiguedad'])?></td>
            <td nowrap>$ <?=number_format($row['BrutoMin'])?></td>
            <td nowrap><b>$ <?=number_format($row['BrutoProm'])?></b></td>
            <td nowrap>$ <?=number_format($row['BrutoMax'])?></td>
            <td nowrap><input type="text" size="3" id="id<?=$row['CategoriaID'].$row['CargoID']?>" value="<?=$row2[5]?>"><input type="button" class="no_recalc" data-text="id<?=$row['CategoriaID'].$row['Cargo']?>" data-categoria="<?=$row['CategoriaID']?>" data-cargoid="<?=$row['CargoID']?>" data-cargo="<?=$row['Cargo']?>" data-periodo="<?=$Periodo?>" value="NO USAR" /></td>
            <td nowrap>$ <?=number_format($row2[7])?></td>
            <td nowrap <?=$color?>><b>$ <?=number_format($row2[8])?></b></td>
            <td nowrap <?=$color2?>>$ <?=number_format($row2[9])?></td>
        </tr>
<?
		$i++;
	}
	
	pg_close($db);
?>
</table>
-->
 
</form>
<script>
	document.getElementById('divLoading').style.display = 'none';

	function recalc_masa()
	{
		$.ajax({
			url: "simuladorMasaSalarial.php",
			data: {
				Periodo: '<?=$Periodo?>'
			},
			success: function (html) {
				$("#divMasaSalarial").html(html);
			}
		});
	}
	
	$(document).ready(function () {
		recalc_masa();
	});
	
	$(document).on('click', '.recalc', function() {

		var valor = $('[id=\'' + $(this).data('text') + '\']').val();
		var btn = $(this);
		$.ajax({
			url: 'simuladorActualizarCategoria.php',
			data: { 
				CategoriaID: $(this).data('categoria'),
				CargoID: $(this).data('cargoid'),
				Cargo: $(this).data('cargo'),
				Periodo: $(this).data('periodo'),
				Valor: valor
			},
			success: function (html) {
				btn.parent().parent().replaceWith(html);
				recalc_masa();
			}
		});

	});
</script>

<? //include("footer.php"); ?>
