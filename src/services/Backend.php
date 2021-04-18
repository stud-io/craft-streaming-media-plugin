<?php
namespace StudIO\StreamingMedia\services;

use yii\base\Component;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;

class Backend extends Component
{
    public function getTranscodeBackendSettings(StreamAsset $stream_asset)
    {
        return $this->getBackendSettings($stream_asset->transcoding_backend);
    }

    public function getStorageBackendSettings(StreamAsset $stream_asset)
    {
        return $this->getBackendSettings($stream_asset->storage_backend);
    }
    
    private function getBackendSettings($backend)
    {
        $backends = StreamingMedia::getInstance()->getSettings()->backends;

        $backend_settings = $backends[$backend];

        return $backend_settings;
    }

    public function getTranscodeBackend(StreamAsset $stream_asset)
    {
        $backend_settings = $this->getTranscodeBackendSettings($stream_asset);
        
        return $this->getBackend($backend_settings);
    }
    
    public function getStorageBackend(StreamAsset $stream_asset)
    {
        $backend_settings = $this->getStorageBackendSettings($stream_asset);
        
        return $this->getBackend($backend_settings);
    }

    private function getBackend($backend_settings)
    {
        switch ($backend_settings['backend']) {
            case 'CloudflareStreamBackend':
                $backend = new \StudIO\StreamingMedia\backends\CloudflareStreamBackend($backend_settings['config']);
                break;
            case 'CoconutBackend':
                $backend = new \StudIO\StreamingMedia\backends\CoconutBackend($backend_settings['config']);
                break;
            case 'GenericS3Backend':
                $backend = new \StudIO\StreamingMedia\backends\GenericS3Backend($backend_settings['config']);
                break;
            default:
                return false;
                break;
        }

        return $backend;
    }
}