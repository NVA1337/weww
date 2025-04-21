<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @OA\Tag(name="Authentication")
 */
class SecurityController extends AbstractController
{
        /**
     * @OA\Post(
     *     path="/login",
     *     summary="User login",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         description="Login credentials",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "password"},
     *                 @OA\Property(property="username", type="string", example="user@example.com"),
     *                 @OA\Property(property="password", type="string", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect after successful login",
     *         headers={
     *             @OA\Header(header="Location", description="Redirect location", @OA\Schema(type="string"))
     *         }
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login form rendered when not authenticated",
     *         @OA\MediaType(
     *             mediaType="text/html",
     *             @OA\Schema(type="string", description="HTML login form")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid credentials.")
     *         )
     *     )
     * )
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
/**
     * @OA\Post(
     *     path="/logout",
     *     summary="User logout",
     *     tags={"Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=302,
     *         description="Redirect after logout",
     *         headers={
     *             @OA\Header(header="Location", description="Redirect location", @OA\Schema(type="string"))
     *         }
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Full authentication is required to access this resource.")
     *         )
     *     )
     * )
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
