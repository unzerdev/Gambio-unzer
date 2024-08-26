<?php

class Unzer_CheckoutConfirmationContentControl extends Unzer_CheckoutConfirmationContentControl_parent{
    public function proceed(){
        $selectedPayment = $this->v_data_array['POST']['payment'] ?? '';
        if(str_starts_with($selectedPayment, 'unzer_')){
            $this->v_data_array['POST']['payment'] = 'unzer';
            $_POST['payment'] = 'unzer';
            $_SESSION['unzer_payment_method'] = substr($selectedPayment, 6);
        }
        parent::proceed();
    }
}