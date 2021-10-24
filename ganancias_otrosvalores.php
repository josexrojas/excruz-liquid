<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;

$rs = pg_query($db, '
SELECT * FROM "tblGananciasOtrosValores" ORDER BY "Mes", "Anio" ASC
');

?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Otros Valores</H1>
<br><br>
<a href="ganancias_otrosvalores_add.php">[+] Agregar otro valor</a>
<br><br>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Periodo</th>
		<th>Prima seguro caso muerte</th>
		<th>Gastos de sepelio</th>
		<th>Intereses Hipotecarios</th>
		<th>Acciones</th>
	</tr>
	
<?php 
	while($row = pg_fetch_array($rs))
	{
?>	
	<tr>
		<td><?=$row['Mes'].'/'.$row['Anio']?></td>		
		<td><?=$row['PrimaSeguroCasoMuerte']?></td>
		<td><?=$row['GastosSepelio']?></td>
		<td><?=$row['InteresesHipotecarios']?></td>
		<td>
			<a href="ganancias_otrosvalores_edit.php?OtroValorID=<?=$row['OtroValorID']?>">Editar</a>
		</td>
	</tr>
<?php 
	} 
?>
	
</table>
<? include("footer.php"); ?>
