<?php declare(strict_types=1);

/**
 * Test: Pixidos\GPWebPay\DI\GPWebPayExtension
 * @testCase GPWebPay\Tests\DI\GPWebPayExtensionTest
 */

namespace GPWebPay\Tests\DI;

use Nette\Http\RequestFactory;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Config\Config;
use Pixidos\GPWebPay\Config\PaymentConfigProvider;
use Pixidos\GPWebPay\Config\SignerConfigProvider;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\ResponseProvider;
use Pixidos\GPWebPay\ResponseProviderInterface;
use GPWebPay\Tests\GPWebPayTestCase;
use Pixidos\GPWebPay\Signer\SignerFactoryInterface;
use Pixidos\GPWebPay\Signer\SignerProvider;
use Pixidos\GPWebPay\Signer\SignerProviderInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class GPWebPayExtensionTest extends GPWebPayTestCase
{

    public function testSettingCreated(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.config.neon');
        $container = $this->getContainer();

        Assert::type(Config::class, $container->getByType(Config::class));
        Assert::type(PaymentConfigProvider::class, $container->getByType(PaymentConfigProvider::class));
        Assert::type(SignerConfigProvider::class, $container->getByType(SignerConfigProvider::class));

        Assert::type(ResponseProvider::class, $container->getByType(ResponseProviderInterface::class));
        Assert::type(ResponseFactory::class, $container->getByType(ResponseFactory::class));
        Assert::type(RequestFactory::class, $container->getByType(RequestFactory::class));
        Assert::type(SignerProvider::class, $container->getByType(SignerProviderInterface::class));
        Assert::type(SignerFactoryInterface::class, $container->getByType(SignerFactoryInterface::class));
        Assert::type(GPWebPayControlFactory::class, $container->getByType(GPWebPayControlFactory::class));

    }


    public function testMultipleSettingCreated(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.multiple.config.neon');
        $container = $this->getContainer();

        Assert::type(Config::class, $container->getByType(Config::class));
        Assert::type(PaymentConfigProvider::class, $container->getByType(PaymentConfigProvider::class));
        Assert::type(SignerConfigProvider::class, $container->getByType(SignerConfigProvider::class));

        Assert::type(ResponseProvider::class, $container->getByType(ResponseProviderInterface::class));
        Assert::type(ResponseFactory::class, $container->getByType(ResponseFactory::class));
        Assert::type(RequestFactory::class, $container->getByType(RequestFactory::class));
        Assert::type(SignerProvider::class, $container->getByType(SignerProviderInterface::class));
        Assert::type(SignerFactoryInterface::class, $container->getByType(SignerFactoryInterface::class));
        Assert::type(GPWebPayControlFactory::class, $container->getByType(GPWebPayControlFactory::class));

        /** @var PaymentConfigProvider $paymentConfig */
        $paymentConfig = $container->getByType(PaymentConfigProvider::class);
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber($paymentConfig->getDefaultGateway()));
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber('czk'));
        Assert::same('123456780', (string)$paymentConfig->getMerchantNumber('eur'));
    }

}

$test = new GPWebPayExtensionTest();
$test->run();
