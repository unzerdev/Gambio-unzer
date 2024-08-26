<?php


use UnzerSDK\Resources\TransactionTypes\AbstractTransactionType;
use UnzerSDK\Resources\TransactionTypes\Authorization;
use UnzerSDK\Resources\TransactionTypes\Cancellation;
use UnzerSDK\Resources\TransactionTypes\Charge;

class UnzerTransactionsController extends AdminHttpViewController
{
    protected function init(){
        UnzerConfigHelper::initTexts();
    }

    public function actionDefault()
    {
        return $this->actionGetTransactions();
    }

    public function actionGetTransactions()
    {
        $apiHelper = new UnzerApiHelper();
        $orderHelper = new UnzerOrderHelper();
        $payment = $apiHelper->fetchPayment($this->_getQueryParameter('paymentId'));
        $orderId = (int)$this->_getQueryParameter('orderId');


        $contentView = MainFactory::create('ContentView');
        $contentView->set_escape_html(true);
        $contentView->set_flat_assigns(true);
        $contentView->set_template_dir(UnzerConstants::MODULE_PATH_FS . 'Admin/Html/');
        $contentView->set_content_template('unzer_transactions.html');
        $contentView->set_content_data('paymentMethodLabel', $orderHelper->getUnzerPaymentMethodNameFromOrderId($orderId));
        $contentView->set_content_data('payment', $payment);


        $paymentState = $payment->getState();
        try {
            $paymentStateName = \UnzerSDK\Constants\PaymentState::mapStateCodeToName($paymentState);
            $contentView->set_content_data('paymentStateName', $paymentStateName);
        } catch (Exception $e) {
            $contentView->set_content_data('paymentStateName', '?');
        }

        $contentView->set_content_data('amountRefundable', $payment->getAmount()->getCharged());
        $contentView->set_content_data('amountChargeable', $payment->getAmount()->getRemaining());

        $contentView->set_content_data('transactions', $this->getTransactionArray($payment));


        $html = $contentView->build_html();

        return MainFactory::create(
            'JsonHttpControllerResponse',
            [
                'success' => true,
                'html' => $html,
                'debug'=>preg_replace('/s\-priv\-[a-z0-9]+/i', '', print_r($payment, true))
            ]
        );
    }

    protected function getTransactionArray(\UnzerSDK\Resources\Payment $payment): array
    {
        $currency = $payment->getCurrency();
        $transactions = [];
        if ($payment->getAuthorization()) {
            $transactions[] = $payment->getAuthorization();
            if ($payment->getAuthorization()->getCancellations()) {
                $transactions = array_merge($transactions, $payment->getAuthorization()->getCancellations());
            }
        }
        if ($payment->getCharges()) {
            foreach ($payment->getCharges() as $charge) {
                $transactions[] = $charge;
                if ($charge->getCancellations()) {
                    $transactions = array_merge($transactions, $charge->getCancellations());
                }
            }
        }
        if ($payment->getReversals()) {
            foreach ($payment->getReversals() as $reversal) {
                $transactions[] = $reversal;
            }
        }
        if ($payment->getRefunds()) {
            foreach ($payment->getRefunds() as $refund) {
                $transactions[] = $refund;
            }
        }
        // $transactions = array_merge($transactions, $payment->getCharges(), $payment->getRefunds(), $payment->getReversals());
        $transactionTypes = [
            Cancellation::class => 'cancellation',
            Charge::class => 'charge',
            Authorization::class => 'authorization',
        ];

        $transactions = array_map(
            function (AbstractTransactionType $transaction) use ($transactionTypes, $currency) {
                $return = $transaction->expose();
                $class = get_class($transaction);
                $return['type'] = $transactionTypes[$class] ?? $class;
                $return['time'] = $transaction->getDate();

                if (method_exists($transaction, 'getAmount') && method_exists($transaction, 'getCurrency')) {
                    $return['amount'] = number_format($transaction->getAmount(), 2).' '.$currency;
                } elseif (isset($return['amount'])) {
                    $return['amount'] = number_format($return['amount'], 2).' '.$currency;
                }
                $status = $transaction->isSuccess() ? 'success' : 'error';
                $status = $transaction->isPending() ? 'pending' : $status;
                $return['status'] = $status;

                return $return;
            },
            $transactions
        );
        usort(
            $transactions,
            function ($a, $b) {
                return strcmp($a['time'], $b['time']);
            }
        );
        return $transactions;
    }

    public function actionDoAction()
    {
        $unzerApiHelper = new UnzerApiHelper();
        $action = $this->_getPostData('action');
        $error = null;
        switch ($action) {
            case 'capture':
                try {
                    $unzerApiHelper->charge($this->_getPostData('paymentId'), (float)$this->_getPostData('amount'));
                } catch (Exception $e) {
                    $error = 'ERROR: ' . $e->getMessage();
                }
                break;
            case 'refund':
                try {
                    $unzerApiHelper->refund($this->_getPostData('paymentId'), (float)$this->_getPostData('amount'));
                } catch (Exception $e) {
                    $error = 'ERROR: ' . $e->getMessage();
                }
                break;

        }
        return MainFactory::create(
            'JsonHttpControllerResponse',
            [
                'success' => true,
                'error' => $error,
            ]
        );
    }
}
