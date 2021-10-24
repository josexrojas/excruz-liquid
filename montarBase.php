<?php
include ('header.php');

//en la segunda fecha se coloca un mes posterior al que se quiere montar

//$comando = "createdb Sueldos201908 -USueldos;  gunzip -c /home/sitios/liquid/SQLBackup/2019-09-01.sql.gz | pg_restore -Ft -c | psql -dSueldos201908 -USueldos";

$resultado = system($comando,$error);

if(!$resultado){
  echo "error";
}else{
  echo "montada";
}
?>
