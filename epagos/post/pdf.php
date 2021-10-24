
<?php
if($_POST[dire]){

$filename=$_POST['token'];

header("Content-type:application/pdf");

}


$filename=$_POST['token'];

header("Content-type:application/pdf");
header("Content-disposition: inline; filename=".$filename.".pdf");
readfile("/tmp/$filename.pdf");

?>

