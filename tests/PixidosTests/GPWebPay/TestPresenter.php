<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 03.04.2017
 * Time: 9:21
 */

namespace PixidosTests\GPWebPay;

use Nette\Application\Responses\TextResponse;
use Nette\Application\UI\Presenter;
use Pixidos\GPWebPay\Components\GPWebPayControl;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;

/**
 * Class TestPresenter
 * @package PixidosTests\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class TestPresenter extends Presenter
{

	private $gpWebPayControlFactory;

	public function __construct(GPWebPayControlFactory $controlFactory	)
	{
		$this->gpWebPayControlFactory = $controlFactory;
	}

	/**
	 * @return GPWebPayControl
	 */
	protected function createComponentPayControl()
	{
		$control = $this->gpWebPayControlFactory->create(TestHelpers::createOperation());
		return $control;
	}

	public function renderFake()
	{
		$response = new TextResponse('text');
		$this->sendResponse($response);
	}
}