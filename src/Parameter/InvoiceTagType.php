<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 發票標籤類型。
 */
enum InvoiceTagType: string
{
    /** 發票開立 */
    case INVOICE = 'I';

    /** 發票作廢 */
    case INVOICE_VOID = 'II';

    /** 折讓開立 */
    case ALLOWANCE = 'A';

    /** 折讓作廢 */
    case ALLOWANCE_VOID = 'AI';

    /** 發票中獎 */
    case INVOICE_WINNING = 'AW';
}
