<?php

/**
 * Test: configuration validation of Pixidos\GPWebPay\DI\GPWebPayExtension
 * @testCase GPWebPay\Tests\DI\GPWebPayExtensionConfigTest
 */

declare(strict_types=1);

namespace GPWebPay\Tests\DI;

use GPWebPay\Tests\GPWebPayTestCase;
use Nette\DI\InvalidConfigurationException;
use Pixidos\GPWebPay\Config\PaymentConfigProvider;
use Pixidos\GPWebPay\Config\SignerConfigProvider;
use Pixidos\GPWebPay\Data\Operation;
use Pixidos\GPWebPay\Enum\Currency as CurrencyEnum;
use Pixidos\GPWebPay\Factory\RequestFactory;
use Pixidos\GPWebPay\Param\AmountInPennies;
use Pixidos\GPWebPay\Param\Currency;
use Pixidos\GPWebPay\Param\OrderNumber;
use Pixidos\GPWebPay\Param\ResponseUrl;
use Tester\Assert;

// phpcs:disable
require_once __DIR__ . '/../bootstrap.php';
// phpcs:enable

class GPWebPayExtensionConfigTest extends GPWebPayTestCase
{
    private const SINGLE = <<<'NEON'
        gpwebpay:
            privateKey: %appDir%/config/test.pem
            privateKeyPassphrase: %passphrase%
            publicKey: %appDir%/config/test-pub.pem
            url: https://test.3dsecure.gpwebpay.com/pgw/order.do
            merchantNumber: %merchantNumber%
        %extra%
        NEON;

    private const MULTIPLE = <<<'NEON'
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
                merchantNumber: %merchantNumber%
            defaultGateway: czk
        NEON;

    public function testUnquotedNumericMerchantNumber(): void
    {
        $this->assertConfigError(
            self::single("'1234567'", '123456789'),
            "'gpwebpay › merchantNumber'",
            'write numbers in quotes'
        );
    }

    public function testUnquotedNumericPassphrase(): void
    {
        $this->assertConfigError(
            self::single('1234567', "'123456789'"),
            "'gpwebpay › privateKeyPassphrase'",
            'write numbers in quotes'
        );
    }

    public function testUnquotedNumericMerchantNumberOfGateway(): void
    {
        $this->assertConfigError(
            str_replace('%merchantNumber%', '123456780', self::MULTIPLE),
            "'gpwebpay › eur › merchantNumber'",
            'write numbers in quotes'
        );
    }

    public function testMissingItem(): void
    {
        $neon = str_replace("    publicKey: %appDir%/config/test-pub.pem\n", '', self::single("'1234567'", "'123456789'"));

        $this->assertConfigError($neon, "'gpwebpay › publicKey'", 'missing');
    }

    public function testUnknownItem(): void
    {
        $this->assertConfigError(
            self::single("'1234567'", "'123456789'", 'responseURL: https://shop.example/r'),
            "'gpwebpay › responseURL'"
        );
    }

    public function testInvalidDepositFlag(): void
    {
        $this->assertConfigError(
            self::single("'1234567'", "'123456789'", 'depositFlag: 2'),
            "'gpwebpay › depositFlag'",
            '0 or 1'
        );
    }

    public function testDepositFlagAndResponseUrl(): void
    {
        $this->prepareContainerFromNeon(
            self::single("'1234567'", "'123456789'", "depositFlag: 0\n    responseUrl: https://shop.example/payment/response")
        );
        $paymentConfig = $this->getContainer()->getByType(PaymentConfigProvider::class);

        Assert::same('0', (string)$paymentConfig->getDepositFlag());
        Assert::same('https://shop.example/payment/response', (string)$paymentConfig->getResponseUrl());
    }

    public function testValuesComputedAtRuntimeAreAccepted(): void
    {
        // a PHP call and a dynamic parameter, both resolved when the container runs
        $this->prepareContainerFromNeon(
            self::single('::trim(" 1234567 ")', '%gpwebpayMerchant%'),
            ['gpwebpayMerchant' => '123456789']
        );
        $container = $this->getContainer();

        Assert::same('123456789', (string)$container->getByType(PaymentConfigProvider::class)->getMerchantNumber());
        Assert::same('1234567', $container->getByType(SignerConfigProvider::class)->getConfig()->getPrivateKeyPassword());
    }

    public function testGatewayKeysAreCaseInsensitive(): void
    {
        $neon = str_replace(
            ['    czk:', '    eur:', 'defaultGateway: czk'],
            ['    CZK:', '    EUR:', 'defaultGateway: CZK'],
            str_replace('%merchantNumber%', "'123456780'", self::MULTIPLE)
        );
        $this->prepareContainerFromNeon($neon);
        $container = $this->getContainer();

        $paymentConfig = $container->getByType(PaymentConfigProvider::class);
        Assert::same('czk', $paymentConfig->getDefaultGateway());
        Assert::same('123456789', (string)$paymentConfig->getMerchantNumber());

        $params = $container->getByType(RequestFactory::class)->create(
            new Operation(
                orderNumber: new OrderNumber('123456'),
                amount: new AmountInPennies(100000),
                currency: new Currency(CurrencyEnum::EUR()),
                gateway: 'EUR',
                responseUrl: new ResponseUrl('https://shop.example/payment/response')
            )
        )->getParams();
        Assert::same('123456780', $params['MERCHANTNUMBER']);
    }

    private function assertConfigError(string $neon, string ...$expectedParts): void
    {
        $exception = Assert::exception(
            fn () => $this->prepareContainerFromNeon($neon),
            InvalidConfigurationException::class
        );
        // nette/schema separates the path items with non-breaking spaces
        $message = str_replace("\u{a0}", ' ', $exception->getMessage());
        foreach ($expectedParts as $part) {
            Assert::contains($part, $message);
        }
    }

    private static function single(string $passphrase, string $merchantNumber, string $extra = ''): string
    {
        return str_replace(
            ['%passphrase%', '%merchantNumber%', '%extra%'],
            [$passphrase, $merchantNumber, '' === $extra ? '' : '    ' . $extra],
            self::SINGLE
        );
    }
}

// phpcs:disable
(new GPWebPayExtensionConfigTest())->run();
// phpcs:enable
