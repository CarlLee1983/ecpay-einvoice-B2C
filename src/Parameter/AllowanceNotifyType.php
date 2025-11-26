<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

class AllowanceNotifyType
{
    // 簡訊通知
    public const string SMS = 'S';

    // 電子郵件通知
    public const string EMAIL = 'E';

    // 皆通知
    public const string ALL = 'A';

    // 皆不通知
    public const string NONE = 'N';
}
