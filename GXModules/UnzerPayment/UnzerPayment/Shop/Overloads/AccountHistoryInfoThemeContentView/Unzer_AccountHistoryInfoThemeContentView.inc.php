<?php

class Unzer_AccountHistoryInfoThemeContentView extends Unzer_AccountHistoryInfoThemeContentView_parent
{
    function _assignPaymentData(){
        parent::_assignPaymentData();
        if($this->order->info['payment_method'] === UnzerConstants::MODULE_NAME){
            $this->set_content_data('PAYMENT_METHOD', (new UnzerOrderHelper())->getUnzerPaymentMethodNameFromOrderId((int)$this->order->info['orders_id']));
        }
    }
}