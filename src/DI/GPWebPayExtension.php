<?php declare(strict_types=1);

namespace Pixidos\GPWebPay\DI;

use Nette;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\Utils\Validators;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Factory\RequestFactory;
use Pixidos\GPWebPay\Factory\ResponseFactory;
use Pixidos\GPWebPay\Provider;
use Pixidos\GPWebPay\Settings\Settings;
use Pixidos\GPWebPay\Settings\SettingsFactory;
use Pixidos\GPWebPay\Signer\ISignerFactory;
use Pixidos\GPWebPay\Signer\SignerFactory;

use function is_array;

/**
 * Class GPWebPayExtension
 * @package Pixidos\GPWebPay\DI
 * @author  Ondra Votava <ondra@votava.dev>
 */
class GPWebPayExtension extends Nette\DI\CompilerExtension
{

    private const PRIVATE_KEY = 'privateKey';
    private const PRIVATE_KEY_PASSWORD = 'privateKeyPassword';
    private const PUBLIC_KEY = 'publicKey';
    private const URL = 'url';
    private const MERCHANT_NUMBER = 'merchantNumber';
    private const DEPOSIT_FLAG = 'depositFlag';
    private const GATEWAY_KEY = 'gatewayKey';

    public $defaults = [
        self::DEPOSIT_FLAG => 1,
        self::GATEWAY_KEY => 'czk',
    ];

    /**
     * @throws Nette\Utils\AssertionException
     */
    public function loadConfiguration(): void
    {
        $config = $this->getConfig();

        $defaults = array_diff_key($this->defaults, $config);
        foreach ($defaults as $key => $val) {
            $config[$key] = $this->defaults[$key];
        }

        Validators::assertField($config, self::PRIVATE_KEY);
        Validators::assertField($config, self::PRIVATE_KEY_PASSWORD);
        Validators::assertField($config, self::PUBLIC_KEY);
        Validators::assertField($config, self::URL);
        Validators::assertField($config, self::MERCHANT_NUMBER);
        Validators::assertField($config, self::DEPOSIT_FLAG);
        Validators::assertField($config, self::GATEWAY_KEY);

        $builder = $this->getContainerBuilder();

        $gatewayKey = $config[self::GATEWAY_KEY];
        $builder->addDefinition($this->prefix('settings'))
            ->setType(Settings::class)
            ->setFactory(
                [SettingsFactory::class, 'create'],
                [
                    $this->getArray(self::PRIVATE_KEY, $config, $gatewayKey),
                    $this->getArray(self::PRIVATE_KEY_PASSWORD, $config, $gatewayKey),
                    $config[self::PUBLIC_KEY],
                    $config[self::URL],
                    $this->getArray(self::MERCHANT_NUMBER, $config, $gatewayKey),
                    $config[self::DEPOSIT_FLAG],
                    $gatewayKey,
                ]
            );
        $builder->addDefinition($this->prefix('requestFactory'))
            ->setType(RequestFactory::class);

        $builder->addDefinition($this->prefix('responseFactory'))
            ->setType(ResponseFactory::class);

        $builder->addDefinition($this->prefix('signerFactory'))
            ->setType(ISignerFactory::class)
            ->setFactory(SignerFactory::class, [$this->prefix('@settings')]);

        $builder->addDefinition($this->prefix('provider'))
            ->setType(Provider::class)
            ->setFactory(
                Provider::class,
                [
                    $this->prefix('@settings'),
                    $this->prefix('@signerFactory'),
                ]
            );

        $builder->addDefinition($this->prefix('controlFactory'))
            ->setFactory(GPWebPayControlFactory::class, [$this->prefix('@provider')]);
    }

    /**
     * @param Configurator $configurator
     */
    public static function register(Configurator $configurator): void
    {
        $configurator->onCompile[] = static function ($config, Compiler $compiler) {
            $compiler->addExtension('gpwebpay', new GPWebPayExtension());
        };
    }


    /**
     * @param string $key
     * @param array  $config
     * @param string $gatewayKey
     *
     * @return array
     */
    private function getArray(string $key, array $config, string $gatewayKey): array
    {
        if (is_array($config[$key])) {
            return $config[$key];
        }

        return [$gatewayKey => $config[$key]];
    }
}
