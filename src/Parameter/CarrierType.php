<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Parameter;

/**
 * 載具類型。
 */
enum CarrierType: string
{
    /** 無載具 */
    case NONE = '';

    /** 會員載具 */
    case MEMBER = '1';

    /** 買受人自然人憑證 */
    case CITIZEN = '2';

    /** 買受人手機條碼 */
    case CELLPHONE = '3';
}
