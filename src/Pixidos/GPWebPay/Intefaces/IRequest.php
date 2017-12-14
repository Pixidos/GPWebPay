<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 06.06.2017
 * Time: 22:28
 */

namespace Pixidos\GPWebPay\Intefaces;


interface IRequest
{

	/**
	 * Return all parameters
	 * @return array
	 */
	public function getParams(): array ;

	/**
	 * Return only parameters what are included in digest
	 * @return array
	 */
    public function getDigestParams(): array ;
    
    /**
     * Method only for ISinger
     * @param string $digest
     * @internal
     * @return void
     */
    public function setDigest(string $digest): void;

}
