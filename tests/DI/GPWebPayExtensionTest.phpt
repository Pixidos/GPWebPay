<?php declare(strict_types=1);

/**
 * Test: Pixidos\GPWebPay\DI\GPWebPayExtension
 * @testCase GPWebPay\Tests\DI\GPWebPayExtensionTest
 */

namespace GPWebPay\Tests\DI;

use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Provider;
use Pixidos\GPWebPay\Settings\Settings;
use GPWebPay\Tests\GPWebPayTestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class GPWebPayExtensionTest extends GPWebPayTestCase
{

    public function testSettingCreated(): void
    {
        $this->prepareContainer(sprintf(__DIR__ . '/../config/webpay.config.neon'));
        $container = $this->getContainer();
        $gpWebPaySettings = $container->getByType(Settings::class);

        Assert::type(Settings::class, $gpWebPaySettings);
    }


    public function testMultipleSettingCreated(): void
    {
        $this->prepareContainer(sprintf(__DIR__ . '/../config/webpay.multiple.config.neon'));
        $container = $this->getContainer();
        /** @var Settings $gpWebPaySettings */
        $gpWebPaySettings = $container->getByType(Settings::class);

        Assert::type(Settings::class, $gpWebPaySettings);
        Assert::same('123456789', (string)$gpWebPaySettings->getMerchantNumber($gpWebPaySettings->getDefaultGatewayKey()));
        Assert::same('123456789', (string)$gpWebPaySettings->getMerchantNumber('czk'));
        Assert::same('123456780', (string)$gpWebPaySettings->getMerchantNumber('eur'));
    }

    public function testProviderCreated(): void
    {
        $this->prepareContainer(sprintf(__DIR__ . '/../config/webpay.config.neon'));
        $container = $this->getContainer();
        $gpWebPayProvider = $container->getByType(Provider::class);
        Assert::type(Provider::class, $gpWebPayProvider);
    }

    public function testGPWebPayControlFactoryCreated(): void
    {
        $this->prepareContainer(sprintf(__DIR__ . '/../config/webpay.config.neon'));
        $container = $this->getContainer();
        $gPWebPayControlFactory = $container->getByType(GPWebPayControlFactory::class);
        Assert::type(GPWebPayControlFactory::class, $gPWebPayControlFactory);
    }

}

$test = new GPWebPayExtensionTest();
$test->run();
