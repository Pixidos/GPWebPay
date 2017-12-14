<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 13:10
 */

namespace Pixidos\GPWebPay\DI;

use Nette;
use Nette\Utils\Validators;
use Pixidos\GPWebPay\Components\GPWebPayControlFactory;
use Pixidos\GPWebPay\Intefaces\ISignerFactory;
use Pixidos\GPWebPay\Provider;
use Pixidos\GPWebPay\Settings;
use Pixidos\GPWebPay\SignerFactory;

/**
 * Class GPWebPayExtension
 * @package Pixidos\GPWebPay\DI
 * @author Ondra Votava <ondra.votava@pixidos.com>
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
                ->setClass(
                    Settings::class, [
                        self::PRIVATE_KEY => $this->getArray(self::PRIVATE_KEY, $config, $gatewayKey),
                        self::PRIVATE_KEY_PASSWORD => $this->getArray(self::PRIVATE_KEY_PASSWORD, $config, $gatewayKey),
                        self::PUBLIC_KEY => $config[self::PUBLIC_KEY],
                        self::URL => $config[self::URL],
                        self::MERCHANT_NUMBER => $this->getArray(self::MERCHANT_NUMBER, $config, $gatewayKey),
                        self::DEPOSIT_FLAG => $config[self::DEPOSIT_FLAG],
                        self::GATEWAY_KEY => $gatewayKey,
                    ]
                );
        
        $builder->addDefinition($this->prefix('signerFactory'))
                ->setClass(ISignerFactory::class)
                ->setFactory(SignerFactory::class, [$this->prefix('@settings')]);
        
        $builder->addDefinition($this->prefix('provider'))
                ->setClass(
                    Provider::class,
                    [
                        $this->prefix('@settings'),
                        $this->prefix('@signerFactory'),
                    ]
                );
        
        $builder->addDefinition($this->prefix('controlFactory'))
                ->setClass(GPWebPayControlFactory::class, [$this->prefix('@provider')]);
        
    }
    
    /**
     * @param Nette\Configurator $configurator
     */
    public static function register(Nette\Configurator $configurator)
    {
        $configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
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
        if (\is_array($config[$key])) {
            return $config[$key];
        }
        
        return [$gatewayKey => $config[$key]];
    }
}
