<?php
/**
 * Created by PhpStorm.
 * User: Ondra Votava
 * Date: 21.10.2015
 * Time: 16:05
 */

namespace Pixidos\GPWebPay\Components;

use Pixidos\GPWebPay\Intefaces\IOperation;
use Pixidos\GPWebPay\Intefaces\IProvider;

/**
 * Class GPWebPayControlFactory
 * @package Pixidos\GPWebPay\Componets
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
class GPWebPayControlFactory
{
    
    /**
     * @var  IProvider $provider
     */
    private $provider;
    
    /**
     * GPWebPayControlFactory constructor.
     *
     * @param IProvider $provider
     */
    public function __construct(IProvider $provider)
    {
        $this->provider = $provider;
    }
    
    /**
     * @param IOperation                            $operation
     * @param \Nette\ComponentModel\IContainer|null $control
     * @param null                                  $name
     *
     * @return GPWebPayControl
     */
    public function create(IOperation $operation, \Nette\ComponentModel\IContainer $control = null, $name = null)
    {
        return new GPWebPayControl($operation, $this->provider, $control, $name);
    }
    
}
