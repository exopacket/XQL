<?php

namespace App\XQL\Cloud;

use Aws\S3\S3Client;

class S3
{

    private string $region;
    private string $key;
    private string $secret;
    private string $bucket;

    private S3Client $client;

    public function __construct()
    {

        $this->region = config("xql.s3.region");
        $this->key = config("xql.s3.key");
        $this->secret = config("xql.s3.secret");
        $this->bucket = config("xql.s3.bucket");

        $credentials = new Aws\Credentials\Credentials($this->key, $this->secret);

        $s3 = new S3Client([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => $credentials
        ]);

    }

}