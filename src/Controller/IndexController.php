<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="API documentation for my project"
 * )
 */
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
