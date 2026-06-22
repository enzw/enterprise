<?php

namespace App\Enums;

enum VendorBillType: string
{
    case PoBased = 'PO_BASED';
    case Standalone = 'STANDALONE';
}
