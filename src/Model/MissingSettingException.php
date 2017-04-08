<?php
/**
 * Created by PhpStorm.
 * User: dave
 * Date: 05/04/2017
 * Time: 19:58
 */

namespace httpdump\Model;


use Throwable;

class MissingSettingException extends \Exception
{

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}