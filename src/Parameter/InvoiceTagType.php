<?php

namespace CarlLee\EcPayB2C\Parameter;

class InvoiceTagType
{
    // 發票開立
    public const INVOICE = 'I';

    // 發票作廢
    public const INVOICE_VOID = 'II';

    // 折讓開立
    public const ALLOWANCE = 'A';

    // 折讓作廢
    public const ALLOWANCE_VOID = 'AI';

    // 發票中獎
    public const INVOICE_WINNING = 'AW';
}
