<?php
/**
 * Business Logic module for Craft CMS 3.x
 *
 * Custom Site Logic
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\businesslogicmodule\assetbundles\businesslogicmodule;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Sten Van den Bergh
 * @package   BusinessLogicModule
 * @since     1.0.0
 */
class BusinessLogicModuleAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@modules/businesslogicmodule/assetbundles/businesslogicmodule/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/BusinessLogicModule.js',
        ];

        $this->css = [
            'css/BusinessLogicModule.css',
        ];

        parent::init();
    }
}
