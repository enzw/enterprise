<?php

namespace App\Enums;

enum PurchaseOrderReceiptStatus: string
{
    case NotReceived = 'NOT_RECEIVED';
    case PartiallyReceived = 'PARTIALLY_RECEIVED';
    case FullyReceived = 'FULLY_RECEIVED';
}
