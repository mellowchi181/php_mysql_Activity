<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $conn->prepare("UPDATE borrowings SET returned_at = NOW() WHERE id = ? AND returned_at IS NULL");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash("Book marked as returned.");
    }
}
header("Location: records.php");
exit;
