<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

class CarrierType
{
    // 無載具
    public const string NONE = '';

    // 會員載具
    public const string MEMBER = '1';

    // 買受人自然人憑證
    public const string CITIZEN = '2';

    // 買受人手機條碼
    public const string CELLPHONE = '3';
}
