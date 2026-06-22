<?php

namespace App\Enums;

enum VendorBillLifecycleStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Void = 'VOID';
}
