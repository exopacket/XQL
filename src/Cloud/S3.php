<?php

namespace App\XQL\Cloud;

use Aws\S3\S3Client;

class S3
{

    private string $bucket;

    private S3Client $s3;

    public function __construct()
    {

        $region = config("xql.s3.region");
        $key = config("xql.s3.key");
        $secret = config("xql.s3.secret");
        $this->bucket = config("xql.s3.bucket");

        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => [
                'key'    => $key,
                'secret' => $secret,
            ],
        ]);

    }

    public function put(string $key, string $content) {
        $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'Body' => $content
        ]);
    }

    public function get(string $key) {
       $res = $this->s3->getObject([
            'Bucket' => $this->bucket,
            'Key' => $key
        ]);
       return $res['Body'];
    }

}
