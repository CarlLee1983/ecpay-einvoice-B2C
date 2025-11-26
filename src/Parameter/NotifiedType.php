<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 通知對象類型。
 */
enum NotifiedType: string
{
    /** 通知客戶 */
    case CUSTOMER = 'C';

    /** 通知廠商 */
    case VENDOR = 'M';

    /** 皆發送 */
    case ALL = 'A';
}
