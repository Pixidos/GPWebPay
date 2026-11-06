<?php

/**
 * Test: the response handler documented in docs/usage.rst (TestPresenter::actionProcessResponse)
 * @testCase GPWebPay\Tests\ResponseHandlingTest
 */

declare(strict_types=1);

namespace GPWebPay\Tests;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Tester\Assert;

// phpcs:disable
require_once __DIR__ . '/bootstrap.php';
// phpcs:enable

class ResponseHandlingTest extends GPWebPayTestCase
{
    private const MERCHANT_NUMBER = '123456789';
    private const EUR_MERCHANT_NUMBER = '123456780';

    public function testPostResponse(): void
    {
        $fields = self::signResponse(self::fields(), self::MERCHANT_NUMBER);

        Assert::same('paid 123456 default', $this->handle('POST', [], $fields));
    }

    public function testGetResponse(): void
    {
        $fields = self::signResponse(self::fields(), self::MERCHANT_NUMBER);

        Assert::same('paid 123456 default', $this->handle('GET', $fields, []));
    }

    public function testPostResponseIgnoresQuery(): void
    {
        // authentic declined payment in the form body; the query must not be mixed into it
        $fields = self::signResponse(self::fields(['PRCODE' => '30', 'SRCODE' => '1002', 'RESULTTEXT' => 'DECLINED']), self::MERCHANT_NUMBER);
        $query = ['PRCODE' => '0', 'SRCODE' => '0', 'RESULTTEXT' => 'OK'];

        Assert::same('failed 123456 default', $this->handle('POST', $query, $fields));
    }

    public function testDeclinedResponse(): void
    {
        $fields = self::signResponse(self::fields(['PRCODE' => '50', 'SRCODE' => '0', 'RESULTTEXT' => 'CANCELLED']), self::MERCHANT_NUMBER);

        Assert::same('failed 123456 default', $this->handle('POST', [], $fields));
    }

    public function testTamperedResponseIsRejected(): void
    {
        $fields = self::signResponse(self::fields(), self::MERCHANT_NUMBER);
        $fields['ORDERNUMBER'] = '123457';

        $this->assertRejected('POST', [], $fields);
    }

    public function testResponseForAnotherMerchantIsRejected(): void
    {
        $fields = self::signResponse(self::fields(), '987654321');

        $this->assertRejected('POST', [], $fields);
    }

    public function testMalformedResponseIsRejected(): void
    {
        $fields = self::signResponse(self::fields(), self::MERCHANT_NUMBER);
        unset($fields['OPERATION']);

        $this->assertRejected('POST', [], $fields);
    }

    public function testResponseOfNonDefaultGateway(): void
    {
        $fields = self::signResponse(self::fields(['MD' => 'eur']), self::EUR_MERCHANT_NUMBER);

        Assert::same('paid 123456 eur', $this->handle('POST', [], $fields, __DIR__ . '/config/webpay.multiple.responseUrl.config.neon'));
    }

    public function testResponseOfNonDefaultGatewaySignedForDefaultMerchantIsRejected(): void
    {
        $fields = self::signResponse(self::fields(['MD' => 'eur']), self::MERCHANT_NUMBER);

        $this->assertRejected('POST', [], $fields, __DIR__ . '/config/webpay.multiple.responseUrl.config.neon');
    }

    /**
     * @param array<string, string> $override
     *
     * @return array<string, string>
     */
    private static function fields(array $override = []): array
    {
        $fields = [
            'OPERATION' => 'CREATE_ORDER',
            'ORDERNUMBER' => TestPresenter::ORDER_NUMBER,
            'MD' => null,
            'PRCODE' => '0',
            'SRCODE' => '0',
            'RESULTTEXT' => 'OK',
        ];

        // keep the signing order of the HTTP API and drop fields without a value
        return array_filter(array_replace($fields, $override), static fn (?string $value): bool => null !== $value);
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $post
     */
    private function handle(string $method, array $query, array $post, string $config = __DIR__ . '/config/webpay.config.neon'): string
    {
        $this->prepareContainer($config);
        $this->useHttpRequest($method, $query, $post);
        $this->usePresenter('Test');

        $response = $this->runPresenterAction('processResponse', $method, $query, $post);
        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */

        return (string)$response->getSource();
    }

    /**
     * @param array<string, string> $query
     * @param array<string, string> $post
     */
    private function assertRejected(string $method, array $query, array $post, string $config = __DIR__ . '/config/webpay.config.neon'): void
    {
        $exception = Assert::exception(
            fn () => $this->handle($method, $query, $post, $config),
            BadRequestException::class,
            'Invalid payment response.'
        );
        Assert::same(400, $exception->getCode());
    }
}

// phpcs:disable
(new ResponseHandlingTest())->run();
// phpcs:enable
