<?php
/**
 * Business Logic module for Craft CMS 3.x
 *
 * Custom Site Logic
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\businesslogicmodule;

use modules\businesslogicmodule\assetbundles\businesslogicmodule\BusinessLogicModuleAsset;

use Craft;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterEmailMessagesEvent;
use craft\events\TemplateEvent;
use craft\helpers\ElementHelper;
use craft\i18n\PhpMessageSource;
use craft\services\SystemMessages;
use craft\web\View;

use modules\businesslogicmodule\services\EmailService;

use putyourlightson\snaptcha\events\ValidateFieldEvent;
use putyourlightson\snaptcha\services\SnaptchaService;

use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
 * Class BusinessLogicModule
 *
 * @author    Sten Van den Bergh
 * @package   BusinessLogicModule
 * @since     1.0.0
 *
 */
class BusinessLogicModule extends Module
{
    // Static Properties
    // =========================================================================

    /**
     * @var BusinessLogicModule
     */
    public static $instance;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        Craft::setAlias('@modules/businesslogicmodule', $this->getBasePath());
        $this->controllerNamespace = 'modules\businesslogicmodule\controllers';

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$id]) && !isset($i18n->translations[$id.'*'])) {
            $i18n->translations[$id] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => 'en-US',
                'basePath' => '@modules/businesslogicmodule/translations',
                'forceTranslation' => true,
                'allowOverrides' => true,
            ];
        }

        // Base template directory
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) {
            if (is_dir($baseDir = $this->getBasePath().DIRECTORY_SEPARATOR.'templates')) {
                $e->roots[$this->id] = $baseDir;
            }
        });

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

        self::setComponents([
            'emailService' => EmailService::class,
        ]);

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                View::class,
                View::EVENT_BEFORE_RENDER_TEMPLATE,
                function (TemplateEvent $event) {
                    try {
                        Craft::$app->getView()->registerAssetBundle(BusinessLogicModuleAsset::class);
                    } catch (InvalidConfigException $e) {
                        Craft::error(
                            'Error registering AssetBundle - '.$e->getMessage(),
                            __METHOD__
                        );
                    }
                }
            );
        }

        Event::on(Entry::class, Entry::EVENT_AFTER_SAVE, function(ModelEvent $event) {
            /** @var Entry $entry */
            $entry = $event->sender;

            if (ElementHelper::isDraftOrRevision($entry)) {
                return;
            }

            if ($event->isNew
                && $entry->getSection()->handle == 'contactSubmissions' // is in 'contactSubmissions' section
                && !$entry->propagating // not during propagating (avoid batch propagating)
                && !$entry->resaving // not during resaving (avoid batch resaving)
            ) {
                $this->emailService->sendEntryNotification($entry, ['business-logic_contact-admin-notification', 'business-logic_contact-client-notification']);
            }
        });

        if (Craft::$app->plugins->isPluginInstalled('snaptcha') && Craft::$app->plugins->isPluginEnabled('snaptcha')) {
            Event::on(SnaptchaService::class, SnaptchaService::EVENT_BEFORE_EXCLUDE_CONTROLLER_ACTIONS, function (ValidateFieldEvent $event) {
                $excludeControllerActions = $event->excludeControllerActions;
                array_push(
                    $excludeControllerActions,
                    'commerce/cart/update-cart',
                    'commerce/payments/pay',
                    'mailchimp-subscribe/audience/subscribe',
                    'users/login',
                    'users/send-password-reset-email',
                    'users/set-password',
                    'cm-lists/subscribe'
                );
                $excludeControllerActions = array_unique($excludeControllerActions);
                $event->excludeControllerActions = $excludeControllerActions;
            });
        }

        // This line calls a private method to register a new email message in Craft
        $this->_registerEmailMessages();

        Craft::info(
            Craft::t(
                'business-logic-module',
                '{name} module loaded',
                ['name' => 'Business Logic']
            ),
            __METHOD__
        );
    }

    private function _registerEmailMessages()
    {
        Event::on(SystemMessages::class, SystemMessages::EVENT_REGISTER_MESSAGES, function(RegisterEmailMessagesEvent $event) {
            $event->messages = array_merge($event->messages, [
                [
                    'key' => 'business-logic_contact-admin-notification',
                    'heading' => Craft::t('business-logic-module', 'contact-admin-notification_heading'),
                    'subject' => Craft::t('business-logic-module', 'contact-admin-notification_subject'),
                    'body' => Craft::t('business-logic-module', 'contact-admin-notification_body'),
                ],
                [
                    'key' => 'business-logic_contact-client-notification',
                    'heading' => Craft::t('business-logic-module', 'contact-client-notification_heading'),
                    'subject' => Craft::t('business-logic-module', 'contact-client-notification_subject'),
                    'body' => Craft::t('business-logic-module', 'contact-client-notification_body'),
                ]
            ]);
        });
    }

    // Protected Methods
    // =========================================================================
}
