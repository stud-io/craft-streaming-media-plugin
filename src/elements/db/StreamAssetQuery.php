<?php
namespace StudIO\StreamingMedia\elements\db;

use craft\db\Query;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;
use StudIO\StreamingMedia\elements\StreamAsset;

class StreamAssetQuery extends ElementQuery
{
    // public $price;
    // public $currency;

    // public function price($value)
    // {
    //     $this->price = $value;

    //     return $this;
    // }

    // public function currency($value)
    // {
    //     $this->currency = $value;

    //     return $this;
    // }

    protected function beforePrepare(): bool
    {
        // join in the products table
        $this->joinElementTable('stream_assets');

        // select the price column
        $this->query->select([
            'stream_assets.draft',
            'stream_assets.source_url',
            'stream_assets.transcoding_backend',
            'stream_assets.transcoding_backend_reference',
            'stream_assets.transcoding_backend_status',
            'stream_assets.storage_backend',
            'stream_assets.storage_backend_reference',
            'stream_assets.storage_backend_status',
        ]);

        // if ($this->price) {
        //     $this->subQuery->andWhere(Db::parseParam('products.price', $this->price));
        // }

        // if ($this->currency) {
        //     $this->subQuery->andWhere(Db::parseParam('products.currency', $this->currency));
        // }

        return parent::beforePrepare();
    }
}