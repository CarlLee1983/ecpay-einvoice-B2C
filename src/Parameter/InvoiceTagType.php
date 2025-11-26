<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

class InvoiceTagType
{
    // 發票開立
    public const string INVOICE = 'I';

    // 發票作廢
    public const string INVOICE_VOID = 'II';

    // 折讓開立
    public const string ALLOWANCE = 'A';

    // 折讓作廢
    public const string ALLOWANCE_VOID = 'AI';

    // 發票中獎
    public const string INVOICE_WINNING = 'AW';
}
