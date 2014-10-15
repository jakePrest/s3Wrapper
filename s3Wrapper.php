<?php
require_once "aws/aws-autoloader.php";
use Aws\S3\S3Client;
class s3Wrapper
{
    // property declaration
    private $client = NULL;
    private $bucket = NULL;

    // method declaration

    function __construct($pathToConfigFile) {
        $configFile = fopen($pathToConfigFile, "r") or die("Unable to open file!");
        $access = fgets($configFile);
        $secret = fgets($configFile);

        $this->client = S3Client::factory(array(
          'key'      => $access,
          'secret'   => $secret
        ));

        $this->bucket = $bucketIn;
    }

    function setBucket($bucketName) {
        $this->bucket = $bucketName;
    }

    function getBucketName() {
        return $this->bucket;
    }

    function listObjects() {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }

        $objectArray = array();
        $o_iter = $this->client->getIterator('ListObjects', array(
            'Bucket' => $this->bucket
        ));

        foreach ($o_iter as $o) {
            array_push($objectArray, array("key" => $o['Key'], "size" => $o['Size'], "lastModified" => $o['LastModified']));
        }

        return $objectArray;
    }

    function listBuckets() {
        $bucketArray = array();

        $bucketList = $this->client->listBuckets();
        foreach ($bucketList['Buckets'] as $bucket) {
          array_push($bucketArray, $bucket['Name']);
        }

        return $bucketArray;
    }

    function createBucket($bucketName) {
      $success = $this->client->createBucket(array('Bucket' => $bucketName));
      $this->client->waitUntil('BucketExists', array('Bucket' => $bucketName));
      return $success;
    }


}
?>
