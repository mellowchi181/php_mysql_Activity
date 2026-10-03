<?php
$title = 'Books';
require 'includes/header.php';

$q = '%' . trim($_GET['q'] ?? '') . '%';
$stmt = $conn->prepare(
  "SELECT b.id, b.title, b.author, b.isbn, b.copies, b.copies - COUNT(br.id) AS available
   FROM books b
   LEFT JOIN borrowings br ON br.book_id = b.id AND br.returned_at IS NULL
   WHERE b.title LIKE ? OR b.author LIKE ?
   GROUP BY b.id ORDER BY b.title");
$stmt->bind_param("ss", $q, $q);
$stmt->execute();
$books = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Books</h2>
  <div class="d-flex gap-2">
    <form class="d-flex gap-2" method="GET">
      <input class="form-control" name="q" placeholder="Search title or author" value="<?= e($_GET['q'] ?? '') ?>">
      <button class="btn btn-outline-light">Search</button>
    </form>
    <a href="book_form.php" class="btn btn-accent"><i class="bi bi-plus-lg"></i> Add Book</a>
  </div>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr><th>Title</th><th>Author</th><th>ISBN</th><th>Available</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($books as $b): ?>
    <tr>
      <td><?= e($b['title']) ?></td>
      <td><?= e($b['author']) ?></td>
      <td><?= e($b['isbn']) ?></td>
      <td><?= (int)$b['available'] ?> / <?= (int)$b['copies'] ?></td>
      <td class="text-end text-nowrap">
        <?php if ($b['available'] > 0): ?>
          <a class="btn btn-sm btn-accent" href="borrow.php?book_id=<?= (int)$b['id'] ?>">Borrow</a>
        <?php endif; ?>
        <a class="btn btn-sm btn-outline-light" href="book_form.php?id=<?= (int)$b['id'] ?>">Edit</a>
        <form method="POST" action="book_delete.php" class="d-inline" onsubmit="return confirm('Delete this book?')">
          <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$books): ?><tr><td colspan="5" class="text-center text-muted">No books found.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<?php require 'includes/footer.php'; ?>
