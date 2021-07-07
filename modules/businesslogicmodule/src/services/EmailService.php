<?php
/**
 * Business Logic module for Craft CMS 3.x
 *
 * Custom Site Logic
 *
 * @link      https://stenvdb.be
 * @copyright Copyright (c) 2020 Sten Van den Bergh
 */

namespace modules\businesslogicmodule\services;

use craft\elements\Entry;
use modules\businesslogicmodule\BusinessLogicModule;

use Craft;
use craft\base\Component;

/**
 * EmailService Service
 *
 * All of your module’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other modules can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Sten Van den Bergh
 * @package   PickupRulesModule
 * @since     1.0.0
 */
class EmailService extends Component
{
    // Public Methods
    // =========================================================================

    public function sendEntryNotification(Entry $entry, $keys)
    {
        $shopGlobalSet = Craft::$app->globals->getSetByHandle('general');
        $notificationEmail = $shopGlobalSet->notificationEmail;
        $mails = [];

        foreach ($keys as $key) {
            $mail = Craft::$app->getMailer()->composeFromKey($key, [
                'submission' => $entry
            ]);
            $mail->setTo($notificationEmail);
            array_push($mails, $mail);
        }

        Craft::$app->getMailer()->sendMultiple($mails);
    }
}
