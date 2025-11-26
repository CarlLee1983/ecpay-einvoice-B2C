<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

class NotifiedType
{
    // 通知客戶
    public const string CUSTOMER = 'C';

    // 通知廠商
    public const string VENDOR = 'M';

    // 皆發送
    public const string ALL = 'A';
}
