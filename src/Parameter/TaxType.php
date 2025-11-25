<?php

namespace CarlLee\EcPayB2C\Parameter;

class TaxType
{
    // 應稅
    public const DUTIABLE = '1';

    // 零稅率
    public const ZERO = '2';

    // 免稅
    public const FREE = '3';

    // 應稅與免稅混合(限收銀機發票無法分辦時使用，且需通過申請核可)
    public const MIX = '9';
}
