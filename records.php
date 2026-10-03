<?php
$title = 'Records';
require 'header.php';

$rows = $conn->query(
  "SELECT br.id, b.title, u.name, br.borrowed_at, br.due_date, br.returned_at
   FROM borrowings br
   JOIN books b ON b.id = br.book_id
   JOIN users u ON u.id = br.user_id
   ORDER BY (br.returned_at IS NULL) DESC, br.due_date ASC")->fetch_all(MYSQLI_ASSOC);
?>
<h2 class="mb-3">Records</h2>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr><th>Borrower</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['name']) ?></td>
      <td><?= e($r['title']) ?></td>
      <td><?= e(date('M j', strtotime($r['borrowed_at']))) ?></td>
      <td><?= e(date('M j', strtotime($r['due_date']))) ?></td>
      <td><?= statusBadge($r['returned_at'], $r['due_date']) ?></td>
      <td class="text-end">
        <?php if (!$r['returned_at']): ?>
          <form method="POST" action="return.php" class="d-inline">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-sm btn-accent">Mark Returned</button>
          </form>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="text-center text-muted">No records yet.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<?php require 'footer.php'; ?>
