<?php declare(strict_types=1);

/**
 * Test:  Pixidos\GPWebPay\Components\GPWebPayControl
 * @testCase GPWebPay\Tests\Components\GPWebPayControlTest
 */

namespace GPWebPay\Tests\Components;

use GPWebPay\Tests\GPWebPayTestCase;
use Nette\Application\AbortException;
use Nette\Application\UI\ITemplateFactory;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Data\RequestInterface;
use Tester\Assert;

/**
 * Class GPWebPayControlTest
 * @package GPWebPay\Tests\Components
 * @author  Ondra Votava <ondra@votava.dev>
 */

require_once __DIR__ . '/../bootstrap.php';

class GPWebPayControlTest extends GPWebPayTestCase
{

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
        $called = false;
        $control->onCheckout[] = static function (GPWebPayControl $control, RequestInterface $request) use (&$called) {
            $called = true;
        };

        Assert::false($called);
        try {
            $control->handleCheckout();
        } catch (AbortException $e) {
            Assert::true($called);
        }
    }


    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareContainer(sprintf(__DIR__ . '/../config/webpay.config.neon'));
        $this->usePresenter('Test');
        $this->presenter['payControl'];
    }

    private function createControl(): GPWebPayControl
    {
        /** @var GPWebPayControl $control */
        $control = $this->presenter['payControl'];
        /** @var ITemplateFactory $templateFactory */
        $templateFactory = $this->getContainer()->getByType(ITemplateFactory::class);
        $control->setTemplateFactory($templateFactory);

        return $control;
    }

}

(new GPWebPayControlTest())->run();
