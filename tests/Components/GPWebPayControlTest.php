<?php

/**
 * Test:  Pixidos\GPWebPay\Components\GPWebPayControl
 * @testCase GPWebPay\Tests\Components\GPWebPayControlTest
 */

declare(strict_types=1);

namespace GPWebPay\Tests\Components;

use GPWebPay\Tests\GPWebPayTestCase;
use GPWebPay\Tests\TestPresenter;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Data\RequestInterface;
use Tester\Assert;

/**
 * Class GPWebPayControlTest
 * @package GPWebPay\Tests\Components
 * @author  Ondra Votava <ondra@votava.dev>
 */

// phpcs:disable
require_once __DIR__ . '/../bootstrap.php';
// phpcs:enable

class GPWebPayControlTest extends GPWebPayTestCase
{
    private const GATEWAY_URL = 'https://test.3dsecure.gpwebpay.com/pgw/order.do';

    public function testControlRender(): void
    {
        $control = $this->createControl();
        $this->runPresenterAction('fake');

        ob_start();
        $control->render(['class' => 'test-class', 'id' => 'testId'], 'Pay Order');
        $html = ob_get_clean();
        Assert::match('#^<a href.*#i', $html);
        Assert::match('#class="test-class"#i', $html);
        Assert::match('#id="testId"#i', $html);
        Assert::match('#>Pay Order#', $html);
    }

    public function testControlRendersRepeatedly(): void
    {
        $control = $this->createControl();
        $this->runPresenterAction('fake');

        ob_start();
        $control->render(['class' => 'top'], 'Pay now');
        $first = (string)ob_get_clean();

        ob_start();
        $control->render(['class' => 'bottom', 'id' => 'pay-bottom'], 'Pay <b>again</b>');
        $second = (string)ob_get_clean();

        Assert::same(1, preg_match('#^<a href="([^"]+)"#', $first, $firstLink));
        Assert::contains('do=payControl-checkout', $firstLink[1]);
        Assert::match('#class="top"#', $first);
        Assert::match('#>Pay now</a>$#', $first);

        Assert::same(1, preg_match('#^<a href="([^"]+)"#', $second, $secondLink));
        Assert::same($firstLink[1], $secondLink[1]);
        Assert::match('#class="bottom"#', $second);
        Assert::match('#id="pay-bottom"#', $second);
        Assert::notContains('top', $second);
        // the text is escaped, as before
        Assert::match('#>Pay &lt;b&gt;again&lt;/b&gt;</a>$#', $second);
    }

    public function testPresenterTemplateRendersControlTwice(): void
    {
        $response = $this->runPresenterAction('buttons');

        Assert::type(TextResponse::class, $response);
        /** @var TextResponse $response */
        $html = (string)$response->getSource();
        Assert::same(2, preg_match_all('#<a href="[^"]*do=payControl-checkout[^"]*"#', $html));
        Assert::match('%A?%<header><a href="%a%" class="btn btn-primary">Pay by card</a>%A?%</header>%A%', $html);
        Assert::match('%A%<footer><a href="%a%" class="btn btn-secondary">Pay</a>%A?%</footer>%A?%', $html);
    }

    public function testSetCustomTemplate(): void
    {
        $control = $this->createControl();
        $control->setTemplateFile(__DIR__ . '/templates/gpWebPayControl.latte');
        $this->runPresenterAction('fake');

        ob_start();
        $control->render(['id' => 'testId'], 'Pay Order');
        $html = ob_get_clean();
        Assert::match('#^<a href.*#i', $html);
        Assert::match('#class="test"#i', $html);
        Assert::match('#id="testId"#i', $html);
        Assert::match('#>Pay Order#', $html);
    }

    public function testHandleCheckout(): void
    {
        $control = $this->createControl();
        $requests = [];
        $control->onCheckout[] = static function (GPWebPayControl $control, RequestInterface $request) use (&$requests): void {
            $requests[] = $request;
        };

        $response = $this->runPresenterAction('fake', 'GET', ['do' => 'payControl-checkout']);

        Assert::count(1, $requests);
        Assert::type(RedirectResponse::class, $response);
        /** @var RedirectResponse $response */
        $url = $response->getUrl();
        Assert::same($requests[0]->getRequestUrl(), $url);
        Assert::true(str_starts_with($url, self::GATEWAY_URL . '?'));

        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        Assert::same('123456789', $params['MERCHANTNUMBER']);
        Assert::same('CREATE_ORDER', $params['OPERATION']);
        Assert::same(TestPresenter::ORDER_NUMBER, $params['ORDERNUMBER']);
        Assert::same('100000', $params['AMOUNT']);
        Assert::same('203', $params['CURRENCY']);
        Assert::same('1', $params['DEPOSITFLAG']);
        Assert::same(TestPresenter::RESPONSE_URL, $params['URL']);

        // signed with the configured private key, in the order of the sent fields
        $digest = $params['DIGEST'];
        unset($params['DIGEST']);
        Assert::true(self::verifySignature(implode('|', $params), $digest));
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareContainer(__DIR__ . '/../config/webpay.config.neon');
        $this->useSameOriginHttpRequest();
        $this->usePresenter('Test');
    }

    private function createControl(): GPWebPayControl
    {
        if (null === $this->presenter) {
            throw new \RuntimeException('');
        }
        /** @var GPWebPayControl $control */
        $control = $this->presenter['payControl'];
        $templateFactory = $this->getContainer()->getByType(TemplateFactory::class);
        $control->setTemplateFactory($templateFactory);

        return $control;
    }
}

// phpcs:disable
(new GPWebPayControlTest())->run();
// phpcs:enable
