<?php
require 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $user = $_SESSION['user'] ?? 'Anonymous';

    if (!empty($_FILES['image']['name']) && $title && $category) {
        $targetDir = "uploads/";
        $filename = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $targetDir . $filename;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
            $stmt = $pdo->prepare("INSERT INTO gallery (title, category, image_path, uploaded_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $category, $targetFile, $user]);
            header("Location: gallery.php?success=1");
            exit;
        } else {
            $error = "Upload failed.";
        }
    } else {
        $error = "Please complete all fields.";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="image" class="form-label">Image</label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>
