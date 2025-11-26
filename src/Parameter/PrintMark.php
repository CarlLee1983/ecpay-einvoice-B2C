<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 列印註記。
 */
enum PrintMark: string
{
    /** 不列印 */
    case NO = '0';

    /** 列印 */
    case YES = '1';
}
