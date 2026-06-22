<?php

namespace App\Enums;

enum ItemReceiptStatus: string
{
    case Draft = 'DRAFT';
    case Posted = 'POSTED';
    case Reversed = 'REVERSED';
}
