<?php

class Unzer_PrintOrderThemeContentView extends Unzer_PrintOrderThemeContentView_parent
{
    function add_order_data(){
        parent::add_order_data();
        if($this->coo_order->info['payment_method'] === UnzerConstants::MODULE_NAME){
            $this->content_array['PAYMENT_METHOD'] = (new UnzerOrderHelper())->getUnzerPaymentMethodNameFromOrderId($this->order_id);
        }
    }
}