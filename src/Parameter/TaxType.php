<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

class TaxType
{
    // 應稅
    public const string DUTIABLE = '1';

    // 零稅率
    public const string ZERO = '2';

    // 免稅
    public const string FREE = '3';

    // 應稅與免稅混合(限收銀機發票無法分辦時使用，且需通過申請核可)
    public const string MIX = '9';
}
