<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 16:05
 */

namespace Pixidos\GPWebPay\Components;

use Pixidos\GPWebPay\Provider;
use Pixidos\GPWebPay\Operation;

/**
 * Class GPWebPayControlFactory
 * @package Pixidos\GPWebPay\Componets
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */

class GPWebPayControlFactory
{

    /**
     * @var  Provider $provider
     */
    private $provider;

    /**
     * GPWebPayControlFactory constructor.
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param Operation $operation
     * @return GPWebPayControl
     */
    public function create(Operation $operation, \Nette\ComponentModel\IContainer $control = NULL, $name = NULL )
    {
        return new GPWebPayControl($operation, $this->provider, $control, $name);
    }

}