<?php
function getDb(): PDO
{
    $map = __DIR__ . "/data";
    if (!is_dir($map)) {
        mkdir($map, 0755, true);
    }

    $pdo = new PDO("sqlite:" . $map . "/gebruikers.sqlite");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // UNIQUE op e-mail: de database weigert dubbele e-mailadressen
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS gebruikers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            wachtwoord_hash TEXT NOT NULL,
            aangemaakt_op TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");

    return $pdo;
}