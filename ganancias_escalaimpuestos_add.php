<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;

if ($_POST)
{
	$Mes	= $_POST['Mes'];
	$Anio	= $_POST['Anio'];
	$De		= $_POST['De'];
	$Pagan	= $_POST['Pagan'];
	$Mas	= $_POST['Mas'];

	$err = 0;

	if ($Mes == '')
		$err+=1;

	if ($Anio == '')
		$err+=2;
	
	if ($De == '')
		$err+=4;
	
	if ($Pagan == '')
		$err+=8;
	
	if ($Mas == '')
		$err+=16;
		
	if ($err == 0)
	{
		$rs = pg_query($db, 'INSERT INTO "tblGananciasEscalaDeImpuestos" ("Mes", "Anio", "De", "Pagan", "Mas") VALUES ('.$Mes.', '.$Anio.', '.$De.', '.$Pagan.', '.$Mas.')');	
			
		header("Location: ganancias_escalaimpuestos.php");
		exit;
	}
}
?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Agregar Escala de impuestos</H1>
<br><br>
<form method="post">
<table width="100%" border="0" cellpadding="5" cellspacing="1" class="datagrid">
	<tr>
		<th>Periodo</th>
		<th>De</th>
		<th>Pagan</th>
		<th>Mas</th>
	</tr>
	<tr>
		<td>
			<select name="Mes">
			<?php for ($i=1;$i<=12;$i++){ ?>
				<option value="<?=$i?>" <?=($Mes == $i) ? "selected=selected" : ""?>><?=$i?></option>
			<?php } ?>				
			</select> / 
			<select name="Anio">
				<option value="2017" <?=($Anio == 2017) ? "selected=selected" : ""?>>2017</option>
				<option value="2018" <?=($Anio == 2018) ? "selected=selected" : ""?>>2018</option>
				<option value="2019" <?=($Anio == 2019) ? "selected=selected" : ""?>>2019</option>
				<option value="2020" <?=($Anio == 2020) ? "selected=selected" : ""?>>2020</option>
			</select>
		</td>		
		<td><input type="text" name="De" value="<?=$De?>" size="8"> <?=($err & 4) ? "(*)" : ""?></td>
		<td><input type="text" name="Pagan" value="<?=$Pagan?>" size="8"> <?=($err & 8) ? "(*)" : ""?></td>
		<td><input type="text" name="Mas" value="<?=$Mas?>" size="8"> <?=($err & 16) ? "(*)" : ""?></td>
	</tr>	
</table>
<br>
<div align="right">
	<input type="submit" value="Guardar">
</div>
</form>
<? include("footer.php"); ?>