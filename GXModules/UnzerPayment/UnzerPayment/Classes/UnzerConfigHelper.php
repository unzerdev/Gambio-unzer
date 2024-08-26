<?php

use UnzerSDK\Constants\TransactionTypes;

class UnzerConfigHelper
{
    protected static $isTextInitialized = false;
    public static function initTexts(){
        if(self::$isTextInitialized){
            return;
        }
        $languageTextManager = MainFactory::create_object('LanguageTextManager', [], true);
        $languageTextManager->init_from_lang_file(UnzerConstants::MODULE_NAME, $_SESSION['languages_id']);
        self::$isTextInitialized = true;
    }
    public static function getConstant($constantName)
    {
        return defined($constantName) ? constant($constantName) : null;
    }

    public static function getStringConstant($constantName): string
    {
        return defined($constantName) ? (string)constant($constantName) : '';
    }

    public static function getConfigValueFromDb($key): ?string
    {
        $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
        $q = "SELECT `value` FROM `gx_configurations` WHERE `key` = 'configuration/$key'";
        $result = xtc_db_query($q);

        if (xtc_db_num_rows($result) === 0) {
            return null;
        }

        $row = xtc_db_fetch_array($result);
        return (string)$row['value'];
    }

    public static function createConfigValue($key, $value, $type = null, $sortOrder = 0, $lastModified = 'now()')
    {
        if (self::getConfigValueFromDb($key) !== null) {
            return;
        }
        xtc_db_perform(
            'gx_configurations',
            [
                '`key`' => "configuration/$key",
                '`value`' => $value,
                '`type`' => $type === null ? 'null' : $type,
                '`sort_order`' => $sortOrder,
                '`last_modified`' => $lastModified,
            ]
        );
    }


    public static function upsertConfigValue(string $key, mixed $json_encode): void
    {
        $existing = self::getConfigValueFromDb($key);
        if ($existing === null) {
            self::createConfigValue($key, $json_encode);
        } else {
            xtc_db_perform(
                'gx_configurations',
                [
                    '`value`' => $json_encode,
                ],
                "update",
                "`key` = '" . xtc_db_input("configuration/$key") . "'"
            );
        }
    }

    public static function getPublicKey(): string
    {
        return self::getStringConstant('MODULE_PAYMENT_UNZER_PUBLIC_KEY');
    }

    public static function getPrivateKey(): string
    {
        return self::getStringConstant('MODULE_PAYMENT_UNZER_PRIVATE_KEY');
    }

    public static function getPaymentMethodsConfiguration(): array
    {
        $configKey = 'MODULE_PAYMENT_UNZER_PAYMENT_METHODS_CONFIGURATION';
        $configValue = self::getConstant('MODULE_PAYMENT_UNZER_PAYMENT_METHODS_CONFIGURATION');
        $return = json_decode($configValue, true);
        return empty($return) ? [] : $return;
    }

    public static function getPaymentMethodTransactionType($type)
    {
        $config = self::getPaymentMethodsConfiguration();
        $type = strtolower($type);
        return $config[$type]['transactionType'] ?: TransactionTypes::CHARGE;
    }

    public static function getPaymentMethodName(string $code): string
    {
        return self::getStringConstant('MODULE_PAYMENT_UNZER_PAYMENT_METHOD_LABEL_' . strtoupper($code));
    }

    public static function getWebhookUrl():string{
        if(function_exists('xtc_catalog_href_link')){
            return xtc_catalog_href_link('shop.php', 'do=UnzerWebhook', 'SSL');
        }else {
            return xtc_href_link('shop.php', 'do=UnzerWebhook', 'SSL');
        }
    }

}