<?php

$dbPath = __DIR__ . '/banco.sqlite';
$pdo = new PDO("sqlite:$dbPath");

$id = $_GET['id'];

$query = "DELETE FROM videos WHERE id = :id";

$stmt = $pdo->prepare($query);

$stmt->bindValue(':id', $id);

if ($stmt->execute() === false) {
    header('Location: /?success=0');
} else {
    header('Location: /?success=1');
}