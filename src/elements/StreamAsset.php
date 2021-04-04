<?php
namespace StudIO\StreamingMedia\elements;

use craft\base\Element;

class StreamAsset extends Element
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Stream Asset';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return 'Stream Assets';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return true;
    }
    
    /**
     * @var string Public url to media source file
     */
    public $source_url;

    /**
     * @var int ID of configured backend to be used for transcoding
     */
    public $transcoding_backend;

    /**
     * @var int ID of configured backend to be used for storage, if not same as transcoding backend
     */
    public $storage_backend;

    public function afterSave(bool $isNew)
    {
      if ($isNew) {
          \Craft::$app->db->createCommand()
              ->insert('{{%stream_assets}}', [
                  'id' => $this->id,
                  'source_url' => $this->source_url,
                  'backend_transcoding' => $this->transcoding_backend,
                  'backend_storage' => $this->storage_backend,
              ])
              ->execute();
      } else {
          \Craft::$app->db->createCommand()
              ->update('{{%stream_assets}}', [
                'source_url' => $this->source_url,
                'backend_transcoding' => $this->transcoding_backend,
                'backend_storage' => $this->storage_backend,
            ], ['id' => $this->id])
              ->execute();
      }

      parent::afterSave($isNew);
    }
}