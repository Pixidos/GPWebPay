<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 31.03.2017
 * Time: 14:02
 */

namespace PixidosTests\GPWebPay;

/**
 * Class GPWebPayTestCase
 * @package PixidosTests\GPWebPay
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

use Pixidos\GPWebPay\DI\GPWebPayExtension;
use Nette;
use Tester;

abstract class GPWebPayTestCase extends Tester\TestCase
{
	/**
	 * @var Nette\DI\Container
	 */
	private $container;

	/**
	 * @var Nette\Application\UI\Presenter
	 */
	protected $presenter;


	/**
	 * @return Nette\DI\Container
	 */
	protected function prepareContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters(array('container' => array('class' => 'SystemContainer_' . md5(TEMP_DIR))));
		$config->addConfig(sprintf(__DIR__ . '/../nette-reset.neon'));
		GPWebPayExtension::register($config);
		$config->addConfig(sprintf(__DIR__ . '/config/webpay.config.neon'));

		return $this->container = $config->createContainer();
	}

	/**
	 * @return Nette\DI\Container
	 */
	public function getContainer()
	{
		if($this->container == NULL){
			throw new \LogicException('First need run ' .  get_called_class() .'::prepareContainer() to initialize the container.');
		}
		return $this->container;
	}


	protected function usePresenter($name)
	{
		$sl = $this->getContainer();
		$presenterFactory = $sl->getByType(Nette\Application\IPresenterFactory::class);
		$presenter = $presenterFactory->createPresenter($name);
		if ($presenter instanceof Nette\Application\UI\Presenter) {
			$presenter->invalidLinkMode = $presenter::INVALID_LINK_EXCEPTION;
			$presenter->autoCanonicalize = FALSE;
			// force the name to the presenter
			$refl = new \ReflectionProperty(Nette\ComponentModel\Component::class, 'name');
			$refl->setAccessible(TRUE);
			$refl->setValue($presenter, $name);
		}
		$this->presenter = $presenter;
	}



	protected function tearDown()
	{
		parent::tearDown(); // TODO: Change the autogenerated stub
		//\Tester\Helpers::purge(TEMP_DIR);
		//rmdir(TEMP_DIR);
	}

	protected function runPresenterAction($action, $method = 'GET', $params = [], $post = [])
	{
		if ($this->presenter === NULL) {
			throw new \LogicException('Call first ' . get_called_class() . '::usePresenter($name) to initialize the presenter.');
		}
		if (is_array($method)) {
			$post = $params;
			$params = $method;
			$method = 'GET';
		}
//		$requestStack = $this->getContainer()->getByType(Kdyby\RequestStack\RequestStack::class);
//		$url = (new Nette\Http\UrlScript('https://kdyby.org'))->setQuery($params);
//		$requestStack->pushRequest(new Nette\Http\Request($url, NULL, $post, NULL, NULL, NULL, $method));
		$request = new Nette\Application\Request($this->presenter->getName(), $method, ['action' => $action] + $params, $post);
		return $this->presenter->run($request);
	}
}