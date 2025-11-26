<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 發票類型。
 */
enum InvType: string
{
    /** 一般稅額 */
    case GENERAL = '07';

    /** 特種稅額 */
    case SPECIAL = '08';
}
