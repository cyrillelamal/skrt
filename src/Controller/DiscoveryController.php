<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mercure\CookieProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/discovery", name="discovery_")
 */
class DiscoveryController extends AbstractController
{
    /**
     * @var string
     */
    private $mercureSecretKey;
    /**
     * @var string
     */
    private $mercurePublishUrl;
    /**
     * @var CookieProvider
     */
    private $cookieProvider;

    public function __construct(
        string $mercureSecretKey
        , string $mercurePublishUrl
        , CookieProvider $cookieProvider
    )
    {
        $this->mercureSecretKey = $mercureSecretKey;
        $this->mercurePublishUrl = $mercurePublishUrl;
        $this->cookieProvider = $cookieProvider;
    }

    /**
     * @Route("/", name="index")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $topic = "http://users/{$user->getId()}";

        $token = (new Builder())
            ->withClaim('mercure', ['subscribe' => [$topic]])
            ->getToken(new Sha256(), new Key($this->mercureSecretKey));

        $response = $this->json([
            'id' => $user->getId(),
            'mercure_publish_url' => $this->mercurePublishUrl,
            'topic' => $topic,
        ]);
        $response->headers->setCookie($this->cookieProvider->getMercureAuthorizationCookie((string)$token));

        return $response;
    }
}
