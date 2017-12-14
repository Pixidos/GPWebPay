<?php

namespace Pixidos\GPWebPay\Intefaces;

/**
 * Interface ISignerFactory
 * @package Pixidos\GPWebPay\Intefaces
 * @author Ondra Votava <ondra.votava@pixidos.com>
 */
interface ISignerFactory
{
    /**
     * @param  null|string $gatewayKey
     *
     * @return ISigner
     */
    public function create(?string $gatewayKey = null): ISigner;
}
