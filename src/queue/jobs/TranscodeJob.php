<?php

namespace StudIO\StreamingMedia\queue\jobs;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;

class TranscodeJob extends \craft\queue\BaseJob
{

    public $use_status_job = true;

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
            $transcode_response = $backend->execute($streamAsset);
            
            // As we're in a try block, throw an exception if execute didn't return true, and as such has failed
            if($transcode_response !== true)
                throw new \Exception(json_encode($transcode_response));

            // Whether to run TranscodeStatus jobs to check if the transcode has finished,
            // or to simply keep this job running, and updating it's progress.
            if($this->use_status_job === true){
                \craft\helpers\Queue::push(new TranscodeStatusJob($streamAsset->id));
            }else{
                $transcode_done = false;

                while($transcode_done === false){
                    sleep(1);
        
                    $streamAsset = StreamAsset::find()->anyStatus()->id($this->stream_asset_id)->one();
                    
                    $transcode_done = $backend->get_status($streamAsset);

                    // FIXME: Update progress of this job to progress of transcode while we're at it

                    \Craft::info("Transcode status: $transcode_done", __METHOD__);
                }    
            }

        } catch (\Throwable $e) {
            // Don’t let an exception block the queue
            \Craft::warning("Something went wrong: {$e->getMessage()}", __METHOD__);

            $streamAsset->transcoding_backend_status = StreamAsset::STATUS_TR_FAILED;
            $save_result = \Craft::$app->elements->saveElement($streamAsset);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return 'Transcode a stream asset';
    }
}