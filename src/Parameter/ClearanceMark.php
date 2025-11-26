<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 通關方式。
 */
enum ClearanceMark: string
{
    /** 經海關出口 */
    case YES = '1';

    /** 非經海關出口 */
    case NO = '2';
}
