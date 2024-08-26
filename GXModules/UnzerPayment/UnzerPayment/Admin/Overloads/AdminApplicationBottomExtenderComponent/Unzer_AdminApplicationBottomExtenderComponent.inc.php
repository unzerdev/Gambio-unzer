<?php

class Unzer_AdminApplicationBottomExtenderComponent extends Unzer_AdminApplicationBottomExtenderComponent_parent
{
    public function proceed()
    {
        parent::proceed();
        if (defined('HAS_UNZER_TRANSACTIONS')) {
            echo '<script src="' . DIR_WS_CATALOG . 'GXModules/UnzerPayment/UnzerPayment/Admin/Assets/js/admin_transactions.js"></script>
                  <link rel="stylesheet" href="' . DIR_WS_CATALOG . 'GXModules/UnzerPayment/UnzerPayment/Admin/Assets/css/admin_transactions.css" />';
        }
    }
}