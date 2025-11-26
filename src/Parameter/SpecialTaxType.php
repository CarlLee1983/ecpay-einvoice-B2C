<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 特種稅類型。
 */
enum SpecialTaxType: string
{
    /** 陪酒業 */
    case BAR = '1';

    /** 夜總會 */
    case NIGHTCLUB = '2';

    /** 銀行業 */
    case BANK = '3';

    /** 保險業業內收入 */
    case INSURANCE = '4';
}
