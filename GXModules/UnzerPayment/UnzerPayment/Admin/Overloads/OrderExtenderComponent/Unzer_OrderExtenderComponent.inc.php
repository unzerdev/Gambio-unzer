<?php

class Unzer_OrderExtenderComponent extends Unzer_OrderExtenderComponent_parent
{
    const ORDER_EXTENDER_POSITION = 'below_product_data';

    public function proceed()
    {
        parent::proceed();

        $orderId = (int)$this->v_data_array['GET']['oID'];
        $q = "SELECT * FROM " . TABLE_ORDERS . " WHERE orders_id = " . $orderId;
        $rs = xtc_db_query($q);
        $r = xtc_db_fetch_array($rs);

        if ($r['payment_method'] !== UnzerConstants::MODULE_NAME || empty($r[UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN])) {
            return;
        }

        define('HAS_UNZER_TRANSACTIONS', true);
        $heading = '<img src="' . UnzerConstants::MODULE_PATH_WS . 'Assets/Image/unzer_logo.svg" alt="Unzer" style="height: 18px; margin-top: 3px;" />';
        $body = '<div id="admin-unzer-transactions" 
                          data-load-url="' . xtc_href_link('admin.php', 'do=UnzerTransactions/getTransactions&paymentId=' . $r[UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN] . '&orderId=' . $orderId) . '"
                          data-action-url="' . xtc_href_link('admin.php', 'do=UnzerTransactions/doAction') . '"
                          data-order-id="' . $orderId . '"
                          data-payment-id="' . $r[UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN] . '"
                     ></div>
                     <div id="unzer-debug-container">
                         <button id="unzer-debug-button" class="btn btn-default" style="margin: 10px 0 0 0;">Debug</button>
                         <pre id="unzer-debug-content" style="display: none;"></pre>
                    </div>
                     ';
        $this->addContentToCollection(self::ORDER_EXTENDER_POSITION, $body, $heading);
    }
}