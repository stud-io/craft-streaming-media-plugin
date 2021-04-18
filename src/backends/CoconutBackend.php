<?php
namespace StudIO\StreamingMedia\backends;

use Craft;
use Yii;

// use Coconut\Job;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\Elements\StreamAsset;

// class CloudflareStreamBackend implements TranscodingBackendInterface, StorageBackendInterface
class CoconutBackend
{
    public $api_key;

    public $rsa_key;

    public function __construct($backend_config)
    {
        $this->api_key = $backend_config['api_token'];
    }

    private function prepareS3Url($storage_backend_config)
    {
        return implode([
            's3://',
            $storage_backend_config['access_key'],
            ':',
            $storage_backend_config['secret_key'],
            '@',
            $storage_backend_config['bucket_name'],
            '?',
            'host=',
            $storage_backend_config['s3_url']
        ]); 
    }

    public function execute(StreamAsset $stream_asset)
    {
        $storage_backend_settings = StreamingMedia::getInstance()->backend->getStorageBackendSettings($stream_asset);

        $rand = Yii::$app->security->generateRandomString(12);

        $s3_url = $this->prepareS3Url($storage_backend_settings['config']);

        Craft::info($s3_url);
        
        $job = \Coconut\Job::create([
            'api_key' => $this->api_key,
            'source' => trim($stream_asset->source_url),
            'webhook' => 'http://stud-io.com/webhook/coconut?id=' . $stream_asset->id,
            'outputs' => [
                'httpstream' => "$s3_url, dash=$rand/enc-dash, hls=$rand/enc-hls",
            ]
        ]);

        if($job->status !== "processing")
            return $job;
        
        $stream_asset->transcoding_backend_reference = $job->id;
        $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_INPROG;;
        
        $save_result = Craft::$app->elements->saveElement($stream_asset);

        return $save_result;
    }

    public function get_status(StreamAsset $stream_asset)
    {
        $info = $this->get_info($stream_asset);
        Craft::info("Transcode info: " . json_encode($info), __METHOD__);

        if($info->status === 'completed'){
          $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_COMPLETED;

          $stream_asset->storage_backend_status = StreamAsset::STATUS_ST_READY;
          $stream_asset->storage_backend_reference = json_encode($info->output_urls->httpstream);

          $save_result = Craft::$app->elements->saveElement($stream_asset);
          
          return $save_result;
        }

        return false;
    }

    public function get_info(StreamAsset $stream_asset)
    {
        if($stream_asset->transcoding_backend_reference != null){
            $job = \Coconut\Job::get($stream_asset->transcoding_backend_reference, ['api_key' => $this->api_key]);
            return $job;
        }

        return false;
    }
}