<?php

namespace App\Enums;

enum VendorBillPaymentStatus: string
{
    case Unpaid = 'UNPAID';
    case Paid = 'PAID';
}
