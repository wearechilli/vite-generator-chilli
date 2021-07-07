<?php
/**
 * ShopAdjusters module for Craft CMS 3.x
 *
 * Manage custom adjusters for Craft Commerce
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\shopadjustersmodule;

use craft\commerce\services\OrderAdjustments;
use craft\events\RegisterComponentTypesEvent;
use modules\shopadjustersmodule\assetbundles\shopadjustersmodule\ShopAdjustersModuleAsset;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\TemplateEvent;
use craft\i18n\PhpMessageSource;
use craft\web\View;

use modules\shopadjustersmodule\models\TaxDeducterAdjuster;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
 * Class ShopAdjustersModule
 *
 * @author    Sten Van den Bergh
 * @package   ShopAdjustersModule
 * @since     1.0.0
 *
 */
class ShopAdjustersModule extends Module
{
    // Static Properties
    // =========================================================================

    /**
     * @var ShopAdjustersModule
     */
    public static $instance;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/shopadjustersmodule', $this->getBasePath());
        $this->controllerNamespace = 'modules\shopadjustersmodule\controllers';

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$id]) && !isset($i18n->translations[$id.'*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@modules/shopadjustersmodule/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }

        if (Craft::$app->plugins->isPluginInstalled('commerce') && Craft::$app->plugins->isPluginEnabled('commerce')) {
            // Base template directory
            Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
                if (is_dir($baseDir = $this->getBasePath() . DIRECTORY_SEPARATOR . 'templates')) {
                    $e->roots[$this->id] = $baseDir;
                }
            });
        }

        // Set this as the global instance of this module class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$instance = $this;

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    try {
                        Craft::$app->getView()->registerAssetBundle(ShopAdjustersModuleAsset::class);
                    } catch (InvalidConfigException $e) {
                        Craft::error(
                            'Error registering AssetBundle - '.$e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            );
        }

        Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $event) {
            $event->types[] = TaxDeducterAdjuster::class;
        });

        Craft::info(
            Craft::t(
                'shop-adjusters-module',
                '{name} module loaded',
                ['name' => 'ShopAdjusters']
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
}
