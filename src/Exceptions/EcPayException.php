<?php

declare(strict_types=1);

namespace CarlLee\EcPayB2C\Exceptions;

use Exception;

/**
 * 綠界電子發票套件的基礎例外類別。
 *
 * 所有套件內部的例外都繼承自此類別，方便使用者統一捕捉。
 */
class EcPayException extends Exception
{
}

