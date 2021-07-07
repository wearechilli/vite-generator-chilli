<?php
/**
 * ShopAdjusters module for Craft CMS 3.x
 *
 * Manage custom adjusters for Craft Commerce
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\shopadjustersmodule\assetbundles\shopadjustersmodule;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Sten Van den Bergh
 * @package   ShopAdjustersModule
 * @since     1.0.0
 */
class ShopAdjustersModuleAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@modules/shopadjustersmodule/assetbundles/shopadjustersmodule/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/ShopAdjustersModule.js',
        ];

        $this->css = [
            'css/ShopAdjustersModule.css',
        ];

        parent::init();
    }
}
