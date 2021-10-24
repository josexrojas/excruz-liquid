<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;

$rs = pg_query($db, '
SELECT * FROM "tblGananciasDeducciones" ORDER BY "Mes", "Anio" ASC
');

?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Deducciones</H1>
<br><br>
<a href="ganancias_deducciones_add.php">[+] Agregar deducci&oacute;n</a>
<br><br>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Periodo</th>
		<th>Desde</th>
		<th>Hasta</th>
		<th>MNI</th>
		<th>Deducci&oacute;n Especial</th>
		<th>C&oacute;nyuge</th>
		<th>Hijos</th>
		<th>Otras cargas</th>
		<th>Acciones</th>
	</tr>
	
<?php 
	while($row = pg_fetch_array($rs))
	{
?>	
	<tr>
		<td><?=$row['Mes'].'/'.$row['Anio']?></td>		
		<td><?=$row['Desde']?></td>
		<td><?=$row['Hasta']?></td>
		<td><?=$row['MNI']?></td>
		<td><?=$row['DeduccionEspecial']?></td>
		<td><?=$row['Conyuge']?></td>
		<td><?=$row['Hijos']?></td>
		<td><?=$row['OtrasCargas']?></td>
		
		<td>
			<a href="ganancias_deducciones_edit.php?DeduccionID=<?=$row['DeduccionID']?>">Editar</a>
		</td>
	</tr>
<?php 
	} 
?>
	
</table>
<? include("footer.php"); ?>
