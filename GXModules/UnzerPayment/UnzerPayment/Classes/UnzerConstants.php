<?php

class UnzerConstants {
    const MODULE_NAME = 'unzer';
    const MODULE_PATH_FS = DIR_FS_CATALOG.'GXModules/UnzerPayment/UnzerPayment/';
    const MODULE_PATH_WS = DIR_WS_CATALOG.'GXModules/UnzerPayment/UnzerPayment/';

    const ORDER_TABLE_PAYMENT_ID_COLUMN = 'unzer_payment_id';
    const ORDER_TABLE_PAYMENT_METHOD_COLUMN = 'unzer_payment_method';
    const ORDER_TABLE_PAYMENT_METHOD_LABEL_COLUMN = 'unzer_payment_method_label';
    const DISABLED_PAYMENT_METHODS = ['giropay', 'PIS', 'bancontact'];
    const MODULE_VERSION = '1.0.1';

    const LOG_LEVEL_DEBUG = 'debug';
    const LOG_LEVEL_ERROR = 'error';

    const META_DATA_SHOP_TYPE = 'Gambio';
    const META_DATA_PLUGIN_TYPE = 'unzerdev/gambio';
}