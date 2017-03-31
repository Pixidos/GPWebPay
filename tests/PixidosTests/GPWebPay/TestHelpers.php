<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 31.03.2017
 * Time: 22:00
 */

namespace PixidosTests\GPWebPay;

use Pixidos\GPWebPay\Operation;

/**
 * Class TestHelpers
 * @package PixidosTests\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class TestHelpers
{


	/**
	 * @return Operation
	 */
	public static function createOperation()
	{
		return new Operation(123456, 1000, Operation::CZK, 'czk', 'http://test.com');
	}

	public static function getTestParams()
	{
		$params = [
			'OPERATION' => 'CREATE_ORDER',
			'ORDERNUMBER' => '123456',
			'MERORDERNUM' => 'FA12345',
			'MD' => 'czk|sometext',
			'PRCODE' => '1000',
			'SRCODE' => '30',
			'RESULTTEXT' => 'resulttext',
			'digest' => 'hash1',
			'digest1' => 'hash2',
			'gatewayKey' => 'czk',
		];

		return $params;
	}
}