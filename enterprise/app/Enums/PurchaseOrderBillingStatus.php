<?php

namespace App\Enums;

enum PurchaseOrderBillingStatus: string
{
    case NotBilled = 'NOT_BILLED';
    case PartiallyBilled = 'PARTIALLY_BILLED';
    case FullyBilled = 'FULLY_BILLED';
}
