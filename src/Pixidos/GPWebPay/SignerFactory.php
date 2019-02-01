<?php
declare(strict_types=1);

namespace Pixidos\GPWebPay;

use Pixidos\GPWebPay\Exceptions\SignerException;
use Pixidos\GPWebPay\Intefaces\ISigner;
use Pixidos\GPWebPay\Intefaces\ISignerFactory;

/**
 * Class SingerFactory
 * @package Pixidos\GPWebPay
 * @author Ondra Votava <ondrej.votava@pixidos.com>
 */
class SignerFactory implements ISignerFactory
{
    /** @var Settings $settings */
    private $settings;
    
    /**
     * SignerFactory constructor.
     *
     * @param Settings $settings
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }
    
    /**
     * @param  null|string $gatewayKey
     *
     * @return ISigner
     * @throws SignerException
     */
    public function create(?string $gatewayKey = null): ISigner
    {
        return new Signer(
            $this->settings->getPrivateKey($gatewayKey),
            $this->settings->getPrivateKeyPassword($gatewayKey),
            $this->settings->getPublicKey()
        );
    }
}

