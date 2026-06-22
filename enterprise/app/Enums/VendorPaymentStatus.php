<?php

namespace App\Enums;

enum VendorPaymentStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Void = 'VOID';
}
