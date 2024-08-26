<?php

use UnzerSDK\Constants\WebhookEvents;

class UnzerWebhookController extends HttpViewController
{
    protected UnzerLogger $logger;

    const REGISTERED_EVENTS = [
        WebhookEvents::CHARGE_CANCELED,
        WebhookEvents::AUTHORIZE_CANCELED,
        WebhookEvents::AUTHORIZE_SUCCEEDED,
        WebhookEvents::CHARGE_SUCCEEDED,
        WebhookEvents::PAYMENT_CHARGEBACK,
    ];
    protected UnzerOrderHelper $orderHelper;
    protected UnzerApiHelper $apiHelper;

    /**
     * CookieConsentPanelVendorListAjaxController constructor.
     *
     * @param HttpContextReaderInterface $httpContextReader
     * @param HttpResponseProcessorInterface $httpResponseProcessor
     * @param ContentViewInterface $defaultContentView
     */
    public function __construct(
        HttpContextReaderInterface     $httpContextReader,
        HttpResponseProcessorInterface $httpResponseProcessor,
        ContentViewInterface           $defaultContentView

    )
    {
        parent::__construct($httpContextReader, $httpResponseProcessor, $defaultContentView);
        $this->logger = new UnzerLogger();
        $this->orderHelper = new UnzerOrderHelper();
        $this->apiHelper = new UnzerApiHelper();
    }

    protected function getJsonResponse($success = true, $data = [])
    {
        return MainFactory::create(
            'JsonHttpControllerResponse',
            [
                'success' => $success,
                'data' => $data,
            ]
        );
    }

    public function actionDefault(): HttpControllerResponse
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data)) {
            $this->logger->debug('empty webhook');
            return $this->getJsonResponse(false, ['msg' => 'empty webhook']);
        }


        if (!in_array($data['event'], self::REGISTERED_EVENTS, true)) {
            return $this->getJsonResponse(false, ['msg' => 'event not relevant']);
        }

        $this->logger->debug('webhook received', ['data' => $data]);
        if (empty($data['paymentId'])) {
            $this->logger->warning('no payment id in webhook event', ['webhook_data' => $data]);
            return $this->getJsonResponse(false, ['msg' => 'no payment id in webhook event']);
        }


        $orderId = $this->orderHelper->getOrderIdFromPaymentId($data['paymentId']);
        if (empty($orderId)) {
            $this->logger->warning('no order id for payment id in webhook event', ['webhook_data' => $data]);
            return $this->getJsonResponse(false, ['msg' => 'no order id for payment id in webhook event']);
        }

        switch ($data['event']) {
            case WebhookEvents::CHARGE_CANCELED:
            case WebhookEvents::AUTHORIZE_CANCELED:
                $this->handleCancel($data['paymentId'], $orderId);
                break;
            case WebhookEvents::AUTHORIZE_SUCCEEDED:
                $this->handleAuthorizeSucceeded($data['paymentId'], $orderId);
                break;
            case WebhookEvents::CHARGE_SUCCEEDED:
                $this->handleChargeSucceeded($data['paymentId'], $orderId);
                break;
            case WebhookEvents::PAYMENT_CHARGEBACK:
                $this->handleChargeback($data['paymentId'], $orderId);
                break;
        }
        return $this->getJsonResponse(true, ['msg' => 'webhook processed']);
    }

    public function handleChargeback($paymentId, $orderId)
    {
        $this->logger->debug(
            'webhook handleChargeback',
            [
                'paymentId' => $paymentId,
                'orderId' => $orderId,
            ]
        );
        $this->orderHelper->setOrderStatusChargeback($orderId, 'Unzer Webhook');
    }

    private function handleCancel($paymentId, $orderId)
    {
        $this->logger->debug(
            'webhook handleCancel',
            [
                'paymentId' => $paymentId,
                'orderId' => $orderId,
            ]
        );
        $payment = $this->apiHelper->fetchPayment($paymentId);
        if($payment->isCanceled()) {
            $this->orderHelper->setOrderStatusRefunded($orderId, 'Unzer Webhook');
        }

    }

    private function handleAuthorizeSucceeded($paymentId, $orderId)
    {
        $this->logger->debug(
            'webhook handleAuthorizeSucceeded',
            [
                'paymentId' => $paymentId,
                'orderId' => $orderId,
            ]
        );
        $this->orderHelper->setOrderStatusAuthorized($orderId, 'Unzer Webhook');
    }

    private function handleChargeSucceeded($paymentId, $orderId)
    {
        $this->logger->debug(
            'webhook handleChargeSucceeded',
            [
                'paymentId' => $paymentId,
                'orderId' => $orderId,
            ]
        );
        $this->orderHelper->setOrderStatusCaptured($orderId, 'Unzer Webhook');
    }
}