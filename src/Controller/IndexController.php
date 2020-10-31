<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IndexController extends AbstractController
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {
        if ($this->getUser()) {
            return $this->redirect($this->urlGenerator->generate('dashboard_index'));
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
}
