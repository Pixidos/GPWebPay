<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 31.03.2017
 * Time: 14:17
 */
/**
 * Test: Pixidos\GPWebPay\Response
 * @testCase PixidosTests\GPWebPay\ResponseTest
 */

namespace PixidosTests\GPWebPay;

use Pixidos\GPWebPay\Response;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';


/**
 * Class ResponseTest
 * @package PixidosTests\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class ResponseTest extends GPWebPayTestCase
{

	/**
	 * 'OPERATION' => 'CREATE_ORDER',
	 * 'ORDERNUMBER' => '123456',
	 * 'MERORDERNUM' => 'FA12345',
	 * 'MD' => 'czk|sometext',
	 * 'PRCODE' => '1000',
	 * 'SRCODE' => '30',
	 * 'RESULTTEXT' => 'resulttext',
	 * 'digest' => 'hash1',
	 * 'digest1' => 'hash2',
	 * 'gatewayKey' => 'czk',
	 */
	public function testCreateGPWebPayResponse()
	{
		$params = TestHelpers::getTestParams();

		$response = new Response($params['OPERATION'], $params['ORDERNUMBER'], $params['MERORDERNUM'], $params['MD'],
			$params['PRCODE'], $params['SRCODE'], $params['RESULTTEXT'], $params['digest'], $params['digest1'],
			$params['gatewayKey']);

		Assert::same('123456', $response->getOrderNumber());
		Assert::same('FA12345', $response->getMerOrderNumber());
		Assert::same('sometext', $response->getMd());
		Assert::same(1000, $response->getPrcode());
		Assert::same(30, $response->getSrcode());
		Assert::same('resulttext', $response->getResultText());
		Assert::same('hash1', $response->getDigest());
		Assert::same('hash2', $response->getDigest1());
		Assert::same('czk', $response->getGatewayKey());

	}
}

(new ResponseTest())->run();