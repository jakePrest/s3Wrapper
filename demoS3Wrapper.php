<?php
include "s3Wrapper.php";

$s3 = new s3Wrapper("config.txt");
//$s3->listObjects();
$s3->setBucket("jake-s3-wrapper-test");

$myObjects = $s3->listObjects();

//$s3->createBucket("jake-create-brand-new-bucket-werd");
var_dump($myObjects);
?>
