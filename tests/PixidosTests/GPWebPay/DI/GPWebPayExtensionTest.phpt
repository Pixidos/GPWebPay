<?php
/**
 * Test: Pixidos\GPWebPay\DI\GPWebPayExtension
 * @testCase PixidosTests\GPWebPay\DI\GPWebPayExtensionTest
 */

namespace PixidosTests\GPWebPay\DI;


use PixidosTests\GPWebPay\GPWebPayTestCase;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

class GPWebPayExtensionTest extends GPWebPayTestCase
{

    public function setUp()
    {
		parent::setUp();
		$this->prepareContainer();
    }

    public function testSettingCreated()
    {
        $container = $this->getContainer();
        $gpWebPaySettings = $container->getByType('Pixidos\GPWebPay\Settings');

		Assert::type('Pixidos\GPWebPay\Settings', $gpWebPaySettings);

    }

	public function testProviderCreated()
	{
		$container = $this->getContainer();
		$gpWebPayProvider = $container->getByType('Pixidos\GPWebPay\Provider');
		Assert::type('Pixidos\GPWebPay\Provider', $gpWebPayProvider);
    }

	public function testGPWebPayControlFactoryCreated()
	{
		$container = $this->getContainer();
		$gPWebPayControlFactory = $container->getByType('Pixidos\GPWebPay\Components\GPWebPayControlFactory');
		Assert::type('Pixidos\GPWebPay\Components\GPWebPayControlFactory', $gPWebPayControlFactory);
	}



}

$test = new GPWebPayExtensionTest();
$test->run();
