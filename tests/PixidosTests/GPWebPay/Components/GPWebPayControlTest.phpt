<?php
/**
 * Test:  Pixidos\GPWebPay\Components\GPWebPayControl
 * @testCase PixidosTests\GPWebPay\Components\GPWebPayControlTest
 */

namespace PixidosTests\GPWebPay\Components;

use Nette\Bridges\ApplicationLatte\Template;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use PixidosTests\GPWebPay\GPWebPayTestCase;
use Tester\Assert;
use Tester\DomQuery;

/**
 * Class GPWebPayControlTest
 * @package PixidosTests\GPWebPay\Components
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

require_once __DIR__ . '/../../bootstrap.php';

class GPWebPayControlTest extends GPWebPayTestCase
{

	protected function setUp()
	{
		parent::setUp();
		$this->prepareContainer();
		$this->usePresenter('Test');
		$this->presenter['payControl'];
	}

	public function testControlRender()
	{
		/** @var GPWebPayControl $control */
		$control = $this->presenter['payControl'];
		/** @var \Nette\Application\UI\ITemplateFactory $templateFactory */
		$templateFactory = $this->getContainer()->getByType('Nette\Application\UI\ITemplateFactory');
		$control->setTemplateFactory($templateFactory);

		$this->runPresenterAction('fake');

		ob_start();
		$control->render(['class' => 'test-class', 'id' => 'testId'], 'Pay Order');
		$html = ob_get_clean();
		Assert::match('#^<a href.*#i', $html);
		Assert::match('#class="test-class"#i', $html);
		Assert::match('#id="testId"#i', $html);
		Assert::match('#>Pay Order#', $html);

	}
}

(new GPWebPayControlTest())->run();