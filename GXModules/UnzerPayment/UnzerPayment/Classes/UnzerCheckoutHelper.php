<?php

namespace UnzerPayment\UnzerPayment\Classes;

use UnzerApiHelper;
use UnzerConfigHelper;
use UnzerLogger;

class UnzerCheckoutHelper
{
    protected UnzerApiHelper $apiHelper;
    public UnzerLogger $logger;

    public function __construct()
    {
        $this->apiHelper = new UnzerApiHelper();
        $this->logger = new UnzerLogger();
    }

    public function getAvailablePaymentMethods($noConstraints = false, $selectedMethod = null, int $cacheTime = 3600): array
    {
        $paymentMethods = $this->apiHelper->getAllPaymentMethods($cacheTime);
        $paymentMethodConfig = UnzerConfigHelper::getPaymentMethodsConfiguration();
        $result = [];
        foreach ($paymentMethods as $paymentMethod) {
            if(in_array($paymentMethod->type, \UnzerConstants::DISABLED_PAYMENT_METHODS)){
                continue;
            }
            if(!$noConstraints) {
                $supportedCurrencies = (array)$paymentMethod->supports[0]->currency;
                if (!in_array($_SESSION['currency'], $supportedCurrencies)) {
                    continue;
                }
            }
            $code = strtolower($paymentMethod->type);
            $label = UnzerConfigHelper::getPaymentMethodName($code);
            if (empty($label)) {
                continue;
            }

            if(!$noConstraints && isset($paymentMethodConfig[$code]['status']) && (int)$paymentMethodConfig[$code]['status'] === 0){
                continue;
            }

            $iconBasePath = 'GXModules/UnzerPayment/UnzerPayment/Assets/Icons/' . $code;
            if (file_exists(DIR_FS_CATALOG . $iconBasePath . '.svg')) {
                $iconPath = DIR_WS_CATALOG . $iconBasePath . '.svg';
            } elseif (file_exists($iconBasePath . '.png')) {
                $iconPath = DIR_WS_CATALOG . $iconBasePath . '.png';
            } else {
                $iconPath = '';
            }


            $result[] = [
                'id' => 'unzer_' . $code,
                'originalCode' => $code,
                'module' => $label,
                'description' => '',
                'logo_url' => $iconPath,
                'logo_alt' => $label,
                'selection' => '<input type="radio" name="payment" value="unzer_' . $code . '" '.($code === $selectedMethod?'checked="checked"':'').' />',
            ];
        }

        return $result;
    }

    public static function doErrorRedirect(string $publicMessage = ''): void
    {
        (new UnzerLogger())->warning('checkout error redirect', ['trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)]);
        if(!empty($publicMessage)){
            $_SESSION['gm_error_message'] = $publicMessage;
        }
        xtc_redirect(xtc_href_link('checkout_payment.php', 'payment_error='.\UnzerConstants::MODULE_NAME, 'SSL'));
    }
}