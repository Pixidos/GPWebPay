<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 22:35
 */

namespace Pixidos\GPWebPay\Intefaces;

use Pixidos\GPWebPay\Exceptions\SignerException;

/**
 * Class ISigner
 * @package Pixidos\GPWebPay\Intefaces
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
interface ISigner
{
    
    /**
     * @param array $params
     *
     * @return mixed
     */
    public function sign($params);
    
    /**
     * @param array $params
     * @param       $digest
     *
     * @return int
     * @throws SignerException
     */
    public function verify($params, $digest);
}
