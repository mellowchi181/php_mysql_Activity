<?php
$title = 'Home';
$fullwidth = true;
require 'header.php';

$totalBooks = (int)$conn->query("SELECT COALESCE(SUM(copies),0) FROM books")->fetch_row()[0];
$borrowed   = (int)$conn->query("SELECT COUNT(*) FROM borrowings WHERE returned_at IS NULL")->fetch_row()[0];
$available  = $totalBooks - $borrowed;
$borrowers  = (int)$conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];

$recent = $conn->query(
  "SELECT u.name AS borrower, b.title, br.borrowed_at, br.due_date, br.returned_at
   FROM borrowings br
   JOIN books b ON b.id = br.book_id
   JOIN users u ON u.id = br.user_id
   ORDER BY br.id DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$stats = ['Total Books' => $totalBooks, 'Available' => $available, 'Borrowed' => $borrowed, 'Borrowers' => $borrowers];
?>

<!-- HERO -->
<div class="container text-white text-center bg-dark py-5 px-3 my-4 rounded">
  <p class="lead fw-lighter fs-6 hero-tag d-inline-block rounded px-3 py-1">Books for everyone</p>
  <h1 class="display-4"><b>Browse. Borrow. Return.</b></h1>
  <p class="lead">
    Libraray is a simple way to find a book, borrow it for a few days,
    and keep track of everything that goes in and out.
  </p>
  <a href="books.php" class="btn btn-light btn-lg">Browse Books</a>
  <a href="borrow.php" class="btn btn-outline-light btn-lg">Borrow a Book</a>
</div>

<!-- STATS -->
<div class="container py-3">
  <div class="row g-4 text-center">
    <?php foreach ($stats as $label => $value): ?>
      <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm border-0"><div class="card-body">
          <div class="text-muted"><?= $label ?></div>
          <div class="stat-number"><?= $value ?></div>
        </div></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- FEATURES -->
<div class="container py-5" id="features">
  <div class="text-center mb-5">
    <h2 class="display-5 fw-bold">What You Can Do</h2>
    <p class="lead text-muted">Everything you need to run a small library.</p>
  </div>
  <div class="row g-4">
    <div class="col-md-4"><div class="card h-100 shadow-sm border-0"><div class="card-body text-center">
      <h3 class="card-title fw-bold">Browse Books</h3>
      <p class="card-text">Search the catalog by title or author and see right away how many copies are available.</p>
      <a href="books.php" class="btn btn-accent">View Books</a>
    </div></div></div>

    <div class="col-md-4"><div class="card h-100 shadow-sm border-0"><div class="card-body text-center">
      <h3 class="card-title fw-bold">Borrow Easily</h3>
      <p class="card-text">Pick a book, choose the borrower, set a due date up to 30 days ahead, and you're done.</p>
      <a href="borrow.php" class="btn btn-accent">Borrow Now</a>
    </div></div></div>

    <div class="col-md-4"><div class="card h-100 shadow-sm border-0"><div class="card-body text-center">
      <h3 class="card-title fw-bold">Track Records</h3>
      <p class="card-text">See who has what, spot overdue books, and mark them returned when they come back.</p>
      <a href="records.php" class="btn btn-accent">View Records</a>
    </div></div></div>
  </div>
</div>

<!-- ABOUT / INFORMATION -->
<div class="container-fluid section-alt py-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6">
        <h2 class="display-6 fw-bold">Your Library, Your Way</h2>
        <p class="lead">Libraray keeps lending simple for both librarians and readers.</p>
        <p>Add new books, register borrowers, and let the system count the available copies for you.</p>
        <a href="borrowers.php" class="btn btn-light">Manage Borrowers</a>
      </div>
      <div class="col-md-6 mt-4 mt-lg-0">
        <div class="card shadow border-0">
          <div class="card-header bg-dark text-white"><b>Why Libraray?</b></div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item">📚 A catalog you can search in seconds</li>
            <li class="list-group-item">📅 Due dates and overdue tracking</li>
            <li class="list-group-item">🔒 No double-borrowing of the last copy</li>
            <li class="list-group-item">✅ One-click returns</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RECENT RECORDS -->
<div class="container py-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold mb-0">Recent Borrowing Records</h2>
    <a href="records.php">View all</a>
  </div>
  <div class="card shadow-sm border-0"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>Borrower</th><th>Book</th><th>Date</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($recent as $r): ?>
        <tr>
          <td><?= e($r['borrower']) ?></td>
          <td><?= e($r['title']) ?></td>
          <td><?= e(date('M j', strtotime($r['borrowed_at']))) ?></td>
          <td><?= statusBadge($r['returned_at'], $r['due_date']) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="4" class="text-center text-muted">No borrowing records yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>

<?php require 'footer.php'; ?>
