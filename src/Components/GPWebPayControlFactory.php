<?php declare(strict_types=1);

namespace Pixidos\GPWebPay\Components;

use Nette\ComponentModel\IContainer;
use Pixidos\GPWebPay\Data\IOperation;
use Pixidos\GPWebPay\IProvider;

/**
 * Class GPWebPayControlFactory
 * @package Pixidos\GPWebPay\Componets
 * @author Ondra Votava <ondra@votava.dev>
 */

class GPWebPayControlFactory
{

    /**
     * @var  IProvider $provider
     */
    private $provider;

    /**
     * GPWebPayControlFactory constructor.
     * @param IProvider $provider
     */
    public function __construct(IProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param IOperation $operation
     * @return GPWebPayControl
     */
    public function create(IOperation $operation): GPWebPayControl
    {
        return new GPWebPayControl($operation, $this->provider);
    }

}
