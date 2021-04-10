<?php
namespace StudIO\StreamingMedia\backends;

use StudIO\StreamingMedia\Elements\StreamAsset;

interface StorageBackendInterface
{
    public function get_info(StreamAsset $stream_asset);
    public function update_info(StreamAsset $stream_asset);
    public function issue_token(StreamAsset $stream_asset);
}