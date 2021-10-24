<? include("header.php"); ?><br />
<?php 

if (!($db = Conectar()))
	exit;
	
if ($_POST)
{
	$Mes				= $_POST['Mes'];
	$Anio				= $_POST['Anio'];
	$Desde				= $_POST['Desde'];
	$Hasta				= $_POST['Hasta'];
	$MNI				= $_POST['MNI'];
	$DeduccionEspecial	= $_POST['DeduccionEspecial'];
	$Conyuge			= $_POST['Conyuge'];
	$Hijos				= $_POST['Hijos'];
	$OtrasCargas		= $_POST['OtrasCargas'];

	$err = 0;
	
	if ($Mes == '')
		$err+=1;

	if ($Anio == '')
		$err+=2;
	
	if ($Desde== '')
		$err+=4;
	
	if ($Hasta== '')
		$err+=8;
	
	if ($MNI == '')
		$err+=16;
	
	if ($DeduccionEspecial == '')
		$err+=32;

	if ($Conyuge == '')
		$err+=64;
	
	if ($Hijos == '')
		$err+=128;
	
	if ($OtrasCargas == '')
		$err+=256;
		
	if ($err == 0)
	{
		$rs = pg_query($db, 'INSERT INTO "tblGananciasDeducciones" 
		("Mes",
"Anio",
"Desde",
"Hasta",
"MNI",
"DeduccionEspecial",
"Conyuge",
"Hijos",
"OtrasCargas") 
		VALUES (
'.$Mes.',
'.$Anio.',
'.$Desde.',
'.$Hasta.',
'.$MNI.',
'.$DeduccionEspecial.',
'.$Conyuge.',
'.$Hijos.',
'.$OtrasCargas.'
)');	
			
		header("Location: ganancias_deducciones.php");
		exit;
	}
}
?>

<H1 style="display:inline"><img src="images/icon64_Recibos.gif" width="64" height="64" align="absmiddle" /> Agregar Deducci&oacute;n</H1>
<br><br>
<form method="post">
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
	</tr>
	<tr>
		<td nowrap>
			<select name="Mes">
			<?php for ($i=0;$i<=13;$i++){ ?>
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
		<td><input type="text" name="Desde" value="<?=$Desde?>" size="8"> <?=($err & 4) ? "(*)" : ""?></td>
		<td><input type="text" name="Hasta" value="<?=$Hasta?>" size="8"> <?=($err & 8) ? "(*)" : ""?></td>
		<td><input type="text" name="MNI" value="<?=$MNI?>" size="8"> <?=($err & 16) ? "(*)" : ""?></td>
		<td><input type="text" name="DeduccionEspecial" value="<?=$DeduccionEspecial?>" size="8"> <?=($err & 32) ? "(*)" : ""?></td>
		<td><input type="text" name="Conyuge" value="<?=$Conyuge?>" size="8"> <?=($err & 64) ? "(*)" : ""?></td>
		<td><input type="text" name="Hijos" value="<?=$Hijos?>" size="8"> <?=($err & 128) ? "(*)" : ""?></td>
		<td><input type="text" name="OtrasCargas" value="<?=$OtrasCargas?>" size="8"> <?=($err & 256) ? "(*)" : ""?></td>
	</tr>	
</table>
<br>
<div align="right">
	<input type="submit" value="Guardar">
</div>
</form>
<? include("footer.php"); ?>