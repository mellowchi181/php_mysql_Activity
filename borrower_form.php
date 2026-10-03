<?php
require_once 'config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$user = ['name' => '', 'email' => ''];

if ($id) {
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$user) { die("Borrower not found."); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user['name']  = trim($_POST['name'] ?? '');
    $user['email'] = trim($_POST['email'] ?? '');

    if ($user['name'] !== '' && filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $user['name'], $user['email'], $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $user['name'], $user['email']);
            }
            $stmt->execute();
            $stmt->close();
            flash($id ? "Borrower updated." : "Borrower registered.");
            header("Location: borrowers.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            flash($e->getCode() == 1062 ? "That email address is already registered." : "An error occurred while saving.", 'danger');
        }
    } else {
        flash("Please provide a valid name and email address.", 'danger');
    }
}

$title = $id ? 'Edit Borrower' : 'Add Borrower';
require 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card"><div class="card-body">
    <h3><?= e($title) ?></h3>
    <form method="POST">
      <div class="mb-3"><label class="form-label">Full Name</label>
        <input name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required></div>
      <button class="btn btn-accent">Save</button>
      <a href="borrowers.php" class="btn btn-outline-light">Cancel</a>
    </form>
  </div></div>
</div></div>
<?php require 'includes/footer.php'; ?>
