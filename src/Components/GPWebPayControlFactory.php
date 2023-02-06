<?php

declare(strict_types=1);

namespace Pixidos\GPWebPay\Components;

use Pixidos\GPWebPay\Data\OperationInterface;
use Pixidos\GPWebPay\Factory\RequestFactory;

/**
 * Class GPWebPayControlFactory
 * @package Pixidos\GPWebPay\Componets
 * @author  Ondra Votava <ondra@votava.dev>
 */
class GPWebPayControlFactory
{
    /**
     * @var RequestFactory
     */
    private $requestFactory;

    /**
     * GPWebPayControlFactory constructor.
     * @param RequestFactory $requestFactory
     */
    public function __construct(RequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * @param OperationInterface $operation
     * @return GPWebPayControl
     */
    public function create(OperationInterface $operation): GPWebPayControl
    {
        return new GPWebPayControl($operation, $this->requestFactory);
    }
}
