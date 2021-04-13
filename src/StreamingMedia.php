<?php
/**
 * streaming-media plugin for Craft CMS 3.x
 *
 * A plugin to work with video files and streaming media services like Cloudflare Stream, Coconut, and S3-compliant storage backends.
 *
 * @link      stud-io.com
 * @copyright Copyright (c) 2021 Stud I/O
 */

namespace StudIO\StreamingMedia;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

use StudIO\StreamingMedia\elements\StreamAsset;
use StudIO\StreamingMedia\CraftVariableBehavior;


/**
 * Main Craft Plugin
 * 
 * @author    Stud I/O
 * @package   StreamingMedia
 * @since     0.0.1
 *
 */
class StreamingMedia extends Plugin
{

    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * StreamingMedia::$plugin
     *
     * @var StreamingMedia
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.0.1';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    public static function hasStatuses(): bool
    {
        return true;
    }

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Streamingmedia::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'backend' => \StudIO\StreamingMedia\services\Backend::class,
            'transcode' => \StudIO\StreamingMedia\services\Transcode::class,
        ]);

        self::$plugin = $this;

        $this->_registerCpRoutes();
        $this->_registerTwigExtensions();
        $this->_registerElementTypes();

        /**
         * Logging 
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'streaming-media',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function _registerElementTypes()
    {
      Event::on(Elements::class,
        Elements::EVENT_REGISTER_ELEMENT_TYPES,
        function(RegisterComponentTypesEvent $event) {
            $event->types[] = StreamAsset::class;
        }
      );
    }

    public function _registerTwigExtensions()
    {
      Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $e) {
        /** @var CraftVariable $variable */
        $variable = $e->sender;

        // Attach a behavior:
        $variable->attachBehaviors([
          CraftVariableBehavior::class,
        ]);

        // // Attach a service:
        // $variable->set('serviceId', MyService::class);
      });
    }

    public function _registerCpRoutes()
    {
      Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
        $event->rules = array_merge($event->rules, [
            'streaming-media' => 'streaming-media/stream-assets/index',
            'streaming-media/new' => 'streaming-media/stream-assets/edit',
            'streaming-media/edit/<streamAssetId:\d+>' => 'streaming-media/stream-assets/edit',
        ]);
      });
    }

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }


    // Protected Methods
    // =========================================================================
    
    /*
     * Create settings model
     */
    protected function createSettingsModel()
    {
        return new \StudIO\StreamingMedia\models\Settings();
    }

    protected function settingsHtml()
    {
        return Craft::$app->getView()->renderTemplate(
            'streaming-media/settings',
            [ 'settings' => $this->getSettings() ]
        );
    }

}
