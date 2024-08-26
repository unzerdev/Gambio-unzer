<?php

use UnzerSDK\Constants\WebhookEvents;
use UnzerSDK\Resources\Payment;
use UnzerSDK\Resources\TransactionTypes\Charge;
use UnzerSDK\Unzer;

class UnzerApiHelper
{
    protected Unzer $unzer;
    public UnzerLogger $logger;

    public function __construct()
    {
        $this->unzer = new Unzer(UnzerConfigHelper::getPrivateKey());
        $this->logger = new UnzerLogger();
    }

    public function getAllPaymentMethods(int $cacheTime = 3600): array
    {
        $storageKey = 'unzer_all_payment_methods_' . md5(UnzerConfigHelper::getPrivateKey());
        $cacheFile = DIR_FS_CATALOG . 'cache/' . $storageKey;
        if (file_exists($cacheFile) && filesize($cacheFile) > 100 && filemtime($cacheFile) > time() - $cacheTime) {
            return unserialize(file_get_contents($cacheFile));
        }
        try {
            $result = $this->unzer->fetchKeypair(true);
            file_put_contents($cacheFile, serialize($result->getAvailablePaymentTypes()));
            return $result->getAvailablePaymentTypes();
        } catch (Exception $e) {
            $this->logger->error('getPaymentMethods Error', [$e->getMessage(), $e->getTraceAsString()]);
            return [];
        }
    }

    public function fetchPayment(string $paymentId): ?Payment
    {
        try {
            return $this->unzer->fetchPayment($paymentId);
        } catch (Exception $e) {
            $this->logger->error('fetchPayment Error', [$e->getMessage(), $e->getTraceAsString()]);
            return null;
        }
    }

    public function refund(string $paymentId, float $amount)
    {
        $this->unzer->cancelPayment(
            $paymentId,
            $amount
        );
    }

    public function charge(?string $paymentId, float $amount)
    {
        $this->unzer->performChargeOnPayment(
            $paymentId,
            new Charge($amount)
        );
    }

    public static function getClassNameForPaymentType(string $type): ?string
    {
        $type = preg_replace('/[^a-z0-9]/', '', strtolower($type));
        $dir = DIR_FS_CATALOG . 'includes/modules/payment/unzer/vendor/unzerdev/php-sdk/src/Resources/PaymentTypes';
        //all files
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $className = str_replace('.php', '', $file);
            if (strtolower($className) === $type) {
                return 'UnzerSDK\\Resources\\PaymentTypes\\' . $className;
            }
        }
        return null;
    }

    public static function canPaymentMethodAuthorize(string $type)
    {
        $className = self::getClassNameForPaymentType($type);
        if ($className) {
            return method_exists($className, 'authorize');
        }
        return false;
    }


    public function fetchWebhooks(): array
    {
        $returnData = [];
        foreach ($this->unzer->fetchAllWebhooks() as $webhook) {
            $returnData[] = $webhook->expose();
        }
        return $returnData;
    }

    public function isWebhookRegistered(): bool
    {
        $currentUrl = UnzerConfigHelper::getWebhookUrl();
        foreach ($this->unzer->fetchAllWebhooks() as $webhook) {
            if ($webhook->getUrl() === $currentUrl) {
                return true;
            }
        }
        return false;
    }

    public function deleteWebhook($webhookId): void
    {
        $this->unzer->deleteWebhook($webhookId);
    }

    public function addCurrentWebhook(): void
    {
        $this->unzer->createWebhook(UnzerConfigHelper::getWebhookUrl(), WebhookEvents::ALL);
    }
}