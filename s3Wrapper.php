<?php
require_once "aws/aws-autoloader.php";
use Aws\S3\S3Client;
use Aws\S3\Model\ClearBucket;


class s3Wrapper
{
    // property declaration
    private $client = NULL;
    private $bucket = NULL;

    // method declaration

    function __construct($pathToConfigFile, $bucketName) {
        $configFile = fopen($pathToConfigFile, "r") or die("Unable to open file!");
        $access = fgets($configFile);
        $secret = fgets($configFile);

        $this->client = S3Client::factory(array(
          'key'      => $access,
          'secret'   => $secret
        ));

        $this->bucket = $bucketName;
    }

    function setBucket($bucketName) {
        $this->bucket = $bucketName;
    }

    function getBucketName() {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }

        return $this->bucket;
    }

    function doesBucketExist($bucketName) {
        return $this->client->doesBucketExist($bucketName);
    }

    function doesObjectExist($objectName) {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }
        return $this->client->doesObjectExit($this->bucket, $objectName);
    }

    function destroyThisBucket() {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }

        $clear = new ClearBucket($this->client, $this->bucket);
        $clear->clear();

        // Delete the bucket
        $this->client->deleteBucket(array('Bucket' => $this->bucket));

        // Wait until the bucket is not accessible
        $this->client->waitUntil('BucketNotExists', array('Bucket' => $this->bucket));
    }

    function uploadFile($fileName, $pathToFile) {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }

        $result = $this->client->putObject(array(
            'Bucket'  => $this->bucket,
            'Key'     => $fileName,
            'Body'    => fopen($pathToFile, 'r+')
        ));

        $this->client->waitUntil('ObjectExists', array(
            'Bucket' => $this->bucket,
            'Key'    => $fileName
        ));
    }

    function downloadFile($fileName, $destinationPath) {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }
        $result = $this->client->getObject(array(
            'Bucket' => $this->bucket,
            'Key'    => $fileName,
            'SaveAs' => $destinationPath
        ));
    }

    function uploadDirectory($directory, $keyPrefix) {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }
        $this->client->uploadDirectory($directory, $this->bucket, $keyPrefix);
    }

    function downloadBucketToDirectory($pathToDirectory) {
        if ($this->bucket == NULL) {
            echo "Bucket must be set to use this method. Use \$s3WrapperObject->setBucket(\$bucketName)\n";
            exit(1);
        }
        $this->client->downloadBucket($pathToDirectory, $this->bucket);
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
        $this->bucket = $bucketName;
        return $success;
    }
}
?>
