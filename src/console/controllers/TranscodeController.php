<?php
namespace StudIO\StreamingMedia\console\controllers;

use Craft;
use craft\console\Controller;
use craft\helpers\Console;
use yii\console\ExitCode;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\backends\CloudflareStreamBackend;
use StudIO\StreamingMedia\queue\jobs\TranscodeJob;

class TranscodeController extends Controller
{
  
    /**
     * Transcodes a StreamAsset
     *
     * @return int
     */
    public function actionRun($stream_asset_id): int
    {
        $stream_asset = StreamAsset::find()->anyStatus()->id((int)$stream_asset_id)->one();
        
        if(!$stream_asset){
          $this->stdout('Unable to find stream_asset with ID ' . $stream_asset_id . PHP_EOL, Console::FG_RED);
          return ExitCode::UNSPECIFIED_ERROR;
        }
        
        \craft\helpers\Queue::push(new TranscodeJob($stream_asset->id));

        $this->stdout('Transcode queued succesfully' . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}