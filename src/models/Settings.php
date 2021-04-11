<?php

namespace StudIO\StreamingMedia\models;

use craft\base\Model;

class Settings extends Model
{
    public $backends = [
      1 => [
        'title' => 'Default Cloudflare Stream Backend',
        'backend' => 'CloudflareStreamBackend',
        'config' => [
          'api_token' => 'some-fancy-api-token',
          'account_id' => 'some-fancy-api-token',
          'email' => 'some@where.email'
        ]
      ],
      2 => [
        'title' => 'Default Coconut Backend',
        'backend' => 'CoconutBackend',
        'config' => [
          'api_token' => 'some-fancy-api-token'
        ]
      ],
      3 => [
        'title' => 'Default Generic Object Storage Backend',
        'backend' => 'GenericObjectStorageBackend',
        'config' => [
          'bucket_name' => 'some-bucket',
          's3_url' => 'https://some-s3-api/',
          'access_key' => 'some-fancy-api-token',
          'secret_key' => 'some-fancy-api-token',
        ]
      ],
    ];
}