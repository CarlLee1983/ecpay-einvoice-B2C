<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 通知方式。
 */
enum NotifyType: string
{
    /** 簡訊通知 */
    case SMS = 'S';

    /** 電子郵件通知 */
    case EMAIL = 'E';

    /** 皆通知 */
    case ALL = 'A';
}
