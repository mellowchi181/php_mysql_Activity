<?php
$title = 'Borrowers';
require 'includes/header.php';
$users = $conn->query(
  "SELECT u.id, u.name, u.email, u.created_at,
          (SELECT COUNT(*) FROM borrowings br WHERE br.user_id = u.id AND br.returned_at IS NULL) AS active
   FROM users u ORDER BY u.id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h2 class="mb-0">Borrowers</h2>
  <a href="borrower_form.php" class="btn btn-accent"><i class="bi bi-person-plus"></i> Add Borrower</a>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
  <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Books Out</th><th>Joined</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($users as $u): ?>
    <tr>
      <td><?= (int)$u['id'] ?></td>
      <td><?= e($u['name']) ?></td>
      <td><?= e($u['email']) ?></td>
      <td><?= (int)$u['active'] ?></td>
      <td><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
      <td class="text-end text-nowrap">
        <a class="btn btn-sm btn-outline-light" href="borrower_form.php?id=<?= (int)$u['id'] ?>">Edit</a>
        <form method="POST" action="borrower_delete.php" class="d-inline" onsubmit="return confirm('Are you sure?')">
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$users): ?><tr><td colspan="6" class="text-center text-muted">No borrowers found.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
<?php require 'includes/footer.php'; ?>
