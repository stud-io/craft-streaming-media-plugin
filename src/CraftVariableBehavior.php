<?php
namespace StudIO\StreamingMedia;

use Craft;
use yii\base\Behavior;

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
}