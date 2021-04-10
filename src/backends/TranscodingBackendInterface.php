<?php
namespace StudIO\StreamingMedia\backends;

use StudIO\StreamingMedia\Elements\StreamAsset;

interface TranscodingBackendInterface
{
    public function validate(StreamAsset $stream_asset);
    public function execute(StreamAsset $stream_asset);
    public function get_status(StreamAsset $stream_asset);
}