<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$id = null;

if (preg_match('#/tasks/(\d+)#', $requestUri, $matches)) {
    $id = (int)$matches[1];
}

function validateTask($data) {
    $errors = [];
    if (empty(trim($data['title'] ?? ''))) {
        $errors[] = 'Title is required';
    }
    if (strlen(trim($data['title'] ?? '')) > 255) {
        $errors[] = 'Title too long';
    }
    if (!empty($data['status']) && !in_array($data['status'], ['pending', 'completed'])) {
        $errors[] = 'Invalid status';
    }
    return $errors;
}

switch ($method) {
    case 'GET':
        if ($id) {
            // GET /tasks/{id}
            $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
            $stmt->execute([$id]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($task) {
                echo json_encode($task);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Task not found']);
            }
        } else {
            // GET /tasks
            $stmt = $pdo->query('SELECT * FROM tasks ORDER BY created_at DESC');
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($tasks);
        }
        break;

    case 'POST':
        // POST /tasks
        $input = json_decode(file_get_contents('php://input'), true);
        $errors = validateTask($input);
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            break;
        }
        
        $stmt = $pdo->prepare('INSERT INTO tasks (title, description, status) VALUES (?, ?, ?)');
        $stmt->execute([$input['title'], $input['description'] ?? null, $input['status'] ?? 'pending']);
        
        $newId = $pdo->lastInsertId();
        echo json_encode(['id' => $newId, 'message' => 'Task created']);
        break;

    case 'PUT':
        // PUT /tasks/{id}
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            break;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $errors = validateTask($input);
        
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['errors' => $errors]);
            break;
        }
        
        $stmt = $pdo->prepare('UPDATE tasks SET title = ?, description = ?, status = ? WHERE id = ?');
        $result = $stmt->execute([$input['title'], $input['description'] ?? null, $input['status'] ?? 'pending', $id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Task updated']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
        }
        break;

    case 'DELETE':
        // DELETE /tasks/{id}
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            break;
        }
        
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $result = $stmt->execute([$id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Task deleted']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Task not found']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>