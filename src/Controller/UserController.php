<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("/api/users", name="users_")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Find a user.",
     *     tags={"users", "read"},
     *     @OA\Parameter(
     *         name="username",
     *         in="query",
     *         required=true,
     *         description="Username or a part of a username, that contains at least 4 characters.",
     *         @OA\Schema(type="string", example="serna")
     *     ),
     *     @OA\Response(
     *     response=200,
     *     description="The informations about the user.",
     *     @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *     response=401,
     *     description="The user is unauthorized to perform the action."
     * )
     * )
     * @Route("/", name="read", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     * @param Request $request
     * @return JsonResponse
     */
    public function read(Request $request): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $username = (string)$request->query->get('username', '');

        $users = $username !== '' ? $this->repository->findWhereUsernameLike($username) : array();
        $users = array_filter($users, function (User $user) use ($currentUser) {
            return $user->getUsername() !== $currentUser->getUsername();
        });

        $status = $users ? Response::HTTP_OK : Response::HTTP_NOT_FOUND;

        return $this->json(
            $users,
            $status,
            array(),
            ['groups' => ['users:search']]
        );
    }
}
