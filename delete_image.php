<?php
require 'config.php';
session_start();

if (!isset($_SESSION['user']) || !isset($_GET['id'])) {
    header("Location: gallery.php");
    exit;
}

$id = $_GET['id'];
$user = $_SESSION['user'];

// Check if user owns the image
$stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ? AND uploaded_by = ?");
$stmt->execute([$id, $user]);
$image = $stmt->fetch();

if ($image) {
    // Delete file
    if (file_exists($image['image_path'])) {
        unlink($image['image_path']);
    }

    // Delete from DB
    $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
}

header("Location: gallery.php?deleted=1");
exit;
