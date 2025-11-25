<?php

namespace CarlLee\EcPayB2C\Parameter;

class CarrierType
{
    // 無載具
    public const NONE = '';

    // 會員載具
    public const MEMBER = '1';

    // 買受人自然人憑證
    public const CITIZEN = '2';

    // 買受人手機條碼
    public const CELLPHONE = '3';
}
