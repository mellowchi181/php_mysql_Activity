<?php
require_once 'config.php';

$today = date('Y-m-d');
$max   = date('Y-m-d', strtotime('+30 days'));
$form  = [
    'name'     => '',
    'email'    => '',
    'book_id'  => filter_var($_GET['book_id'] ?? null, FILTER_VALIDATE_INT), // pre-selected from the Books page
    'due_date' => date('Y-m-d', strtotime('+7 days')),
];

function availableCopies($conn, $id) {
    $stmt = $conn->prepare(
      "SELECT b.copies - COUNT(br.id) FROM books b
       LEFT JOIN borrowings br ON br.book_id = b.id AND br.returned_at IS NULL
       WHERE b.id = ? GROUP BY b.id");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_row();
    $stmt->close();
    return $row ? (int)$row[0] : -1; // -1 = book doesn't exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['name']     = trim($_POST['name'] ?? '');
    $form['email']    = trim($_POST['email'] ?? '');
    $form['book_id']  = filter_var($_POST['book_id'] ?? null, FILTER_VALIDATE_INT);
    $form['due_date'] = $_POST['due_date'] ?? '';

    if ($form['name'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL) ||
        !$form['book_id'] || $form['due_date'] < $today || $form['due_date'] > $max) {
        flash("Please enter your name, a valid email, choose a book, and pick a due date within the next 30 days.", 'danger');
    } else {
        try {
            $conn->begin_transaction();

            // Lock the book row so two people can't borrow the last copy at the same time
            $lock = $conn->prepare("SELECT id, title FROM books WHERE id = ? FOR UPDATE");
            $lock->bind_param("i", $form['book_id']);
            $lock->execute();
            $book = $lock->get_result()->fetch_assoc();
            $lock->close();

            if (!$book) {
                $conn->rollback();
                flash("That book doesn't exist.", 'danger');
            } elseif (availableCopies($conn, $book['id']) < 1) {
                $conn->rollback();
                flash("Sorry, no copies of \"" . $book['title'] . "\" are available right now.", 'danger');
            } else {
                // Find the borrower by email, or register them automatically
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $form['email']);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($user) {
                    $user_id = (int)$user['id'];
                } else {
                    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                    $stmt->bind_param("ss", $form['name'], $form['email']);
                    $stmt->execute();
                    $user_id = $conn->insert_id;
                    $stmt->close();
                }

                // Don't let the same person borrow the same book twice at once
                $stmt = $conn->prepare("SELECT COUNT(*) FROM borrowings WHERE user_id = ? AND book_id = ? AND returned_at IS NULL");
                $stmt->bind_param("ii", $user_id, $book['id']);
                $stmt->execute();
                $already = (int)$stmt->get_result()->fetch_row()[0];
                $stmt->close();

                if ($already > 0) {
                    $conn->rollback();
                    flash("You already have \"" . $book['title'] . "\" borrowed.", 'danger');
                } else {
                    $stmt = $conn->prepare("INSERT INTO borrowings (book_id, user_id, borrowed_at, due_date) VALUES (?, ?, CURDATE(), ?)");
                    $stmt->bind_param("iis", $book['id'], $user_id, $form['due_date']);
                    $stmt->execute();
                    $stmt->close();
                    $conn->commit();

                    flash("Borrowed \"" . $book['title'] . "\"! Please return it by " . $form['due_date'] . ".");
                    header("Location: records.php");
                    exit;
                }
            }
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            flash("Could not process your request. Please try again.", 'danger');
        }
    }
}

// All books with how many copies are free right now
$books = $conn->query(
  "SELECT b.id, b.title, b.author, b.copies, b.copies - COUNT(br.id) AS available
   FROM books b
   LEFT JOIN borrowings br ON br.book_id = b.id AND br.returned_at IS NULL
   GROUP BY b.id ORDER BY b.title")->fetch_all(MYSQLI_ASSOC);

$title = 'Borrow';
require 'header.php';
?>
<div class="row justify-content-center"><div class="col-md-7 col-lg-6">
  <div class="card shadow-sm"><div class="card-body p-4">
    <h3 class="mb-1">Borrow a Book</h3>
    <p class="text-muted">Fill in the form below. New here? Your name and email will register you automatically.</p>

    <form method="POST">
      <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="name" name="name"
               placeholder="Enter your name" value="<?= e($form['name']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email"
               placeholder="Enter your email" value="<?= e($form['email']) ?>" required>
      </div>

      <div class="mb-3">
        <label for="book_id" class="form-label">Book</label>
        <select class="form-select" id="book_id" name="book_id" required>
          <option value="">-- Choose a book --</option>
          <?php foreach ($books as $b): $avail = (int)$b['available']; ?>
            <option value="<?= (int)$b['id'] ?>"
                    <?= $avail < 1 ? 'disabled' : '' ?>
                    <?= (int)$form['book_id'] === (int)$b['id'] && $avail > 0 ? 'selected' : '' ?>>
              <?= e($b['title']) ?> — <?= e($b['author']) ?>
              (<?= $avail > 0 ? $avail . ' of ' . (int)$b['copies'] . ' available' : 'none available' ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="due_date" class="form-label">Return By (up to 30 days)</label>
        <input type="date" class="form-control" id="due_date" name="due_date"
               min="<?= $today ?>" max="<?= $max ?>" value="<?= e($form['due_date']) ?>" required>
      </div>

      <button type="submit" class="btn btn-accent w-100">Submit Borrow Request</button>
    </form>
  </div></div>
</div></div>
<?php require 'footer.php'; ?>
