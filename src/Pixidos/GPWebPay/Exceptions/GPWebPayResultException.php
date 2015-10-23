<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 23.10.2015
 * Time: 9:01
 */

namespace Pixidos\GPWebPay\Exceptions;

/**
 * Class GPWebPayResultException
 * @package Pixidos\GPWebPay\Exceptions
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayResultException extends GPWebPayException
{

    /**
     * @var int $prcode
     */
    private $prcode;
    /**
     * @var int $srcode
     */
    private $srcode;

    /**
     * @param string $message
     * @param int $prcode
     * @param int $srcode
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $prcode, $srcode, $code = NULL, \Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
        $this->prcode = $prcode;
        $this->srcode = $srcode;
    }

    public function getPrcode()
    {
        return $this->prcode;
    }

    public function getSrcode()
    {
        return $this->srcode;
    }

}