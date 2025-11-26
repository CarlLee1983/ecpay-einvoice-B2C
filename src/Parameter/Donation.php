<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 捐贈註記。
 */
enum Donation: string
{
    /** 不捐贈 */
    case NO = '0';

    /** 捐贈 */
    case YES = '1';
}
