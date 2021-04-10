<?php
namespace StudIO\StreamingMedia\controllers;

use Craft;
use craft\helpers\ArrayHelper;
use craft\helpers\Json;
use craft\web\Controller;

use yii\web\NotFoundHttpException;
use yii\web\Response;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\queue\jobs\TranscodeJob;

class StreamAssetsController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        $stream_assets = StreamAsset::find()->anyStatus()->all();
        
        Craft::info("Got Stream Assets " . (string)count($stream_assets), __METHOD__);

        return $this->renderTemplate('streaming-media/stream-assets/index', compact('stream_assets'));
    }

    public function actionEdit(int $streamAssetId = null, StreamAsset $streamAsset = null): Response
    {
        $this->requireAdmin();

        $variables = [
        ];

        if ($streamAssetId !== null) {
            if ($streamAsset === null) {
                $streamAsset = StreamAsset::find()->anyStatus()->id($streamAssetId)->one();

                if (!$streamAsset) {
                    throw new NotFoundHttpException('Stream Asset not found');
                }
            }

            $variables['title'] = trim($streamAsset->title) ?: Craft::t('app', 'Edit Stream Asset');
        } else {

            if ($streamAsset === null) {
                $streamAsset = new StreamAsset();
                $streamAsset->draft = true;
            }

            $variables['title'] = Craft::t('app', 'Create a new stream asset');
        }

        $variables['streamAssetId'] = $streamAssetId;
        $variables['streamAsset'] = $streamAsset;

        $backends = StreamingMedia::getInstance()->getSettings()->backends;
        $variables['availableTranscodingBackends'] = [];
        $variables['availableStorageBackends'] = [];

        foreach ($backends as $backendId => $backendConfig) {
          $variables['availableTranscodingBackends'][$backendId] = $backendConfig['title'];
          $variables['availableStorageBackends'][$backendId] = $backendConfig['title'];
        }

        return $this->renderTemplate('streaming-media/stream-assets/_edit', $variables);
    }

    public function actionSave()
    {
        $this->requirePostRequest();
        $this->requireAdmin();
        
        $streamAssetId = $this->request->getBodyParam('streamAssetId');
        
        if ($streamAssetId) {
          $streamAsset = StreamAsset::find()->anyStatus()->id($streamAssetId)->one();
          if (!$streamAsset) {
            throw new BadRequestHttpException("Invalid stream asset ID: $streamAssetId");
          }
        } else {
          $streamAsset = new StreamAsset();
          $streamAsset->draft = true;
        }
        
        $wasDraft = $streamAsset->draft;
        
        $streamAsset->title = $this->request->getBodyParam('title');
        
        // These can only be changed for drafts
        if($wasDraft){
            $streamAsset->source_url = $this->request->getBodyParam('source_url');
            $streamAsset->transcoding_backend = $this->request->getBodyParam('transcoding_backend');
            $streamAsset->storage_backend = $this->request->getBodyParam('storage_backend');
        }

        $streamAsset->draft = $this->request->getBodyParam('draft') == '1' || false;
        $streamAsset->enabled = $this->request->getBodyParam('enabled') || false;

        // Check if we transitioned from non-draft to draft
        if($wasDraft === false && $streamAsset->draft === true){
            // Unset transcode job details
            $streamAsset->transcoding_backend_reference = null; 
            $streamAsset->transcoding_backend_status = null; 

            // Unset storage job details
            $streamAsset->storage_backend_reference = null; 
            $streamAsset->storage_backend_status = null; 
        }

        if (!Craft::$app->elements->saveElement($streamAsset)) {
            $this->setFailFlash(Craft::t('app', 'Couldn’t save the stream asset.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'streamAsset' => $streamAsset,
            ]);

            return null;
        }

        // Check if we transitioned from draft to non-draft
        if($wasDraft === true && $streamAsset->draft !== true){
            // Queue transcode job
            \craft\helpers\Queue::push(new TranscodeJob($streamAsset->id));
        }

        $this->setSuccessFlash(Craft::t('app', 'Stream Asset saved.'));
        return $this->redirectToPostedUrl($streamAsset);
    }


}