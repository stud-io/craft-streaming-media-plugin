<?php
namespace StudIO\StreamingMedia\fields;

use Craft;
use craft\db\Table as DbTable;
use craft\elements\db\EntryQuery;
use craft\elements\Entry;
use craft\fields\BaseRelationField;

use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\elements\db\StreamAssetQuery;

class StreamAssets extends BaseRelationField
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Stream Assets';
    }

    /**
     * @inheritdoc
     */
    protected static function elementType(): string
    {
        return StreamAsset::class;
    }

    /**
     * @inheritdoc
     */
    public static function defaultSelectionLabel(): string
    {
        return 'Add a Stream Asset';
    }

}
