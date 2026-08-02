<?php
require_once 'includes/header.php';
require_once 'classes/Book.php';
require_once 'classes/Issue.php';
require_once 'classes/User.php';

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$book = new Book();
$issue = new Issue();
$userObj = new User();

$userDetails = $userObj->getUserDetails($_SESSION['user_id']);

// Handle Book Borrowing
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['borrow_book'])) {
    $issue->setBookNo($_POST['book_no']);
    $issue->setBookName($_POST['book_name']);
    $issue->setBookAuthor($_POST['book_author']);
    $issue->setStudentId($_SESSION['user_id']);
    $issue->setExpectedReturnDate($_POST['return_date']);
    
    if ($issue->issueBook()) {
        $book_name = $_POST['book_name'];
        $message = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle me-2'></i>You have successfully borrowed <strong>$book_name</strong>!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $message = "<div class='alert alert-danger alert-dismissible fade show'><i class='bi bi-exclamation-triangle me-2'></i>Failed to borrow book. Please try again.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
}
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
if ($search_query !== '') {
    $allBooks = $book->searchBook($search_query);
} else {
    $allBooks = $book->getAllBooks();
}

$issuedBooks = $issue->getIssuedBooksByUser($_SESSION['user_id']);

$total_fines = 0;
$issued_arr = [];
if($issuedBooks && $issuedBooks->num_rows > 0) {
    while($r = $issuedBooks->fetch_assoc()){
        $issued_arr[] = $r;
        if($r['fine_status'] == 'Unpaid'){
            $total_fines += $r['fine_amount'];
        }
    }
}

?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="glass-card bg-primary text-white text-center py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <h3 class="mb-0"><i class="bi bi-person-circle me-2"></i>Welcome, <?php echo $_SESSION['user_name']; ?>!</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-4">
        <?php if($total_fines > 0): ?>
            <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                <div>
                    <h5 class="alert-heading mb-1">Pending Fines: Rs. <?php echo $total_fines; ?></h5>
                    <p class="mb-0">You have unpaid library fines due to late returns. Please pay the librarian to settle your account.</p>
                </div>
            </div>
        <?php endif; ?>
        <?php echo $message; ?>
        <div class="card-clean h-100">
            <h4 class="mb-4 text-primary"><i class="bi bi-search me-2"></i>Library Catalog</h4>
            
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by book name, author, or book number..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="btn btn-primary px-4">Search</button>
                </div>
            </form>

            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle" id="booksTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th scope="col">Book ID</th>
                            <th scope="col">Title</th>
                            <th scope="col">Author</th>
                            <th scope="col">Category</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $allBooks->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-secondary">#<?php echo $row['book_no']; ?></span></td>
                            <td class="fw-bold"><?php echo $row['book_name']; ?></td>
                            <td><i class="bi bi-person text-muted me-1"></i><?php echo $row['author_name']; ?></td>
                            <td><span class="badge rounded-pill bg-info text-dark"><?php echo $row['cat_name']; ?></span></td>
                            <td>
                                <form method="POST" style="margin: 0;" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="borrow_book" value="1">
                                    <input type="hidden" name="book_no" value="<?php echo $row['book_no']; ?>">
                                    <input type="hidden" name="book_name" value="<?php echo $row['book_name']; ?>">
                                    <input type="hidden" name="book_author" value="<?php echo $row['author_name']; ?>">
                                    <input type="date" name="return_date" class="form-control form-control-sm" required style="width: 130px;" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+1 month')); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">Borrow</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Issued Books Section -->
    <div class="col-12 mb-4">
        <div class="card-clean">
            <h4 class="mb-4 text-success"><i class="bi bi-journal-check me-2"></i>My Issued Books</h4>
            
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-striped align-middle">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Book Title</th>
                            <th>Dates</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($issued_arr) > 0): ?>
                            <?php foreach($issued_arr as $row): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php echo $row['book_name']; ?>
                                    <div class="small text-muted">ID: #<?php echo $row['book_no']; ?></div>
                                </td>
                                <td>
                                    <div><small class="text-muted">Issued:</small> <?php echo date('d M Y', strtotime($row['issue_date'])); ?></div>
                                    <div><small class="text-muted">Due:</small> <strong class="text-danger"><?php echo date('d M Y', strtotime($row['expected_return_date'])); ?></strong></div>
                                </td>
                                <td>
                                    <?php if($row['status'] == 1): ?>
                                        <span class="badge bg-warning text-dark mb-1 d-inline-block"><i class="bi bi-hourglass-split me-1"></i>Issued</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Returned</span>
                                        <?php if($row['fine_amount'] > 0): ?>
                                            <div class="mt-1">
                                                <?php if($row['fine_status'] == 'Unpaid'): ?>
                                                    <span class="badge bg-danger"><i class="bi bi-cash me-1"></i>Fine: Rs. <?php echo $row['fine_amount']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><i class="bi bi-check2-all me-1"></i>Fine Settled</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    You haven't issued any books yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
