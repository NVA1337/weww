<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Conversations API",
 *     version="1.0.0",
 *     description="API for managing conversations between users"
 * )
 * @OA\Server(
 *     url="http://localhost:8000",
 *     description="Local server"
 * )
 * @OA\Tag(name="Messages")
 * @OA\Schema(
 *     schema="Message",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="content", type="string", example="Hello there!"),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="mine", type="boolean", description="Whether the message belongs to the current user")
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
#[Route('/messages', name: 'messages.')]
final class MessageController extends AbstractController
{
    const ATTRIBUTES_TO_SERIALIZE = ['id', 'content', 'createdAt', 'mine'];

    private EntityManagerInterface $entityManager;
    private MessageRepository $messageRepository;
    private UserRepository $userRepository;
    private ParticipantRepository $participantRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageRepository $messageRepository,
        UserRepository $userRepository,
        ParticipantRepository $participantRepository
    ) {
        $this->entityManager = $entityManager;
        $this->messageRepository = $messageRepository;
        $this->userRepository = $userRepository;
        $this->participantRepository = $participantRepository;
    }

    /**
     * @OA\Get(
     *     path="/messages/{id}",
     *     summary="Get messages in a conversation",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the conversation",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of messages in the conversation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conversation not found"
     *     )
     * )
     */
    #[Route("/{id}", name: "getMessages", methods: ['GET'])]
    public function index(Request $request, Conversation $conversation): Response
    {
        $this->denyAccessUnlessGranted('view', $conversation);
    
        $messages = $this->messageRepository->findMessageByConversationId(
            $conversation->getId()
        );
    
        $processedMessages = array_map(function ($message) {
            return [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'mine' => $message->getUser()->getId() === 1, //$this->getUser()->getId(),
            ];
        }, $messages);
    
        return $this->json($processedMessages, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *     path="/messages/{id}",
     *     summary="Send a new message in a conversation",
     *     tags={"Messages"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the conversation",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         description="Message content",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"content"},
     *             @OA\Property(property="content", type="string", example="Hello, how are you?")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Message created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Message")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Conversation not found"
     *     )
     * )
     */
    #[Route("/{id}", name: "newMessage", methods: ['POST'])]
    public function newMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->denyAccessUnlessGranted('view', $conversation);

        $user = $this->getUser();
        $content = $request->request->get('content');
        
        if (empty($content)) {
            return $this->json(['error' => 'Content cannot be empty'], Response::HTTP_BAD_REQUEST);
        }

        $message = new Message();
        $message->setContent($content);
        $message->setUser($user);
        $message->setMine(true);

        $conversation->addMessage($message);
        $conversation->setLastMessage($message);

        $this->entityManager->getConnection()->beginTransaction();
        try {
            $this->entityManager->persist($message);
            $this->entityManager->persist($conversation);
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return $this->json(['error' => 'Failed to send message'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json([
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'mine' => true
        ], Response::HTTP_CREATED);
    }
}