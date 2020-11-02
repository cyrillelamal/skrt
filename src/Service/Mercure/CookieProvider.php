<?php


namespace App\Service\Mercure;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Cookie;

class CookieProvider
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;
    /**
     * @var string
     */
    private $mercurePublishUrl;

    public function __construct(
        ParameterBagInterface $parameterBag
        , string $mercurePublishUrl
    )
    {
        $this->parameterBag = $parameterBag;
        $this->mercurePublishUrl = $mercurePublishUrl;
    }

    public function getMercureAuthorizationCookie(string $value): Cookie
    {
        return Cookie::create('mercureAuthorization')
            ->withValue((string)$value)
            ->withPath(parse_url($this->mercurePublishUrl, PHP_URL_PATH))
            ->withSecure(filter_var($this->parameterBag->get('app_secure'), FILTER_VALIDATE_BOOLEAN))
            ->withHttpOnly(true)
            ->withSameSite('strict');
    }
}
