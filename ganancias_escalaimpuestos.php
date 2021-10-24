<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;

$rs = pg_query($db, '
SELECT * FROM "tblGananciasEscalaDeImpuestos" ORDER BY "Mes", "Anio" ASC
');

?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Escala de impuestos</H1>
<br><br>
<a href="ganancias_escalaimpuestos_add.php">[+] Agregar escala de impuesto</a>
<br><br>
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Periodo</th>
		<th>De</th>
		<th>Pagan</th>
		<th>Mas</th>
		<th>Acciones</th>
	</tr>
	
<?php 
	while($row = pg_fetch_array($rs))
	{
?>	
	<tr>
		<td><?=$row['Mes'].'/'.$row['Anio']?></td>		
		<td><?=$row['De']?></td>
		<td><?=$row['Pagan']?></td>
		<td><?=$row['Mas']?></td>
		<td>
			<a href="ganancias_escalaimpuestos_edit.php?EscalaImpuestoID=<?=$row['EscalaImpuestoID']?>">Editar</a>
		</td>
	</tr>
<?php 
	} 
?>
	
</table>
<? include("footer.php"); ?>
