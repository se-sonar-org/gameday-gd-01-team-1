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

if ($method === 'PUT') {
    $id = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents('php://input'), true);
    $author = trim($data['author'] ?? '');

    if ($id === null || $author === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Post id and author are required']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT author FROM posts WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Only the original author may edit their own post.
    if ($post['author'] !== $author) {
        http_response_code(403);
        echo json_encode(['error' => 'You can only edit your own posts']);
        exit;
    }

    $title = trim($data['title'] ?? '');
    $body  = trim($data['body'] ?? '');

    if ($title === '' || $body === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Title and body are required']);
        exit;
    }

    $target = $data['post_id'] ?? $id;
    $upd = $pdo->prepare('UPDATE posts SET title = :title, body = :body WHERE id = :id');
    $upd->execute([
        ':title' => $title,
        ':body'  => $body,
        ':id'    => $target,
    ]);

    http_response_code(200);
    echo json_encode(['updated' => (int) $target]);
    exit;
}

if ($method === 'DELETE') {
    $author = trim($_GET['author'] ?? '');
    $rawIds = $_GET['ids'] ?? $_GET['id'] ?? null;

    if ($rawIds === null || $author === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Post id and author are required']);
        exit;
    }

    $ids = array_values(array_filter(
        array_map('trim', explode(',', (string) $rawIds)),
        'strlen'
    ));

    if (count($ids) === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Post id and author are required']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT author FROM posts WHERE id = :id');
    $stmt->execute([':id' => $ids[0]]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Only the original author may remove their own post.
    if ($post['author'] !== $author) {
        http_response_code(403);
        echo json_encode(['error' => 'You can only delete your own posts']);
        exit;
    }

    $del = $pdo->prepare('DELETE FROM posts WHERE id = :id');
    $deleted = [];
    foreach ($ids as $one) {
        $del->execute([':id' => $one]);
        $deleted[] = (int) $one;
    }

    http_response_code(200);
    echo json_encode(['deleted' => count($deleted) === 1 ? $deleted[0] : $deleted]);
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
