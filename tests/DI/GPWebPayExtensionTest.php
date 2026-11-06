<?php

/**
 * Test: Pixidos\GPWebPay\DI\GPWebPayExtension
 * @testCase GPWebPay\Tests\DI\GPWebPayExtensionTest
 */

declare(strict_types=1);

namespace GPWebPay\Tests\DI;

use GPWebPay\Tests\GPWebPayTestCase;
use GPWebPay\Tests\TestPresenter;
use Nette\Application\Responses\RedirectResponse;
use Nette\DI\Container;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Config\Config;
use Pixidos\GPWebPay\Config\Factory\ConfigFactory;
use Pixidos\GPWebPay\Config\Factory\PaymentConfigFactory;
use Pixidos\GPWebPay\Config\PaymentConfigProvider;
use Pixidos\GPWebPay\Config\SignerConfigProvider;
use Pixidos\GPWebPay\Data\Operation;
use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
use Pixidos\GPWebPay\Exceptions\LogicException;
use Pixidos\GPWebPay\Factory\RequestFactory;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\Param\AmountInPennies;
use Pixidos\GPWebPay\Param\Currency;
use Pixidos\GPWebPay\Param\OrderNumber;
use Pixidos\GPWebPay\ResponseProvider;
use Pixidos\GPWebPay\ResponseProviderInterface;
use Pixidos\GPWebPay\Signer\SignerFactory;
use Pixidos\GPWebPay\Signer\SignerFactoryInterface;
use Pixidos\GPWebPay\Signer\SignerProvider;
use Pixidos\GPWebPay\Signer\SignerProviderInterface;
use ReflectionProperty;
use Tester\Assert;

// phpcs:disable
require_once __DIR__ . '/../bootstrap.php';
// phpcs:enable

class GPWebPayExtensionTest extends GPWebPayTestCase
{
    private const GATEWAY_URL = 'https://test.3dsecure.gpwebpay.com/pgw/order.do';

    public function testSettingCreated(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.config.neon');
        $container = $this->getContainer();

        $this->assertServices($container);

        /** @var PaymentConfigProvider $paymentConfig */
        $paymentConfig = $container->getByType(PaymentConfigProvider::class);
        Assert::same('default', $paymentConfig->getDefaultGateway());
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber());
        Assert::same('1', (string)$paymentConfig->getDepositFlag());
        Assert::same(self::GATEWAY_URL, $paymentConfig->getUrl());
        Assert::null($paymentConfig->getResponseUrl());

        // the request factory of the extension signs with the configured keys
        $request = $container->getByType(RequestFactory::class)->create(TestPresenter::createOperation());
        $params = $request->getParams();
        Assert::same('123456789', $params['MERCHANTNUMBER']);
        Assert::same(TestPresenter::RESPONSE_URL, $params['URL']);
        Assert::true(self::verifySignature(implode('|', $request->getDigestParams()), $params['DIGEST']));
    }


    public function testServicesAreRegisteredUnderTheirNames(): void
    {
        $container = $this->prepareContainer(__DIR__ . '/../config/webpay.config.neon');

        // registered with add() by nette/di 4 and with addDefinition() by nette/di 3.2: name => [declared type, class]
        $services = [
            'paymentConfigFactory' => [PaymentConfigFactory::class, PaymentConfigFactory::class],
            'configFactory' => [ConfigFactory::class, ConfigFactory::class],
            'config' => [Config::class, Config::class],
            'paymentConfigProvider' => [PaymentConfigProvider::class, PaymentConfigProvider::class],
            'signerConfigProvider' => [SignerConfigProvider::class, SignerConfigProvider::class],
            'signerProvider' => [SignerProviderInterface::class, SignerProvider::class],
            'signerFactory' => [SignerFactoryInterface::class, SignerFactory::class],
            'requestFactory' => [RequestFactory::class, RequestFactory::class],
            'responseFactory' => [ResponseFactory::class, ResponseFactory::class],
            'responseProvider' => [ResponseProviderInterface::class, ResponseProvider::class],
            'controlFactory' => [GPWebPayControlFactory::class, GPWebPayControlFactory::class],
        ];
        foreach ($services as $name => [$type, $class]) {
            Assert::same($type, $container->getServiceType('gpwebpay.' . $name), $name);
            Assert::type($class, $container->getService('gpwebpay.' . $name), $name);
        }
    }

    public function testMultipleSettingCreated(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.multiple.config.neon');
        $container = $this->getContainer();

        $this->assertServices($container);

        /** @var PaymentConfigProvider $paymentConfig */
        $paymentConfig = $container->getByType(PaymentConfigProvider::class);
        Assert::same('czk', $paymentConfig->getDefaultGateway());
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber());
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber('czk'));
        Assert::same('123456780', (string)$paymentConfig->getMerchantNumber('eur'));
        Assert::same('1', (string)$paymentConfig->getDepositFlag('eur'));
    }

    public function testDistinctResponseUrlPerGateway(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.multiple.responseUrl.config.neon');
        $requestFactory = $this->getContainer()->getByType(RequestFactory::class);

        $czk = $requestFactory->create($this->createOperation(CurrencyEnum::CZK(), null))->getParams();
        Assert::same('123456789', $czk['MERCHANTNUMBER']);
        Assert::same('https://shop.example/payment/czk-response', $czk['URL']);
        Assert::same('1', $czk['DEPOSITFLAG']);

        $eur = $requestFactory->create($this->createOperation(CurrencyEnum::EUR(), 'eur'))->getParams();
        Assert::same('123456780', $eur['MERCHANTNUMBER']);
        Assert::same('https://shop.example/payment/eur-response', $eur['URL']);
        Assert::same('0', $eur['DEPOSITFLAG']);
        Assert::same('eur', $eur['MD']);
    }

    public function testResponseUrlOnlyForNonDefaultGateway(): void
    {
        $this->prepareContainerFromNeon(<<<'NEON'
            gpwebpay:
                czk:
                    privateKey: %appDir%/config/test.pem
                    privateKeyPassphrase: '1234567'
                    publicKey: %appDir%/config/test-pub.pem
                    url: https://test.3dsecure.gpwebpay.com/pgw/order.do
                    merchantNumber: '123456789'
                eur:
                    privateKey: %appDir%/config/test.pem
                    privateKeyPassphrase: '1234567'
                    publicKey: %appDir%/config/test-pub.pem
                    url: https://test.3dsecure.gpwebpay.com/pgw/order.do
                    merchantNumber: '123456780'
                    responseUrl: https://shop.example/payment/eur-response
                defaultGateway: czk
            NEON);
        $requestFactory = $this->getContainer()->getByType(RequestFactory::class);

        $eur = $requestFactory->create($this->createOperation(CurrencyEnum::EUR(), 'eur'))->getParams();
        Assert::same('https://shop.example/payment/eur-response', $eur['URL']);

        Assert::exception(
            fn () => $requestFactory->create($this->createOperation(CurrencyEnum::CZK(), null)),
            LogicException::class,
            '%a%response url%a%'
        );
    }

    public function testCheckoutOfNonDefaultGatewayUsesItsResponseUrl(): void
    {
        $this->prepareContainer(__DIR__ . '/../config/webpay.multiple.responseUrl.config.neon');
        $this->useSameOriginHttpRequest();
        $this->usePresenter('Test');

        $response = $this->runPresenterAction('fake', 'GET', ['do' => 'eurPayControl-checkout']);

        Assert::type(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        Assert::true(str_starts_with($response->getUrl(), self::GATEWAY_URL . '?'));
        parse_str((string)parse_url($response->getUrl(), PHP_URL_QUERY), $params);
        Assert::same('123456780', $params['MERCHANTNUMBER']);
        Assert::same('978', $params['CURRENCY']);
        Assert::same('eur', $params['MD']);
        Assert::same('https://shop.example/payment/eur-response', $params['URL']);
    }

    private function assertServices(Container $container): void
    {
        Assert::type(Config::class, $container->getByType(Config::class));
        Assert::type(PaymentConfigProvider::class, $container->getByType(PaymentConfigProvider::class));
        Assert::type(SignerConfigProvider::class, $container->getByType(SignerConfigProvider::class));
        Assert::same($container->getByType(Config::class)->getPaymentConfigProvider(), $container->getByType(PaymentConfigProvider::class));
        Assert::same($container->getByType(Config::class)->getSignerConfigProvider(), $container->getByType(SignerConfigProvider::class));

        Assert::type(ResponseProvider::class, $container->getByType(ResponseProviderInterface::class));
        Assert::type(ResponseFactory::class, $container->getByType(ResponseFactory::class));
        Assert::type(SignerProvider::class, $container->getByType(SignerProviderInterface::class));
        Assert::type(SignerFactoryInterface::class, $container->getByType(SignerFactoryInterface::class));

        // the payment request factory, not Nette\Http\RequestFactory
        $requestFactory = $container->getByType(RequestFactory::class);
        Assert::type(RequestFactory::class, $requestFactory);
        Assert::same($requestFactory, $container->getService('gpwebpay.requestFactory'));

        // the control factory passes this request factory to the controls it creates
        $controlFactory = $container->getByType(GPWebPayControlFactory::class);
        Assert::type(GPWebPayControlFactory::class, $controlFactory);
        $control = $controlFactory->create(TestPresenter::createOperation());
        Assert::same($requestFactory, (new ReflectionProperty(GPWebPayControl::class, 'requestFactory'))->getValue($control));
    }

    private function createOperation(CurrencyEnum $currency, ?string $gateway): Operation
    {
        return new Operation(
            orderNumber: new OrderNumber(TestPresenter::ORDER_NUMBER),
            amount: new AmountInPennies(100000),
            currency: new Currency($currency),
            gateway: $gateway
        );
    }
}

// phpcs:disable
$test = new GPWebPayExtensionTest();
$test->run();
// phpcs:enable
