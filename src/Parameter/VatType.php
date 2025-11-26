<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 商品單價是否含稅。
 */
enum VatType: string
{
    /** 未稅 */
    case NO = '0';

    /** 含稅 */
    case YES = '1';
}
