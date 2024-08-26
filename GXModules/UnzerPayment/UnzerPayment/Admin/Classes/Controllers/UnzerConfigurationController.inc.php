<?php


use UnzerPayment\UnzerPayment\Classes\UnzerCheckoutHelper;
use UnzerSDK\Constants\TransactionTypes;

class UnzerConfigurationController extends AdminHttpViewController
{
    protected function init()
    {
        UnzerConfigHelper::initTexts();
    }

    public function actionDefault()
    {
        $title = new NonEmptyStringType(UnzerConfigHelper::getStringConstant('UNZER_CONFIGURATION_TITLE'));
        $template = new ExistingFile(new NonEmptyStringType(UnzerConstants::MODULE_PATH_FS . 'Admin/Html/unzer_configuration.html'));
        $data = $this->getTemplateData();
        $assets = $this->getAssetData();

        return MainFactory::create(AdminLayoutHttpControllerResponse::class, $title, $template, $data, $assets);
    }

    public function actionWebhooks()
    {
        $title = new NonEmptyStringType(UnzerConfigHelper::getStringConstant('UNZER_WEBHOOKS_TITLE'));
        $template = new ExistingFile(new NonEmptyStringType(UnzerConstants::MODULE_PATH_FS . 'Admin/Html/unzer_webhooks.html'));
        $assets = $this->getAssetData();
        $unzerApiHelper = new UnzerApiHelper();

        $data = MainFactory::create(KeyValueCollection::class, [
            'urls' => [
                'webhookAdd' => xtc_href_link('admin.php', 'do=UnzerConfiguration/addWebhook'),
                'webhookDelete' => xtc_href_link('admin.php', 'do=UnzerConfiguration/deleteWebhook'),
                'logo' => UnzerConstants::MODULE_PATH_WS . 'Assets/Image/unzer_logo.svg',
            ],
            'isRegistered' => $unzerApiHelper->isWebhookRegistered(),
            'webhooks' => $unzerApiHelper->fetchWebhooks(),
        ]);

        return MainFactory::create(AdminLayoutHttpControllerResponse::class, $title, $template, $data, $assets);
    }

    public function actionAddWebhook()
    {
        $unzerApiHelper = new UnzerApiHelper();
        $unzerApiHelper->addCurrentWebhook();
        return MainFactory::create('RedirectHttpControllerResponse',
            xtc_href_link('admin.php', 'do=UnzerConfiguration/webhooks'));
    }

    public function actionDeleteWebhook()
    {
        $unzerApiHelper = new UnzerApiHelper();
        $unzerApiHelper->deleteWebhook($this->_getPostData('webhookId'));
        return MainFactory::create('RedirectHttpControllerResponse',
            xtc_href_link('admin.php', 'do=UnzerConfiguration/webhooks'));
    }

    protected function getTemplateData()
    {
        $paymentMethods = (new UnzerCheckoutHelper())->getAvailablePaymentMethods(true, null, 0);
        foreach ($paymentMethods as &$paymentMethod) {
            $paymentMethod['canAuthorize'] = UnzerApiHelper::canPaymentMethodAuthorize($paymentMethod['originalCode']);
        }


        return MainFactory::create(KeyValueCollection::class,
            [
                'urls' => [
                    'saveConfiguration' => xtc_href_link('admin.php', 'do=UnzerConfiguration/saveConfiguration'),
                    'logo' => UnzerConstants::MODULE_PATH_WS . 'Assets/Image/unzer_logo.svg',
                ],
                'paymentMethods' => $paymentMethods,
                'currentConfig' => UnzerConfigHelper::getPaymentMethodsConfiguration(),
                'options' => [
                    'transactionTypes' => [
                        [
                            'value' => TransactionTypes::CHARGE,
                            'label' => UnzerConfigHelper::getStringConstant('UNZER_OPTION_LABEL_CAPTURE'),
                        ],
                        [
                            'value' => TransactionTypes::AUTHORIZATION,
                            'label' => UnzerConfigHelper::getStringConstant('UNZER_OPTION_LABEL_AUTHORIZE'),
                        ],
                    ],

                ],
            ]
        );
    }

    protected function getAssetData()
    {
        return MainFactory::create(AssetCollection::class,
            [
                MainFactory::create(Asset::class, UnzerConstants::MODULE_PATH_WS . 'Admin/Assets/css/admin.css'),
            ]
        );
    }


    public function actionSaveConfiguration()
    {
        if (!empty($this->_getPostData('configuration'))) {
            UnzerConfigHelper::upsertConfigValue('MODULE_PAYMENT_UNZER_PAYMENT_METHODS_CONFIGURATION', json_encode($this->_getPostData('configuration')));
        }
        return MainFactory::create('RedirectHttpControllerResponse',
            xtc_href_link('modules.php', 'set=payment&module=' . UnzerConstants::MODULE_NAME));
    }
}
