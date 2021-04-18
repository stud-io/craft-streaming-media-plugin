<?php
namespace StudIO\StreamingMedia;

use Craft;
use yii\base\Behavior;

use StudIO\StreamingMedia\StreamingMedia;
use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\elements\db\StreamAssetQuery;

/**
 * Adds a `craft.stream_assets()` function to the templates (like `craft.entries()`)
 */
class CraftVariableBehavior extends Behavior
{
    public function stream_assets($criteria = null): StreamAssetQuery
    {
        $query = StreamAsset::find();
        if ($criteria) {
            Craft::configure($query, $criteria);
        }
        return $query;
    }
    
    public function playback_token(StreamAsset $streamAsset)
    {
        if (StreamingMedia::getInstance()->is(StreamingMedia::EDITION_PRO)) {
            $backend = StreamingMedia::getInstance()->backend->getStorageBackend($streamAsset);

            return $backend->issue_token($streamAsset, []);    
        }

        // FIXME: Use backend_storage_reference
        return $streamAsset->transcoding_backend_reference;
    }
}