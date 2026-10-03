<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            flash("Borrower deleted.");
        } catch (mysqli_sql_exception $e) {
            flash($e->getCode() == 1451 ? "Can't delete: this borrower has borrowing records." : "Could not delete the borrower.", 'danger');
        }
    }
}
header("Location: borrowers.php");
exit;
