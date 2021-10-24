

<?php

if($_REQUEST[dire]){

	$filename=$_REQUEST['token'];

	header("Content-type:application/pdf");

}

	$filename=$_REQUEST['token'];

	header("Content-type:application/pdf");
	header("Content-disposition: inline; filename=".$filename.".pdf");
	readfile("/tmp/$filename.pdf");

?>
