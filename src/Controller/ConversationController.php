<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Participant;
use App\Repository\ConversationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;




#[Route("/conversations",name: "conversations.")]

/**
 * @OA\Info(
 *     title="Conversations API",
 *     version="1.0.0",
 *     description="API for managing conversations between users"
 * )
 * @OA\Server(
 *     url="https://127.0.0.1:8000/",
 *     description="Local server"
 * )
 */
final class ConversationController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ConversationRepository
     */
    private $conversationRepository;
    public function __construct(UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->conversationRepository = $conversationRepository;
    }

    /**
     * @OA\Post(
     *     path="/conversations/",
     *     summary="Create a new conversation",
     *     tags={"Conversations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         description="Data needed to create a new conversation",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"otherUser"},
     *             @OA\Property(property="otherUser", type="integer", description="ID of the other participant")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Conversation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", description="ID of the created conversation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input or conversation already exists"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    #[Route('/', name: 'newConversations', methods: ['POST'])]
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */

    public function index(Request $request){
    
    $otherUser = $request->get('otherUser');
    $otherUser = $this->userRepository->find($otherUser);

    if (is_null($otherUser)) {
        throw new \Exception("The user was not found");
    }

    // cannot create a conversation with myself
     if ($otherUser->getId() === $this->getUser()-> getId()) {
    // if ($otherUser->getId() === 1) {
        throw new \Exception("That's deep but you cannot create a conversation with yourself");
    }

    // Check if conversation already exists
    $conversation = $this->conversationRepository->findConversationByParticipants(
        $otherUser->getId(),
        $this->getUser()->getId()
        //o 1
    );

    if (count($conversation)) {
        throw new \Exception("The conversation already exists");
    }

    $conversation = new Conversation();

    $participant = new Participant();
    $participant->setUser($this->getUser());
    //o $participant->setUser( $this->userRepository->find(1) );
    $participant->setConversation($conversation);

    $otherParticipant = new Participant();
    $otherParticipant->setUser($otherUser);
    $otherParticipant->setConversation($conversation);

    $this->entityManager->getConnection()->beginTransaction();
    try {
        $this->entityManager->persist($conversation);
        $this->entityManager->persist($participant);
        $this->entityManager->persist($otherParticipant);

        $this->entityManager->flush();
        $this->entityManager->commit();

    } catch (\Exception $e) {
        $this->entityManager->rollback();
        throw $e;
    }


    return $this->json([
        'id' => $conversation->getId()
    ], Response::HTTP_CREATED, [], []);
}


    /**
     * @OA\Get(
     *     path="/conversations/",
     *     summary="Get user's conversations",
     *     tags={"Conversations"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user's conversations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Conversation ID"),
     *                 @OA\Property(property="participants", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="Participant ID"),
     *                     @OA\Property(property="user", type="object", description="User details")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    #[Route("/", name: "getConversations", methods: ['GET'])]
    public function getConvs() {
        $conversations = $this->conversationRepository->findConversationsByUser($this->getUser()->getId());
        //o $conversations = $this->conversationRepository->findConversationsByUser(2);
        return $this->json($conversations);
    }

}