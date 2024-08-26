<?php

use UnzerPayment\UnzerPayment\Classes\UnzerCheckoutHelper;

class Unzer_CheckoutPaymentModulesThemeContentView extends Unzer_CheckoutPaymentModulesThemeContentView_parent
{
    public function prepare_data()
    {
        parent::prepare_data();


        $this->expandUnzerPaymentMethods();


    }

    protected function expandUnzerPaymentMethods()
    {
        $paymentModules = $this->get_content_array()['module_content'] ?? [];
        if(empty($paymentModules)) {
            return;
        }
        if (($position = array_search('unzer', array_column($paymentModules, 'id'))) !== false) {
            $unzerPaymentMethods = (new UnzerCheckoutHelper())->getAvailablePaymentMethods(false, $_SESSION['unzer_payment_method']??null);
            array_splice($paymentModules, $position, 1, $unzerPaymentMethods);
            $this->set_content_data('module_content', $paymentModules);
        }
    }
}