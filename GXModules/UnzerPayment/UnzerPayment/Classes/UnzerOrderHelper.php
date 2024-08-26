<?php

use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Constants\ShippingTypes;
use UnzerSDK\Constants\TransactionTypes;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\EmbeddedResources\Address;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;
use UnzerSDK\Resources\Metadata;
use UnzerSDK\Resources\PaymentTypes\Paypage;
use UnzerSDK\Unzer;

class UnzerOrderHelper
{
    protected Unzer $unzer;
    public UnzerLogger $logger;

    public function __construct()
    {
        $this->unzer = new Unzer(UnzerConfigHelper::getPrivateKey());
        $this->logger = new UnzerLogger();
    }

    public function getUnzerPayPage(order_ORIGIN $order, ?string $selectedPaymentMethod = null)
    {
        $basket = $this->getUnzerBasket($order);
        $customer = $this->getUnzerCustomer($order);
        $payPage = new Paypage($basket->getTotalValueGross(), $basket->getCurrencyCode(), xtc_href_link('checkout_process.php', '', 'SSL'));
        $threatMetrixId = md5(HTTPS_SERVER) . '_' . $order->info['orders_id'];
        $isCustomerRegistered = $this->isCustomerRegistered((int)$order->customer['id']);
        $payPage
            ->setAdditionalAttribute('riskData.threatMetrixId', $threatMetrixId)
            ->setAdditionalAttribute('riskData.customerGroup', 'NEUTRAL')
            ->setAdditionalAttribute('riskData.customerId', $customer->getCustomerId())
            ->setAdditionalAttribute('riskData.confirmedAmount', $this->getCustomersTotalOrderAmount((int)$order->customer['id']))
            ->setAdditionalAttribute('riskData.confirmedOrders', $this->getCustomersTotalNumberOfOrders((int)$order->customer['id']))
            ->setAdditionalAttribute('riskData.registrationLevel', $isCustomerRegistered ? '1' : '0')
            ->setAdditionalAttribute('riskData.registrationDate', $this->getCustomersRegistrationDate((int)$order->customer['id']));

        if(!$isCustomerRegistered){
            $payPage->setAdditionalAttribute('disabledCOF', 'card,paypal,sepa-direct-debit');
        }

        $payPage->setOrderId($order->info['orders_id']);
        $metaData = $this->getMetaData($payPage, $order->info['orders_id']);

        $transactionType = TransactionTypes::CHARGE;
        if ($selectedPaymentMethod) {
            $apiHelper = new UnzerApiHelper();
            foreach ($apiHelper->getAllPaymentMethods() as $paymentMethod) {
                if (strtolower($paymentMethod->type) !== $selectedPaymentMethod) {
                    $payPage->addExcludeType($paymentMethod->type);
                }
            }

            if (UnzerConfigHelper::getPaymentMethodTransactionType($selectedPaymentMethod) === TransactionTypes::AUTHORIZATION) {
                $transactionType = TransactionTypes::AUTHORIZATION;
            }
        }

        if ($transactionType === TransactionTypes::AUTHORIZATION) {
            $return = $this->unzer->initPayPageAuthorize($payPage, $customer, $basket, $metaData);
        } else {
            $return = $this->unzer->initPayPageCharge($payPage, $customer, $basket, $metaData);
        }

        $this->logger->debug('paypage data', [$return->expose()]);

        return $return;
    }

    protected function getMetaData(Paypage $payPage, $orderId = null): Metadata
    {

        $metaData = new Metadata();
        $metaData
            ->setShopType(UnzerConstants::META_DATA_SHOP_TYPE)
            ->setShopVersion(gm_get_conf('INSTALLED_VERSION'))
            ->addMetadata('pluginType', UnzerConstants::META_DATA_PLUGIN_TYPE)
            ->addMetadata('pluginVersion', UnzerConstants::MODULE_VERSION);

        if ($orderId !== null) {
            $metaData->addMetadata('orderId', $orderId);
        }
        return $metaData;
    }

    public function getUnzerBasket(order_ORIGIN $order)
    {
        $basket = (new Basket())
            ->setTotalValueGross($order->info['pp_total'])
            ->setOrderId($order->info['orders_id'])
            ->setCurrencyCode($order->info['currency']);

        $basketItems = [];

        foreach ($order->products as $orderItem) {
            $basketItem = (new BasketItem())
                ->setTitle($orderItem['name'])
                ->setQuantity($orderItem['qty'])
                ->setType(BasketItemTypes::GOODS)
                ->setAmountPerUnitGross($orderItem['price'])
                ->setVat($orderItem['tax']);
            $basketItems[] = $basketItem;
        }

        if ($order->info['pp_shipping']) {
            $basketItem = (new BasketItem())
                ->setTitle($order->info['shipping_method'])
                ->setQuantity(1)
                ->setType(BasketItemTypes::SHIPMENT)
                ->setAmountPerUnitGross($order->info['pp_shipping'])
                ->setVat(0);
            $basketItems[] = $basketItem;
        }

        $q = "SELECT * FROM coupon_gv_redeem_track WHERE orders_id = " . (int)$order->info['orders_id'];
        $rs = xtc_db_query($q);
        while ($r = xtc_db_fetch_array($rs)) {
            $basketItem = (new BasketItem())
                ->setTitle($r['coupon_code'])
                ->setQuantity(1)
                ->setType(BasketItemTypes::VOUCHER)
                ->setAmountDiscountPerUnitGross(abs($r['amount']))
                ->setVat(0);
            $basketItems[] = $basketItem;
        }

$q = "SELECT * FROM " . TABLE_ORDERS_TOTAL . " WHERE orders_id = " . (int)$order->info['orders_id'] . " AND class = 'ot_coupon'";
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            $basketItem = (new BasketItem())
                ->setTitle(trim($r['title'], ':'))
                ->setQuantity(1)
                ->setType(BasketItemTypes::VOUCHER)
                ->setAmountDiscountPerUnitGross(abs($r['value']))
                ->setVat(0);
            $basketItems[] = $basketItem;
        }


        $totalLeft = $basket->getTotalValueGross();
        foreach ($basketItems as $basketItem) {
            $totalLeft -= $basketItem->getAmountPerUnitGross() * $basketItem->getQuantity();
            $totalLeft += $basketItem->getAmountDiscountPerUnitGross() * $basketItem->getQuantity();
        }

        if (number_format($totalLeft, 2) !== '0.00') {
            if ($totalLeft < 0) {
                $basketItem = (new BasketItem())
                    ->setTitle('---')
                    ->setQuantity(1)
                    ->setType(BasketItemTypes::VOUCHER)
                    ->setAmountDiscountPerUnitGross(round($totalLeft * -1, 2))
                    ->setVat(0);
                $basketItems[] = $basketItem;
            } else {
                $basketItem = (new BasketItem())
                    ->setTitle('---')
                    ->setQuantity(1)
                    ->setType(BasketItemTypes::GOODS)
                    ->setAmountPerUnitGross(round($totalLeft, 2));
                $basketItems[] = $basketItem;
            }
        }
        $basket->setBasketItems($basketItems);

        return $basket;
    }

    public function getUnzerCustomer(order_ORIGIN $order)
    {
        $customerId = $order->customer['id'];
        try {
            $customer = $this->unzer->fetchCustomerByExtCustomerId('gx4-' . $customerId);
        } catch (Exception $e) {
            // no worries, we cover this by creating a new customer
        }

        if (empty($customer)) {
            $customer = new Customer();
            $customer->setCustomerId('gx4-' . $customerId);
        }

        $customer
            ->setFirstname($order->customer['firstname'] ?: '')
            ->setLastname($order->customer['lastname'] ?: '')
            ->setPhone($order->customer['telephone'] ?: '')
            ->setCompany($order->customer['company'] ?: '')
            ->setEmail($order->customer['email_address'] ?: '');

        $this->setDateOfBirth($customer, $order);
        $this->setAddresses($customer, $order);
        $this->logger->debug('customer data', [$customer->expose()]);

        if ($customer->getId()) {
            try {
                $this->unzer->updateCustomer($customer);
            } catch (Exception $e) {
                $this->logger->warning('update customer failed: ' . $e->getMessage(), [$customer->expose()]);
            }
        }

        return $customer;
    }

    protected function setDateOfBirth(Customer $customer, order_ORIGIN $order): void
    {
        $q = "SELECT customers_dob FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . (int)$order->customer['id'];
        $rs = xtc_db_query($q);
        $r = xtc_db_fetch_array($rs);
        $dob = $r['customers_dob'];
        if (!empty($dob)) {
            try {
                $date = new DateTime($dob);
                if ($date->format('Y') > 1900) {
                    $customer->setBirthDate($date->format('Y-m-d'));
                }
            } catch (Exception $e) {
                $this->logger->warning('Could not parse date of birth: ' . $dob, [$e->getMessage()]);
            }
        }
    }

    protected function setAddresses(Customer $customer, order_ORIGIN $order)
    {
        $shippingType = ShippingTypes::EQUALS_BILLING;
        if ($order->delivery && xtc_address_format($order->billing['format_id'], $order->billing, false, '', '') !== xtc_address_format($order->delivery['format_id'], $order->delivery, false, '', '')) {
            $shippingType = ShippingTypes::DIFFERENT_ADDRESS;
        }

        $billingAddress = (new Address())
            ->setName($order->billing['name'])
            ->setStreet($order->billing['street_address'])
            ->setZip($order->billing['postcode'])
            ->setCity($order->billing['city'])
            ->setState($order->billing['state'])
            ->setCountry($order->billing['country_iso_2']);

        if ($order->delivery) {
            $shippingAddress = (new Address())
                ->setName($order->delivery['name'])
                ->setStreet($order->delivery['street_address'])
                ->setZip($order->delivery['postcode'])
                ->setCity($order->delivery['city'])
                ->setState($order->delivery['state'])
                ->setCountry($order->delivery['country_iso_2'])
                ->setShippingType($shippingType);
        } else {
            $shippingAddress = $billingAddress;
            $shippingAddress->setShippingType(ShippingTypes::EQUALS_BILLING);
        }

        $customer
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress);
    }

    public function setOrderStatus($orders_id, $orders_status, string $comment = ''): void
    {
        if (empty($orders_id)) {
            $this->logger->warning('setOrderStatus: orders_id is empty', ['orders_status' => $orders_status, 'comment' => $comment, 'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)]);
            return;
        }
        $insertId = new IdType((int)$orders_id);

        if (empty($orders_status)) {
            if (empty($comment)) {
                //nothing to do
                return;
            }
            /** @var OrderReadServiceInterface $orderReadService */
            $orderReadService = StaticGXCoreLoader::getService('OrderRead');
            /** @var OrderInterface $order */
            $order = $orderReadService->getOrderById($insertId);
            $orders_status = $order->getStatusId();
        }

        /** @var OrderWriteServiceInterface $orderWriteService */
        $orderWriteService = StaticGXCoreLoader::getService('OrderWrite');
        $orderWriteService->updateOrderStatus($insertId,
            new IntType((int)$orders_status),
            new StringType($comment),
            new BoolType(false));
    }

    public function writePaymentIdAndPaymentMethod(mixed $orderId, string $paymentId, string $paymentMethod = '')
    {
        xtc_db_perform(TABLE_ORDERS, [
            UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN => $paymentId,
            UnzerConstants::ORDER_TABLE_PAYMENT_METHOD_COLUMN => $paymentMethod,
            UnzerConstants::ORDER_TABLE_PAYMENT_METHOD_LABEL_COLUMN => UnzerConfigHelper::getPaymentMethodName($paymentMethod) ?: $paymentMethod
        ], 'update', 'orders_id = ' . (int)$orderId);
    }

    public function getUnzerPaymentMethodNameFromOrderId(int $orderId): string
    {
        $q = "SELECT " . UnzerConstants::ORDER_TABLE_PAYMENT_METHOD_LABEL_COLUMN . " FROM " . TABLE_ORDERS . " WHERE orders_id = " . $orderId;
        $rs = xtc_db_query($q);
        $r = xtc_db_fetch_array($rs);
        return (string)$r[UnzerConstants::ORDER_TABLE_PAYMENT_METHOD_LABEL_COLUMN];
    }

    public function getOrderIdFromPaymentId(string $paymentId): ?int
    {
        $q = "SELECT orders_id FROM " . TABLE_ORDERS . " WHERE " . UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN . " = '" . xtc_db_input($paymentId) . "'";
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            return (int)$r['orders_id'];
        } else {
            return null;
        }
    }

    public function getPaymentIdFromOrderId(int $orderId): ?string
    {
        $q = "SELECT " . UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN . " FROM " . TABLE_ORDERS . " WHERE orders_id = " . $orderId;
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            return (string)$r[UnzerConstants::ORDER_TABLE_PAYMENT_ID_COLUMN];
        } else {
            return null;
        }
    }

    public function setOrderStatusChargeback($orderId, $comment = ''): void
    {
        $status = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CHARGEBACK');
        $this->setOrderStatus($orderId, $status, $comment);
    }

    public function setOrderStatusAuthorized($orderId, $comment = ''): void
    {
        $status = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_AUTHORIZED');
        $this->setOrderStatus($orderId, $status, $comment);
    }

    public function setOrderStatusCaptured($orderId, $comment = ''): void
    {
        $status = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_CAPTURED');
        $this->setOrderStatus($orderId, $status, $comment);
    }

    public function setOrderStatusRefunded($orderId, $comment = ''): void
    {
        $status = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_REFUNDED');
        $this->setOrderStatus($orderId, $status, $comment);
    }

    public function formatPaymentInstructions(\UnzerSDK\Resources\Payment $payment): string
    {

        try {
            $transaction = $payment->getInitialTransaction();
            if ($transaction && $transaction->getBic() && $transaction->getIban() && $transaction->getHolder() && $transaction->getDescriptor()) {
                UnzerConfigHelper::initTexts();
                return sprintf("%s: %s \n%s: %s \n%s: %s \n%s: %s\n",
                    UnzerConfigHelper::getStringConstant('UNZER_ACCOUNT_HOLDER'),
                    $transaction->getHolder(),
                    UnzerConfigHelper::getStringConstant('UNZER_ACCOUNT_IBAN'),
                    $transaction->getIban(),
                    UnzerConfigHelper::getStringConstant('UNZER_ACCOUNT_BIC'),
                    $transaction->getBic(),
                    UnzerConfigHelper::getStringConstant('UNZER_ACCOUNT_DESCRIPTOR'),
                    $transaction->getDescriptor()
                );
            } else {
                return '';
            }
        } catch (Exception $e) {
            return '';
        }
    }

    protected function getCustomersTotalOrderAmount(int $customerId): float
    {
        $q = "SELECT SUM(value) as total FROM " . TABLE_ORDERS_TOTAL . " WHERE class = 'ot_total' AND orders_id IN (SELECT orders_id FROM " . TABLE_ORDERS . " WHERE customers_id = " . $customerId . ")";
        $rs = xtc_db_query($q);
        $r = xtc_db_fetch_array($rs);
        return (float)$r['total'];
    }

    protected function getCustomersTotalNumberOfOrders(int $customerId): int
    {
        $q = "SELECT COUNT(*) as total FROM " . TABLE_ORDERS . " WHERE customers_id = " . $customerId;
        $rs = xtc_db_query($q);
        $r = xtc_db_fetch_array($rs);
        return (int)$r['total'];
    }

    protected function isCustomerRegistered(int $customerId): bool
    {
        $q = "SELECT account_type FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $customerId;
        $rs = xtc_db_query($q);
        if ($r = xtc_db_fetch_array($rs)) {
            return (int)$r['account_type'] === 0;
        } else {
            return false;
        }
    }

    protected function getCustomersRegistrationDate(int $customerId): ?string
    {
        $q = "SELECT customers_date_added FROM " . TABLE_CUSTOMERS . " WHERE customers_id = " . $customerId;
        $rs = xtc_db_query($q);
        if($r = xtc_db_fetch_array($rs)){
            return $r['customers_date_added'];
        } else {
            return null;
        }
    }

    public function listenToOrderStatusChange(IdType $orderId, IntType $newOrderStatusId):void
    {
        if($orderStatusToTriggerCapture = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_CAPTURE')){
           if($newOrderStatusId->asInt() === (int)$orderStatusToTriggerCapture){
               $this->triggerCaptureOnOrder($orderId->asInt());
           }
        }
        if($orderStatusToTriggerRefund = UnzerConfigHelper::getConstant('MODULE_PAYMENT_UNZER_ORDER_STATUS_ID_TRIGGER_REFUND')){
            if($newOrderStatusId->asInt() === (int)$orderStatusToTriggerRefund){
                $this->triggerRefundOnOrder($orderId->asInt());
            }
        }
    }

    public function triggerCaptureOnOrder(int $orderId):void
    {
        $paymentId = $this->getPaymentIdFromOrderId($orderId);
        if(!$paymentId){
            $this->logger->warning('triggerCaptureOnOrder: paymentId not found', ['orderId' => $orderId]);
            return;
        }

        try {
            $payment = $this->unzer->fetchPayment($paymentId);
            $payment->charge();
            if($payment->isCompleted()) {
                $this->setOrderStatusCaptured($orderId, 'Unzer');
            }
        } catch (Exception $e) {
            $this->logger->error('triggerCaptureOnOrder: ' . $e->getMessage(), ['orderId' => $orderId, 'paymentId' => $paymentId]);
        }
    }

    public function triggerRefundOnOrder(int $orderId):void
    {
        $paymentId = $this->getPaymentIdFromOrderId($orderId);
        if(!$paymentId){
            $this->logger->warning('triggerRefundOnOrder: paymentId not found', ['orderId' => $orderId]);
            return;
        }

        try {
            $payment = $this->unzer->fetchPayment($paymentId);
            $payment->cancelAmount();
            if($payment->isCanceled()) {
                $this->setOrderStatusRefunded($orderId, 'Unzer');
            }
        } catch (Exception $e) {
            $this->logger->error('triggerRefundOnOrder: ' . $e->getMessage(), ['orderId' => $orderId, 'paymentId' => $paymentId]);
        }
    }
}