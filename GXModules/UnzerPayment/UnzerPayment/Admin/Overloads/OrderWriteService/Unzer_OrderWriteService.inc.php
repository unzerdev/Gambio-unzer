<?php

class Unzer_OrderWriteService extends Unzer_OrderWriteService_parent
{
    static $called = [];

    public function updateOrderStatus(
        IdType     $orderId,
        IntType    $newOrderStatusId,
        StringType $comment,
        BoolType   $customerNotified,
        IdType     $customerId = null
    )
    {
        parent::updateOrderStatus($orderId, $newOrderStatusId, $comment, $customerNotified, $customerId);
        $callId = $orderId->asInt() . '_' . $newOrderStatusId->asInt();
        if (isset(self::$called[$callId])) {
            return;
        }
        self::$called[$callId] = true;
        (new UnzerOrderHelper())->listenToOrderStatusChange($orderId, $newOrderStatusId);
    }
}
