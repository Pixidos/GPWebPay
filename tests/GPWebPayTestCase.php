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
use Nette\Bridges\ApplicationDI\ApplicationExtension;
use Nette\Bridges\ApplicationDI\LatteExtension;
use Nette\Bridges\ApplicationDI\RoutingExtension;
use Nette\Bridges\CacheDI\CacheExtension;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\Bridges\HttpDI\SessionExtension;
use Nette\ComponentModel\Component;
use Nette\DI\Container;
use Nette\DI\Extensions\ExtensionsExtension;
use Nette\DI\Extensions\InjectExtension;
use Nette\Http\UrlScript;
use Pixidos\GPWebPay\DI\GPWebPayExtension;
use ReflectionProperty;
use RuntimeException;
use Tester\TestCase;

abstract class GPWebPayTestCase extends TestCase
{
    /**
     * Private key of tests/config/test.pem; it plays the role of GP webpay when signing test responses.
     */
    private const TEST_PRIVATE_KEY_PASSPHRASE = '1234567';

    protected ?Presenter $presenter = null;

    private ?Container $container = null;

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
     * @param string               $configNeon
     * @param array<string, mixed> $dynamicParameters
     *
     * @return Container
     */
    protected function prepareContainer(string $configNeon, array $dynamicParameters = []): Container
    {
        if (!class_exists(Nette\Bootstrap\Configurator::class)) {
            return $this->container = self::createContainerWithoutBootstrap($configNeon, $dynamicParameters);
        }

        $config = new Nette\Bootstrap\Configurator();
        $config->setTempDirectory(TEMP_DIR); //phpstan-ignore-line
        $config->addStaticParameters(['container' => ['class' => 'SystemContainer_' . md5(TEMP_DIR)]]);
        $config->addConfig(sprintf(__DIR__ . '/config/nette-reset.neon'));
        GPWebPayExtension::register($config);
        $config->addConfig($configNeon);
        $config->addDynamicParameters($dynamicParameters);

        return $this->container = $config->createContainer();
    }

    /**
     * Compiles the container without nette/bootstrap, as an application does when it has no Configurator.
     * The development branch of nette/bootstrap (4.0-dev) still requires nette/di ^3.2, so it cannot be
     * installed together with nette/di 4.0-dev. The extension is registered in the extensions section,
     * as described in docs/configuration.rst.
     *
     * @param array<string, mixed> $dynamicParameters
     */
    private static function createContainerWithoutBootstrap(string $configNeon, array $dynamicParameters): Container
    {
        $tempDir = TEMP_DIR; //phpstan-ignore-line
        $loader = new Nette\DI\ContainerLoader($tempDir . '/cache/nette.di', true);
        $class = $loader->load(
            static function (Nette\DI\Compiler $compiler) use ($tempDir, $configNeon, $dynamicParameters): void {
                $compiler->addExtension('application', new ApplicationExtension(false, null, $tempDir . '/cache/nette.application'));
                $compiler->addExtension('cache', new CacheExtension($tempDir . '/cache'));
                $compiler->addExtension('extensions', new ExtensionsExtension());
                $compiler->addExtension('http', new HttpExtension(true));
                $compiler->addExtension('inject', new InjectExtension());
                $compiler->addExtension('latte', new LatteExtension($tempDir . '/cache/latte'));
                $compiler->addExtension('routing', new RoutingExtension());
                $compiler->addExtension('session', new SessionExtension(false, true));
                $compiler->addConfig(['parameters' => [
                    'appDir' => __DIR__,
                    'tempDir' => $tempDir,
                    'debugMode' => false,
                    'productionMode' => true,
                    'consoleMode' => true,
                ]]);
                $compiler->loadConfig(__DIR__ . '/config/nette-reset.neon');
                $compiler->addConfig(['extensions' => ['gpwebpay' => GPWebPayExtension::class]]);
                $compiler->loadConfig($configNeon);
                $compiler->setDynamicParameterNames(array_keys($dynamicParameters));
            },
            [$configNeon, array_keys($dynamicParameters)]
        );

        /** @var Container $container */
        $container = new $class($dynamicParameters);
        $container->initialize();

        return $container;
    }

    /**
     * Writes the NEON configuration of the gpwebpay section to a temporary file and creates the container.
     * Paths may use %appDir%, which is the tests directory.
     *
     * @param array<string, mixed> $dynamicParameters
     */
    protected function prepareContainerFromNeon(string $neon, array $dynamicParameters = []): Container
    {
        $file = TEMP_DIR . '/config-' . md5($neon) . '.neon'; //phpstan-ignore-line
        if (false === file_put_contents($file, $neon)) {
            throw new RuntimeException(sprintf('Cannot write "%s".', $file));
        }

        return $this->prepareContainer($file, $dynamicParameters);
    }

    /**
     * Replaces the HTTP request of the container. Call it before usePresenter().
     *
     * @param array<string, string> $query
     * @param array<string, mixed>  $post
     * @param array<string, string> $headers
     * @param array<string, string> $cookies
     */
    protected function useHttpRequest(
        string $method,
        array $query = [],
        array $post = [],
        array $headers = [],
        array $cookies = []
    ): void {
        $query = [] === $query ? '' : '?' . http_build_query($query);
        $request = new Nette\Http\Request(
            new UrlScript('https://shop.example/payment/response' . $query, '/'),
            post: $post,
            cookies: $cookies,
            headers: $headers,
            method: $method
        );

        $container = $this->getContainer();
        $container->removeService('http.request');
        $container->addService('http.request', $request);
    }

    /**
     * A navigation from a page of the same site, as when the customer clicks the payment button.
     */
    protected function useSameOriginHttpRequest(): void
    {
        // nette/application 3.2+ accepts signals only from the same origin:
        // Sec-Fetch-Site in current browsers, the strict cookie of nette/http otherwise
        $this->useHttpRequest('GET', [], [], ['Sec-Fetch-Site' => 'same-origin'], ['_nss' => '1']);
    }

    protected function usePresenter(string $name): void
    {
        $container = $this->getContainer();
        /** @var IPresenterFactory $presenterFactory */
        $presenterFactory = $container->getByType(IPresenterFactory::class);
        $presenter = $presenterFactory->createPresenter($name);
        if ($presenter instanceof Presenter) {
            // nette/application 3.1 names the constant INVALID_LINK_EXCEPTION
            $presenter->invalidLinkMode = defined(Presenter::class . '::InvalidLinkException')
                ? constant(Presenter::class . '::InvalidLinkException')
                : constant(Presenter::class . '::INVALID_LINK_EXCEPTION');
            $presenter->autoCanonicalize = false;
            // force the name to the presenter
            $refl = new ReflectionProperty(Component::class, 'name');
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

    /**
     * Adds DIGEST and DIGEST1 the way GP webpay signs a response: the fields in the order given,
     * and for DIGEST1 the same fields followed by the merchant number.
     *
     * @param array<string, string> $fields response fields in the signing order of the HTTP API
     *
     * @return array<string, string>
     */
    protected static function signResponse(array $fields, string $merchantNumber): array
    {
        $fields['DIGEST'] = self::sign(implode('|', $fields));
        $fields['DIGEST1'] = self::sign(implode('|', array_diff_key($fields, ['DIGEST' => true])) . '|' . $merchantNumber);

        return $fields;
    }

    protected static function verifySignature(string $data, string $digest): bool
    {
        $key = openssl_pkey_get_public((string)file_get_contents(__DIR__ . '/config/test-pub.pem'));
        if (false === $key) {
            throw new RuntimeException('Cannot read the test public key.');
        }

        return 1 === openssl_verify($data, (string)base64_decode($digest, true), $key, OPENSSL_ALGO_SHA1);
    }

    private static function sign(string $data): string
    {
        $key = openssl_pkey_get_private(
            (string)file_get_contents(__DIR__ . '/config/test.pem'),
            self::TEST_PRIVATE_KEY_PASSPHRASE
        );
        if (false === $key || !openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA1)) {
            throw new RuntimeException('Cannot sign with the test private key.');
        }

        return base64_encode($signature);
    }
}
