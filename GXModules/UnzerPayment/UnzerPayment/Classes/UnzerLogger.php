<?php

use Gambio\Core\Logging\LoggerBuilder;
use Psr\Log\LoggerInterface;

class UnzerLogger
{
    protected FileLog|LoggerInterface $logger;

    public function debug($msg, $data = null): void
    {
        $this->log('debug', $msg, $data);
    }

    public function warning($msg, $data = null): void
    {
        $this->log('warning', $msg, $data);
    }

    public function error($msg, $data = null): void
    {
        $this->log('error', $msg, $data);
    }

    public function log($level, $message, ?array $context = [])
    {
        if (class_exists(LoggerBuilder::class)) {
            if (empty($this->logger)) {
                /** @var LoggerBuilder $loggerBuilder */
                $loggerBuilder = LegacyDependencyContainer::getInstance()->get(LoggerBuilder::class);
                $this->logger = $loggerBuilder->omitRequestData()->changeNamespace('unzer')->build();
            }
            $this->logger->log($level, $message, $context);
        } else {
            //legacy
            if (empty($this->logger)) {
                $this->logger = new FileLog('unzer', true);;
            }
            $prefix = str_pad(strtoupper($level) . ' | ' . date('Y-m-d H:i:s') . ' - ' . session_id(), 60, ' ') . ' - ';
            $this->logger->write($prefix . $message . ' ' . serialize($context) . "\n");
        }


    }
}