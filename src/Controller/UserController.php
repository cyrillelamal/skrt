<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/", name="read", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     * @param Request $request
     * @return Response
     */
    public function read(Request $request): Response
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
