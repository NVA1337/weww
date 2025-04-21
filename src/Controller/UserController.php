<?php

namespace App\Controller;

use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use PDO;
use Symfony\Component\HttpFoundation\Request; // <-- Добавьте эту строку


/**
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

        /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Создать нового пользователя",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         description="Данные пользователя",
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username", "password"},
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="roles", type="string", default="ROLE_USER")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Пользователь успешно создан",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="username", type="string"),
     *             @OA\Property(property="roles", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Некорректные данные"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Ошибка сервера"
     *     )
     * )
     */
    #[Route('/api/users', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Валидация данных
            if (!isset($data['username']) || !isset($data['password'])) {
                return $this->json([
                    'error' => 'Invalid data',
                    'message' => 'Username and password are required'
                ], 400);
            }

            $username = $data['username'];
            $password = password_hash($data['password'],0);
            $roles = $data['roles'] ?? '["ROLE_USER"]';

            // Подключение к базе данных
            $db = new PDO('sqlite:' . $this->dbFile);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Проверка на существование пользователя
            $stmt = $db->prepare("SELECT id FROM {$this->tableName} WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                return $this->json([
                    'error' => 'User exists',
                    'message' => 'Username already taken'
                ], 400);
            }

            // Вставка нового пользователя
            $stmt = $db->prepare("INSERT INTO {$this->tableName} (username, password, roles) VALUES (?, ?, ?)");
            $stmt->execute([$username, $password, $roles]);

            $userId = $db->lastInsertId();

            // Возвращаем созданного пользователя
            return $this->json([
                'id' => $userId,
                'username' => $username,
                'roles' => $roles
            ], 201, [], [
                'json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ]);

        } catch (\Exception $e) {
            // Логирование ошибки
            error_log($e->getMessage());
            
            // Возвращаем ошибку сервера
            return $this->json([
                'error' => 'Server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}