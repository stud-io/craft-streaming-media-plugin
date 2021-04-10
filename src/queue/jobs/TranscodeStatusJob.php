<?php

namespace StudIO\StreamingMedia\queue\jobs;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;

class TranscodeStatusJob extends \craft\queue\BaseJob
{

    public $stream_asset_id;

    public function __construct($stream_asset_id)
    {
        $this->stream_asset_id = $stream_asset_id;

        parent::__construct();
    }

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $streamAsset = StreamAsset::find()->anyStatus()->id($this->stream_asset_id)->one();
        
        $backend = StreamingMedia::getInstance()->backend->getBackend($streamAsset);

        try {
            $streamAsset = StreamAsset::find()->anyStatus()->id($this->stream_asset_id)->one();
              
            $transcode_done = $backend->get_status($streamAsset);
            \Craft::info("Transcode status: $transcode_done", __METHOD__);

            if(!$transcode_done){
                sleep(30);
                \craft\helpers\Queue::push(new TranscodeStatusJob($streamAsset->id));
            }

        } catch (\Throwable $e) {
            // Don’t let an exception block the queue
            \Craft::warning("Something went wrong: {$e->getMessage()}", __METHOD__);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return 'Update stream asset transcode status';
    }
}