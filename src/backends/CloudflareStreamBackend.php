<?php
namespace StudIO\StreamingMedia\backends;

use Craft;

use BoldBrush\Cloudflare\API;

use StudIO\StreamingMedia\Elements\StreamAsset;

// class CloudflareStreamBackend implements TranscodingBackendInterface, StorageBackendInterface
class CloudflareStreamBackend
{
    public $api;

    public function __construct($backend_config)
    {
        $this->api = API\Factory::make(
            $backend_config['account_id'],
            $backend_config['api_token'],
            'api_token'
        );
    }

    public function execute(StreamAsset $stream_asset)
    {
        $stream = $this->api->copy(trim($stream_asset->source_url), $stream_asset->title);
        
        if($stream instanceof \BoldBrush\Cloudflare\API\Response\Error){
            return $stream;
        }

        $stream_asset->transcoding_backend_reference = $stream->uid;
        $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_INPROG;;
        
        $save_result = Craft::$app->elements->saveElement($stream_asset);

        return $save_result;
    }

    public function get_status(StreamAsset $stream_asset)
    {
        $info = $this->get_info($stream_asset);
        Craft::info("Transcode info: " . json_encode($info), __METHOD__);

        if($info->status->state === 'ready'){
          $stream_asset->transcoding_backend_status = StreamAsset::STATUS_TR_COMPLETED;
          $save_result = Craft::$app->elements->saveElement($stream_asset);

          return true;
        }

        return false;
    }

    public function get_info(StreamAsset $stream_asset)
    {
        if($stream_asset->transcoding_backend_reference != null)
          return $this->api->getStream($stream_asset->transcoding_backend_reference);

        return false;
    }
}