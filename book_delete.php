<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash("Book deleted.");
        } catch (mysqli_sql_exception $e) {
            // 1451 = foreign key constraint (book has borrowing history)
            flash($e->getCode() == 1451 ? "Can't delete: this book has borrowing records." : "Could not delete the book.", 'danger');
        }
    }
}
header("Location: books.php");
exit;
