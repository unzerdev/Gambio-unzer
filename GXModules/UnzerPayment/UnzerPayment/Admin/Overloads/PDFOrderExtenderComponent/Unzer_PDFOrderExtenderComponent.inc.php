<?php

class Unzer_PDFOrderExtenderComponent extends Unzer_PDFOrderExtenderComponent_parent
{
    public function extendOrderInfo($order_info)
    {
        $order_info = parent::extendOrderInfo($order_info);
        if ($this->v_data_array['order']->info['payment_method'] === UnzerConstants::MODULE_NAME) {
            UnzerConfigHelper::initTexts();
            $orderId = (int)$this->v_data_array['GET']['oID'];
            $paymentMethod = (new UnzerOrderHelper())->getUnzerPaymentMethodNameFromOrderId($orderId);
            if (!empty($paymentMethod)) {
                $order_info['PAYMENT_METHOD'][1] = $paymentMethod;
            }
            $unzerOrderHelper = new UnzerOrderHelper();
            $unzerPaymentId = $unzerOrderHelper->getPaymentIdFromOrderId($orderId);
            if (!empty($unzerPaymentId)) {
                $payment = (new UnzerApiHelper())->fetchPayment($unzerPaymentId);
                if($payment !== null){
                    if($instructions = $unzerOrderHelper->formatPaymentInstructions($payment)) {

                        $order_info['PAYMENT_INSTRUCTIONS'] = [
                            UnzerConfigHelper::getStringConstant('UNZER_PAYMENT_INSTRUCTIONS'),
                            $instructions
                        ];
                    }
                }
            }
        }
        return $order_info;
    }
}