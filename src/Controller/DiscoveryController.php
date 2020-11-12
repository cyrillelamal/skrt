<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Mercure\CookieProvider;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use OpenApi\Annotations as OA;
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
     * @OA\Get(
     *     path="/api/discovery",
     *     summary="Register the user in the mercure hub setting authorization cookie.",
     *     tags={"mercure"},
     *     @OA\Response(
     *         response=200,
     *         description="The informations about the users and the mercure hub.",
     *         @OA\JsonContent(
     *          @OA\Property(property="id", type="integer", example=12, description="User's id"),
     *          @OA\Property(property="mercure_publish_url", type="string", example="mercure://mercure-hub", description="Mercure hub URL."),
     *          @OA\Property(property="topic", type="string", example="/users/12", description="User's topic in the mercure hub.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="The user is not authorized."
     *     )
     * )
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
