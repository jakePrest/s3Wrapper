<?php
include "s3Wrapper.php";

$s3 = new s3Wrapper("config.txt");
//$s3->listObjects();
//$s3->setBucket("jake-s3-wrapper-test");
//$s3->destroyThisBucket();
//$myObjects = $s3->listObjects();

$s3->createBucket("jake-s3-wrapper-test");
$s3->uploadFile("newFileYup!", "");
//$return = $s3->doesBucketExist("jake-s3-wrapper-test");
var_dump($return);
?>
