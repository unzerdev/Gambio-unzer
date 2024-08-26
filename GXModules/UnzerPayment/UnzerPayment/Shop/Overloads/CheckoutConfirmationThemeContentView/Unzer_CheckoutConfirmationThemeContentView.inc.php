<?php

class Unzer_CheckoutConfirmationThemeContentView extends Unzer_CheckoutConfirmationThemeContentView_parent{
    public function prepare_data(){
        $data = parent::prepare_data();
        if($this->coo_order->info['payment_method'] === UnzerConstants::MODULE_NAME && !empty($_SESSION['unzer_payment_method'])){
            $this->content_array['PAYMENT_METHOD'] = UnzerConfigHelper::getPaymentMethodName($_SESSION['unzer_payment_method']);
        }
    }
}