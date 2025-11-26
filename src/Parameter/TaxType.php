<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 課稅類型。
 */
enum TaxType: string
{
    /** 應稅 */
    case DUTIABLE = '1';

    /** 零稅率 */
    case ZERO = '2';

    /** 免稅 */
    case FREE = '3';

    /** 應稅與免稅混合(限收銀機發票無法分辦時使用，且需通過申請核可) */
    case MIX = '9';
}
