<?php

use UnzerPayment\UnzerPayment\Classes\UnzerCheckoutHelper;
use UnzerSDK\Exceptions\UnzerApiException;

class UnzerCheckoutController extends HttpViewController
{

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
    }

    public function actionCheckout(): HttpControllerResponse
    {
        $orderId = $_SESSION['unzer_order_id'];
        $paymentMethod = $_SESSION['unzer_payment_method'] ?? null;
        $order = MainFactory::create('order', $orderId);
        $unzerOrderHelper = new UnzerOrderHelper();
        try {
            $payPage = $unzerOrderHelper->getUnzerPayPage($order, $paymentMethod);
            $_SESSION['unzer_payment_id'] = $payPage->getPaymentId();
            $data = [
                'unzerPaymentPageId' => $payPage->getId(),
                'threatMetrixUrl' => 'https://h.online-metrix.net/fp/tags.js?org_id=363t8kgq&session_id='.$payPage->getAdditionalAttribute('riskData.threatMetrixId'),
                'locale' => $_SESSION['language_code'] ?? 'en',
                'checkoutPaymentUrl' => xtc_href_link('checkout_payment.php', '', 'SSL'),
                'checkoutProcessUrl' => xtc_href_link('checkout_process.php', '', 'SSL'),
            ];
            $html = $this->_render('unzer_checkout.html', $data);
            return $this->getMainLayoutResponse($html);
        } catch (UnzerApiException $e) {
            $unzerOrderHelper->logger->error('Error during payment action: ' . $e->getMerchantMessage(), [
                'trace' => $e->getTraceAsString(),
                'error' => $e->getMerchantMessage(),
                'message' => $e->getMessage(),
                'basket' => ($payPage ?? null)?->getBasket()?->expose(),
                'customer' => ($payPage ?? null)?->getCustomer()?->expose(),
                'paypage' => ($payPage ?? null)?->expose(),
            ]);
            UnzerCheckoutHelper::doErrorRedirect($e->getClientMessage());
        }catch (Exception $e){
            $unzerOrderHelper->logger->error('Error during payment action: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'error' => $e->getMessage(),
                'message' => $e->getMessage(),
                'basket' => ($payPage ?? null)?->getBasket()?->expose(),
                'customer' => ($payPage ?? null)?->getCustomer()?->expose(),
                'paypage' => ($payPage ?? null)?->expose(),
            ]);
            UnzerCheckoutHelper::doErrorRedirect($e->getMessage());
        }

    }

    public function actionDefault(): HttpControllerResponse
    {
        return $this->actionCheckout();
    }

    protected function getMainLayoutResponse($html)
    {
        $layoutContentControl = MainFactory::create_object('LayoutContentControl');
        $layoutContentControl->set_data('GET', $this->_getQueryParametersCollection()->getArray());
        $layoutContentControl->set_data('POST', $this->_getPostDataCollection()->getArray());
        $layoutContentControl->set_('coo_breadcrumb', $GLOBALS['breadcrumb']);
        $layoutContentControl->set_('coo_product', $GLOBALS['product']);
        $layoutContentControl->set_('coo_xtc_price', $GLOBALS['xtPrice']);
        $layoutContentControl->set_('c_path', $GLOBALS['cPath']);
        $layoutContentControl->set_('main_content', $html);
        $layoutContentControl->set_('request_type', $GLOBALS['request_type']);
        $layoutContentControl->proceed();

        $redirectUrl = $layoutContentControl->get_redirect_url();
        if (!empty($redirectUrl)) {
            return MainFactory::create('RedirectHttpControllerResponse', $redirectUrl);
        }

        return MainFactory::create('HttpControllerResponse', $layoutContentControl->get_response());
    }

}