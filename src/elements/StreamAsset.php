<?php
namespace StudIO\StreamingMedia\elements;

use craft\base\Element;
use craft\elements\db\ElementQueryInterface;

use StudIO\StreamingMedia\elements\db\StreamAssetQuery;

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

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    public $draft;

    const STATUS_DRAFT = 'draft';
    const STATUS_TR_PENDING = 'transcode_pending';
    const STATUS_TR_INPROG = 'transcode_in_progress';
    const STATUS_TR_FAILED = 'transcode_failed';
    const STATUS_TR_COMPLETED = 'transcode_completed';
        # STATUS_DISABLED
        # STATUS_ENABLED

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => ['label' => 'Draft', 'color' => '407294'],
            self::STATUS_TR_PENDING => ['label' => 'Pending transcode', 'color' => '407294'],
            self::STATUS_TR_INPROG => ['label' => 'Transcode in progress', 'color' => '013056'],
            self::STATUS_TR_FAILED => ['label' => 'Failed transcode', 'color' => 'ad2828'],
        ];
    }

    public function getStatus()
    {        
        if ($this->draft) {
          return SELF::STATUS_DRAFT;
        }

        if ($this->transcoding_backend_status === null) {
          return SELF::STATUS_TR_PENDING;
        }
        
        if ($this->transcoding_backend_status === SELF::STATUS_TR_INPROG) {
          return SELF::STATUS_TR_INPROG;
        }

        if ($this->transcoding_backend_status === SELF::STATUS_TR_FAILED) {
          return SELF::STATUS_TR_FAILED;
        }
        
        return parent::getStatus();
    }

    protected function statusCondition(string $status)
    {
        switch ($status) {
            case 'transcode_pending':
                return ['transcode_pending' => true];
            case 'transcode_failed':
                return ['transcode_failed' => true];
            default:
                // call the base method for `enabled` or `disabled`
                return parent::statusCondition($status);
        }
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
     * @var string Backend-specific reference to stream asset in transcoding backend (probably some sort of Video or Job ID)
     */
    public $transcoding_backend_reference;
    
    public $transcoding_backend_status;
    
    
    /**
     * @var int ID of configured backend to be used for storage, if not same as transcoding backend
     */
    public $storage_backend;

    /**
     * @var string Backend-specific reference to stream asset in storage backend (probably some sort of Video ID or storage path)
     */
    public $storage_backend_reference;
   
    public $storage_backend_status;
    
    public function afterSave(bool $isNew)
    {
      \Craft::trace($isNew);
      if ($isNew) {
          \Craft::$app->db->createCommand()
              ->insert('{{%stream_assets}}', [
                  'id' => $this->id,
                  'draft' => true,
                  'source_url' => $this->source_url,
                  'transcoding_backend' => $this->transcoding_backend,
                  'storage_backend' => $this->storage_backend,
              ])
              ->execute();
      } else {
          \Craft::$app->db->createCommand()
              ->update('{{%stream_assets}}', [
                'draft' => $this->draft,
                'source_url' => $this->source_url,
                'transcoding_backend' => $this->transcoding_backend,
                'transcoding_backend_reference' => $this->transcoding_backend_reference,
                'transcoding_backend_status' => $this->transcoding_backend_status,
                'storage_backend' => $this->storage_backend,
                'storage_backend_reference' => $this->storage_backend_reference,
                'storage_backend_status' => $this->storage_backend_status,
            ], ['id' => $this->id])
              ->execute();
      }

      parent::afterSave($isNew);
    }

    /*
     * Tie in StreamAsset Element Query
     */
    public static function find(): ElementQueryInterface
    {
        return new StreamAssetQuery(static::class);
    }
    

    //////////////////////////////////
    // Control Panel Implementation //
    //////////////////////////////////

    /**
     * @inheritdoc
     */
    public function getIsEditable(): bool
    {
        // return \Craft::$app->user->checkPermission('edit-product:'.$this->getType()->id);
        return true;
    }

    public function getCpEditUrl()
    {
        return 'streaming-media/'.$this->id;
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'title' => \Craft::t('app', 'Title'),
            // 'price' => \Craft::t('plugin-handle', 'Price'),
            // 'currency' => \Craft::t('plugin-handle', 'Currency'),
        ];
    }
    
   /*
     * Return editor HTML
     */
    public function getEditorHtml(): string
    {
        $html = \Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => \Craft::t('app', 'Title'),
                'siteId' => $this->siteId,
                'id' => 'title',
                'name' => 'title',
                'value' => $this->title,
                'errors' => $this->getErrors('title'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }
}