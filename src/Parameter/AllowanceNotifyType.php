<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 折讓通知類型。
 */
enum AllowanceNotifyType: string
{
    /** 簡訊通知 */
    case SMS = 'S';

    /** 電子郵件通知 */
    case EMAIL = 'E';

    /** 皆通知 */
    case ALL = 'A';

    /** 皆不通知 */
    case NONE = 'N';
}
