<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use PDO;

/**
 * 
 * @OA\Info(
 *     title="Conversations API",
 *     version="1.0.0",
 *     description="API for managing conversations between users"
 * )
 * @OA\Tag(name="Users")
 */
class UserController extends AbstractController
{
    private string $dbFile = 'E:\ГПО_Владимиров\pro\pro\var\data.db';
    private string $tableName = 'user';

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Получить список пользователей",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="username", type="string"),
     *                 @OA\Property(property="roles", type="string"),
     *                 @OA\Property(property="password", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    #[Route('/api/users', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            // Подключение к SQLite базе данных
            $db = new PDO('sqlite:' . $this->dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            // Запрос данных из таблицы (используем подготовленные выражения)
            $query = "SELECT id, username, roles, password FROM " . $this->tableName;
            $stmt = $db->query($query);
            
            // Получение данных в виде ассоциативного массива
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Возвращаем JSON ответ
            return $this->json($data, 200, [], [
                'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ]);
            
        } catch (PDOException $e) {
            // Логирование ошибки
            error_log($e->getMessage());
            
            // Возвращаем ошибку сервера
            return $this->json([
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}