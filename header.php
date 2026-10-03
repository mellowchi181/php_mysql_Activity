<?php
require_once __DIR__ . '/config.php';
$title = $title ?? 'Libraray';
$page  = basename($_SERVER['PHP_SELF']);
$fullwidth = $fullwidth ?? false; // landing-style pages manage their own containers
?>
<!doctype html>
<html lang="en" data-bs-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <title><?= e($title) ?> | Libraray</title>
  <style>
  :root {
    --chrome:       #212529;  /* navbar, hero, footer, table header */
    --primary:      #0d6efd;  /* buttons, links, numbers */
    --primary-dark: #0a58ca;  /* hover */
    --soft:         #e7f1ff;  /* table row hover */
    --page:         #ffffff;  /* page background */
    --page-alt:     #f8f9fa;  /* alternate sections */
    --line:         #dee2e6;
    --ink:          #212529;  /* main text */
    --muted:        #6c757d;  /* secondary text */
    --bs-body-bg: var(--page);
    --bs-body-color: var(--ink);
    --bs-border-color: var(--line);
  }
  body { background: var(--page); color: var(--ink); }
  .bg-dark { background-color: var(--chrome) !important; color: #fff; }
  .section-alt { background-color: var(--page-alt); color: var(--ink); }

  /* Text colors: gray on light areas, soft white on dark areas */
  .text-muted { color: var(--muted) !important; }
  .bg-dark .text-white-50 { color: rgba(255,255,255,.7) !important; }

  .navbar .nav-link { color: rgba(255,255,255,.75); }
  .navbar .nav-link:hover, .navbar .nav-link.active { color: #fff; }
  .hero-tag { background: var(--page-alt); color: var(--ink); }
  .card { background: #fff; border: 1px solid var(--line); color: var(--ink); }
  .card-header.bg-dark { color: #fff; }
  .list-group-item { background: transparent; color: var(--ink); border-color: var(--line); }
  .stat-number { font-size: 2.2rem; font-weight: 700; color: var(--primary); }
  .table { --bs-table-bg: transparent; --bs-table-color: var(--ink); --bs-table-border-color: var(--line);
           --bs-table-hover-bg: var(--soft); --bs-table-hover-color: var(--ink); }
  .table thead th { background: var(--chrome); color: #fff; border-color: var(--chrome); }
  .form-control, .form-select { background: #fff; color: var(--ink); }
  .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 .2rem rgba(13,110,253,.25); }

  /* Buttons */
  .btn-accent { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
  .btn-accent:hover { background: var(--primary-dark); border-color: var(--primary-dark); color: #fff; }
  .btn-outline-primary { --bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary);
                         --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); --bs-btn-hover-color: #fff; }
  /* A white outline button is invisible on a light page, so show it in blue there (the dark hero keeps it white) */
  .container:not(.bg-dark) .btn-outline-light { --bs-btn-color: var(--primary); --bs-btn-border-color: var(--primary);
                         --bs-btn-hover-bg: var(--primary); --bs-btn-hover-border-color: var(--primary); --bs-btn-hover-color: #fff; }

  a { color: var(--primary); }
  a:hover { color: var(--primary-dark); }
  .btn, .navbar-brand { text-decoration: none; }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fs-1 text-light" href="index.php"><b>Libraray</b></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php
        $links = ['index.php' => 'Home', 'books.php' => 'Books', 'borrowers.php' => 'Borrowers', 'borrow.php' => 'Borrow', 'records.php' => 'Records'];
        foreach ($links as $href => $label):
            $active = ($page === $href || ($href === 'books.php' && $page === 'book_form.php') || ($href === 'borrowers.php' && $page === 'borrower_form.php')) ? 'active' : '';
        ?>
          <li class="nav-item"><a class="nav-link <?= $active ?>" href="<?= $href ?>"><?= $label ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</nav>

<?php if ($fullwidth): ?>
  <div class="container mt-3"><?= flash() ?></div>
<?php else: ?>
  <div class="container py-4">
  <?= flash() ?>
<?php endif; ?>
