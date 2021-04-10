<?php
namespace StudIO\StreamingMedia\services;

use yii\base\Component;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\backends\CloudflareStreamBackend;

class Backend extends Component
{
    public function getBackendSettings(StreamAsset $stream_asset)
    {
        $backends = StreamingMedia::getInstance()->getSettings()->backends;

        $backend_settings = $backends[$stream_asset->transcoding_backend];

        return $backend_settings;
    }

    public function getBackend(StreamAsset $stream_asset)
    {
        $backend_settings = $this->getBackendSettings($stream_asset);

        switch ($backend_settings['backend']) {
            case 'CloudflareStreamBackend':
                $backend = new \StudIO\StreamingMedia\backends\CloudflareStreamBackend($backend_settings['config']);
                break;
            default:
                return false;
                break;
        }

        return $backend;
    }
}