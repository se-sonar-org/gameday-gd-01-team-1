<?php
require_once __DIR__ . '/config.php';

/**
 * Open a connection to the SQLite database and make sure the
 * posts table exists. A new database file is created on first run.
 */
function get_db()
{
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS posts (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            title      TEXT NOT NULL,
            author     TEXT NOT NULL,
            body       TEXT NOT NULL,
            created_at TEXT NOT NULL
        )'
    );

    return $pdo;
}
