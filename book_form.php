<?php
require_once 'config.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$book = ['title' => '', 'author' => '', 'isbn' => '', 'copies' => 1];

if ($id) { // editing: load current data
    $stmt = $conn->prepare("SELECT title, author, isbn, copies FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$book) { die("Book not found."); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book['title']  = trim($_POST['title'] ?? '');
    $book['author'] = trim($_POST['author'] ?? '');
    $book['isbn']   = trim($_POST['isbn'] ?? '');
    $book['copies'] = filter_var($_POST['copies'] ?? 1, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $isbn = $book['isbn'] === '' ? null : $book['isbn'];

    if ($book['title'] === '' || $book['author'] === '' || !$book['copies']) {
        flash("Title, author and a copies count of at least 1 are required.", 'danger');
    } else {
        try {
            if ($id) {
                $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, copies=? WHERE id=?");
                $stmt->bind_param("sssii", $book['title'], $book['author'], $isbn, $book['copies'], $id);
            } else {
                $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, copies) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $book['title'], $book['author'], $isbn, $book['copies']);
            }
            $stmt->execute();
            $stmt->close();
            flash($id ? "Book updated." : "Book added.");
            header("Location: books.php");
            exit;
        } catch (mysqli_sql_exception $e) {
            flash($e->getCode() == 1062 ? "That ISBN already exists." : "Could not save the book.", 'danger');
        }
    }
}

$title = $id ? 'Edit Book' : 'Add Book';
require 'includes/header.php';
?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card shadow-sm"><div class="card-body">
    <h3><?= e($title) ?></h3>
    <form method="POST">
      <div class="mb-3"><label class="form-label">Title</label>
        <input name="title" class="form-control" value="<?= e($book['title']) ?>" required></div>
      <div class="mb-3"><label class="form-label">Author</label>
        <input name="author" class="form-control" value="<?= e($book['author']) ?>" required></div>
      <div class="mb-3"><label class="form-label">ISBN (optional)</label>
        <input name="isbn" class="form-control" value="<?= e($book['isbn']) ?>"></div>
      <div class="mb-3"><label class="form-label">Copies</label>
        <input type="number" min="1" name="copies" class="form-control" value="<?= e($book['copies']) ?>" required></div>
      <button class="btn btn-accent">Save</button>
      <a href="books.php" class="btn btn-outline-light">Cancel</a>
    </form>
  </div></div>
</div></div>
<?php require 'includes/footer.php'; ?>
