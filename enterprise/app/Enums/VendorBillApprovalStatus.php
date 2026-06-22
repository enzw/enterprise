<?php

namespace App\Enums;

enum VendorBillApprovalStatus: string
{
    case NotRequired = 'NOT_REQUIRED';
    case Pending = 'PENDING';
    case Approved = 'APPROVED';
    case Rejected = 'REJECTED';
}
