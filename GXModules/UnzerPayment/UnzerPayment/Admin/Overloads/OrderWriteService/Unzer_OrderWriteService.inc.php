<?php

class Unzer_OrderWriteService extends Unzer_OrderWriteService_parent
{
    public function updateOrderStatus(
        IdType $orderId,
        IntType $newOrderStatusId,
        StringType $comment,
        BoolType $customerNotified,
        IdType $customerId = null
    ) {
        parent::updateOrderStatus($orderId, $newOrderStatusId, $comment, $customerNotified, $customerId);
        (new UnzerOrderHelper())->listenToOrderStatusChange($orderId, $newOrderStatusId);
    }
}