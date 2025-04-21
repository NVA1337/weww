<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class IndexController extends AbstractController
{

    /**
     * @OA\Get(
     *     path="/index",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response"
     *     )
     * )
     */
     

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->json([
            'Hello' => ')',

        ], 500);
    }
}
