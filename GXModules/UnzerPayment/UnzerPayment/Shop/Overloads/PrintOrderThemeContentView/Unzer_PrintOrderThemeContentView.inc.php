<?php

class Unzer_PrintOrderThemeContentView extends Unzer_PrintOrderThemeContentView_parent
{
    function add_order_data(){
        parent::add_order_data();
        if($this->coo_order->info['payment_method'] === UnzerConstants::MODULE_NAME){
            $this->content_array['PAYMENT_METHOD'] = (new UnzerOrderHelper())->getUnzerPaymentMethodNameFromOrderId($this->order_id);

            $unzerOrderHelper = new UnzerOrderHelper();
            $unzerPaymentId = $unzerOrderHelper->getPaymentIdFromOrderId($this->order_id);
            if (!empty($unzerPaymentId)) {
                $payment = (new UnzerApiHelper())->fetchPayment($unzerPaymentId);
                if($payment !== null){
                    if($instructions = $unzerOrderHelper->formatPaymentInstructions($payment)) {
                        $this->content_array['PAYMENT_INSTRUCTIONS'] = '<div><b>'.UnzerConfigHelper::getStringConstant('UNZER_PAYMENT_INSTRUCTIONS').'</b></div>'.nl2br($instructions);
                    }
                }
            }


        }
    }
}