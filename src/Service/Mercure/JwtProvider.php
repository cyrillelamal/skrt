<?php


namespace App\Service\Mercure;


use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class JwtProvider
{
    /**
     * @var string
     */
    private $mercureSecretKey;

    public function __construct(string $mercureSecretKey)
    {
        $this->mercureSecretKey = $mercureSecretKey;
    }

    public function __invoke(): string
    {
        return (string)(new Builder())
            ->withClaim('mercure', ['publish' => ['*']])
            ->getToken(new Sha256(), new Key($this->mercureSecretKey));
    }
}
