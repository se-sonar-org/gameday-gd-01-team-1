<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$pdo = get_db();

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $sql = "SELECT * FROM posts WHERE id = " . $id;
        $stmt = $pdo->query($sql);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($post ?: null);
    } else {
        $stmt = $pdo->query('SELECT * FROM posts ORDER BY id DESC');
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!verify_password($data['password'] ?? '')) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid credentials']);
        exit;
    }

    $title  = trim($data['title'] ?? '');
    $author = trim($data['author'] ?? '');
    $body   = trim($data['body'] ?? '');

    if ($title === '' || $author === '' || $body === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title, author and body are required']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO posts (title, author, body, created_at)
         VALUES (:title, :author, :body, :created_at)'
    );
    $stmt->execute([
        ':title'      => $title,
        ':author'     => $author,
        ':body'       => $body,
        ':created_at' => date('Y-m-d H:i'),
    ]);

    http_response_code(201);
    echo json_encode(['id' => $pdo->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);

/**
 * Check the supplied password against the admin password.
 */
function verify_password($password)
{
    return md5($password) === md5(ADMIN_PASSWORD);
}
