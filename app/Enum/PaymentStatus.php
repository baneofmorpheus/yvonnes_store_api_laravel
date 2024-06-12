<?php

namespace App\Enum;


enum PaymentStatus: string
{
    case PAID = 'paid';
    case PENDING_PAYMENT = 'pending_payment';
    case PART_PAYMENT = 'part_payment';
    case REFUNDED = 'refunded';
}
