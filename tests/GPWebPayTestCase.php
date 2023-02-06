<?php

/**
 * Class GPWebPayTestCase
 * @package GPWebPay\Tests
 * @author  Ondra Votava <ondra@votava.dev>
 */

declare(strict_types=1);

namespace GPWebPay\Tests;

use LogicException;
use Nette;
use Nette\Application\IPresenterFactory;
use Nette\Application\Request;
use Nette\Application\Response;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;
use Nette\DI\Container;
use Pixidos\GPWebPay\DI\GPWebPayExtension;
use ReflectionProperty;
use Tester\TestCase;

abstract class GPWebPayTestCase extends TestCase
{
    protected ?Presenter $presenter;

    private ?Container $container;

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        if (null === $this->container) {
            throw new LogicException(
                'First need run ' . static::class . '::prepareContainer() to initialize the container.'
            );
        }

        return $this->container;
    }

    /**
     * @param string $configNeon
     *
     * @return Container
     */
    protected function prepareContainer(string $configNeon): Container
    {
        $config = new Nette\Bootstrap\Configurator();
        $config->setTempDirectory(TEMP_DIR);
        $config->addStaticParameters(['container' => ['class' => 'SystemContainer_' . md5(TEMP_DIR)]]);
        $config->addConfig(sprintf(__DIR__ . '/config/nette-reset.neon'));
        GPWebPayExtension::register($config);
        $config->addConfig($configNeon);

        return $this->container = $config->createContainer();
    }

    protected function usePresenter(string $name): void
    {
        $container = $this->getContainer();
        /** @var IPresenterFactory $presenterFactory */
        $presenterFactory = $container->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter($name);
        if ($presenter instanceof Presenter) {
            $presenter->invalidLinkMode = $presenter::INVALID_LINK_EXCEPTION;
            $presenter->autoCanonicalize = false;
            // force the name to the presenter
            $refl = new ReflectionProperty(Component::class, 'name');
            $refl->setAccessible(true);
            $refl->setValue($presenter, $name);
            $this->presenter = $presenter;
        }
    }

    /**
     * @param string $action
     * @param string $method
     * @param mixed[] $params
     * @param mixed[] $post
     *
     * @return Response
     */
    protected function runPresenterAction(
        string $action,
        string $method = 'GET',
        array  $params = [],
        array  $post = []
    ): Response {

        if (null === $this->presenter) {
            throw new LogicException(
                'Call first ' . static::class . '::usePresenter($name) to initialize the presenter.'
            );
        }
        $request = new Request(
            (string)$this->presenter->getName(),
            $method,
            ['action' => $action] + $params,
            $post
        );

        return $this->presenter->run($request);
    }
}
