<?php

namespace App\Enums;

enum PurchaseOrderLifecycleStatus: string
{
    case Draft = 'DRAFT';
    case Open = 'OPEN';
    case Closed = 'CLOSED';
    case Cancelled = 'CANCELLED';
}
