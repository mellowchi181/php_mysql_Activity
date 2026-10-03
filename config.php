<?php
// Throw exceptions on SQL errors so try/catch blocks actually work
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();

try {
    $conn = new mysqli('localhost', 'root', '', 'app_db');
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    die("Database connection failed. Is MySQL running and is app_db imported?");
}

// Escape output
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Flash messages: flash('Saved!') to set, flash() to display
function flash($msg = null, $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return ''; }
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return '<div class="alert alert-' . e($t) . ' alert-dismissible fade show">' . e($m) .
               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return '';
}

// Status badge for a borrowing record
function statusBadge($returned_at, $due_date) {
    if ($returned_at) return '<span class="badge text-bg-success">Returned</span>';
    if ($due_date < date('Y-m-d')) return '<span class="badge text-bg-danger">Overdue</span>';
    return '<span class="badge text-bg-warning">Borrowed</span>';
}
