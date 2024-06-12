<?php

namespace App\Enum;


enum PaymentType: string
{
    case CASH = 'cash';
    case CHEQUE = 'cheque';
    case TRANSFER = 'transfer';
    case POS = 'pos';
}
