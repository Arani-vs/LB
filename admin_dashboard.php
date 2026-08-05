<?php
require_once 'includes/header.php';
require_once 'classes/Book.php';
require_once 'classes/Issue.php';
require_once 'classes/User.php';

if(!isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit;
}

$book = new Book();
$issue = new Issue();
$userObj = new User();

// Handle Actions
$message = "";
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['add_book'])){
        $book->setBookName($_POST['book_name']);
        $book->setAuthorName($_POST['author_name']);
        $book->setCatId($_POST['cat_id']);
        $book->setBookNo($_POST['book_no']);
        $book->setBookPrice($_POST['book_price']);
        $book->setCopies($_POST['copies']);
        $book->addBook();
        $message = "<div class='alert alert-success alert-dismissible fade show shadow-sm'><i class='bi bi-check-circle-fill me-2'></i>Book added successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    }
    if(isset($_POST['issue_book'])){
        $book_no = $_POST['book_no'];
        $student_id = $_POST['student_id'];
        
        // Let's get book details properly
        $db_instance = new Database();
        $db_conn = $db_instance->connect();
        
        $book_details_query = $db_conn->query("SELECT b.book_name, a.author_name FROM books b LEFT JOIN authors a ON b.author_id = a.author_id WHERE b.book_no = $book_no");
        if($book_details_query && $book_details_query->num_rows > 0) {
            $b_data = $book_details_query->fetch_assoc();
            $expected = date('Y-m-d', strtotime('+14 days'));
            
            $issue->setBookNo($book_no);
            $issue->setBookName($b_data['book_name']);
            $issue->setBookAuthor($b_data['author_name']);
            $issue->setMemberId($student_id);
            $issue->setExpectedReturnDate($expected);
            
            $issue->issueBook();
            $message = "<div class='alert alert-success alert-dismissible fade show shadow-sm'><i class='bi bi-check-circle-fill me-2'></i>Book issued successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
    if(isset($_POST['return_book'])){
        $issue->returnBook($_POST['s_no']);
    }
    if(isset($_POST['toggle_user_status'])){
        if($userObj->toggleUserStatus($_POST['user_id'], $_POST['new_status'])) {
            $status_text = $_POST['new_status'] == 1 ? "Activated" : "Deactivated";
            $message = "<div class='alert alert-info alert-dismissible fade show shadow-sm'><i class='bi bi-info-circle-fill me-2'></i>User successfully <strong>$status_text</strong>!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        }
    }
    if(isset($_POST['settle_fine'])){
        $issue->settleFine($_POST['s_no']);
    }
}

$allBooks = $book->getAllBooks();
$allUsers = $userObj->getAllUsers();
$allIssued = $issue->getAllIssuedBooks();
$defaulters = $issue->getDefaulters();
$authors = $book->getAllAuthors();
$categories = $book->getAllCategories();

?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card-clean">
            <h4>Admin Dashboard</h4>
            <ul class="nav nav-pills mt-3" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="books-tab" data-bs-toggle="tab" data-bs-target="#books" type="button" role="tab">Manage Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="students-tab" data-bs-toggle="tab" data-bs-target="#members" type="button" role="tab">Manage Members</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="issued-tab" data-bs-toggle="tab" data-bs-target="#issued" type="button" role="tab">View Issued Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="defaulters-tab" data-bs-toggle="tab" data-bs-target="#defaulters" type="button" role="tab">Defaulter List</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fines-tab" data-bs-toggle="tab" data-bs-target="#fines" type="button" role="tab">Fines</button>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php if($message != "") echo $message; ?>

<div class="tab-content" id="adminTabsContent">
    <!-- Manage Books -->
    <div class="tab-pane fade show active" id="books" role="tabpanel">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card-clean">
                    <h5>Add New Book</h5>
                    <form method="POST">
                        <input type="hidden" name="add_book" value="1">
                        <div class="mb-2">
                            <label>Book Name</label>
                            <input type="text" name="book_name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Book No</label>
                            <input type="number" name="book_no" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label>Author</label>
                            <input type="text" name="author_name" class="form-control" placeholder="Type author name..." required>
                        </div>
                        <div class="mb-2">
                            <label>Category</label>
                            <select name="cat_id" class="form-select" required>
                                <?php while($c = $categories->fetch_assoc()): ?>
                                    <option value="<?php echo $c['cat_id']; ?>"><?php echo $c['cat_name']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label>Price</label>
                            <input type="number" name="book_price" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Number of Copies</label>
                            <input type="number" name="copies" class="form-control" value="1" min="1" required>
                        </div>
                        <button type="submit" name="add_book" class="btn btn-primary-custom w-100">Add Book</button>
                    </form>
                </div>
            </div>
            <div class="col-12">
                <div class="card-clean p-4">
                    <h5 class="mb-4">All Books</h5>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-hover align-middle">
                            <thead><tr><th>No</th><th>Name</th><th>Author</th><th>Category</th><th>Copies</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php while($b = $allBooks->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $b['book_no']; ?></td>
                                    <td><?php echo $b['book_name']; ?></td>
                                    <td><?php echo $b['author_name']; ?></td>
                                    <td><?php echo $b['cat_name']; ?></td>
                                    <td><span class="badge bg-secondary"><?php echo $b['copies']; ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#issueModal<?php echo $b['book_no']; ?>">Issue</button>
                                        
                                        <!-- Issue Modal -->
                                        <div class="modal fade" id="issueModal<?php echo $b['book_no']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title">Issue '<?php echo $b['book_name']; ?>'</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="book_no" value="<?php echo $b['book_no']; ?>">
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small text-uppercase fw-bold">Member ID</label>
                                                                <input type="number" name="student_id" class="form-control" required placeholder="Enter member's ID number">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="issue_book" class="btn btn-primary-custom px-4">Confirm Issue</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Users -->
    <div class="tab-pane fade" id="members" role="tabpanel">
        <div class="card-clean">
            <h5 class="mb-4">Registered Users</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php 
                    $allUsers = $userObj->getAllUsers();
                    while($u = $allUsers->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo $u['member_id']; ?></td>
                        <td><?php echo $u['name']; ?></td>
                        <td><?php echo $u['email']; ?></td>
                        <td><?php echo isset($u['role']) ? $u['role'] : 'Member'; ?></td>
                        <td>
                            <?php if(!isset($u['status']) || $u['status'] == 1): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="toggle_user_status" value="1">
                                <input type="hidden" name="user_id" value="<?php echo $u['member_id']; ?>">
                                <?php if(!isset($u['status']) || $u['status'] == 1): ?>
                                    <input type="hidden" name="new_status" value="0">
                                    <button type="submit" class="btn btn-sm btn-danger">Deactivate</button>
                                <?php else: ?>
                                    <input type="hidden" name="new_status" value="1">
                                    <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- View Issued Books -->
    <div class="tab-pane fade" id="issued" role="tabpanel">
        <div class="card-clean">
            <h5 class="mb-4">Issued Books</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                <thead><tr><th>Book No</th><th>Book Name</th><th>Member Name</th><th>Issue Date</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    <?php while($i = $allIssued->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i['book_no']; ?></td>
                        <td><?php echo $i['book_name']; ?></td>
                        <td><?php echo $i['student_name']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($i['issue_date'])); ?></td>
                        <td>
                            <?php if($i['status'] == 1): ?>
                                <span class="badge bg-warning">Issued</span>
                            <?php else: ?>
                                <span class="badge bg-success">Returned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($i['status'] == 1): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="return_book" value="1">
                                <input type="hidden" name="s_no" value="<?php echo $i['s_no']; ?>">
                                <button type="submit" class="btn btn-sm btn-success">Return</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- Defaulters -->
    <div class="tab-pane fade" id="defaulters" role="tabpanel">
        <div class="card-clean">
            <h5 class="mb-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Defaulter List (Overdue Books)</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                <thead><tr><th>Book Name</th><th>Member</th><th>Email</th><th>Mobile</th><th>Issue Date</th></tr></thead>
                <tbody>
                    <?php if($defaulters->num_rows > 0): ?>
                        <?php while($d = $defaulters->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $d['book_name']; ?></td>
                            <td><?php echo $d['student_name']; ?></td>
                            <td><?php echo $d['email']; ?></td>
                            <td><?php echo $d['mobile']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($d['issue_date'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No defaulters found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    
    <!-- Fines Tab -->
    <div class="tab-pane fade" id="fines" role="tabpanel">
        <div class="card-clean">
            <h4 class="mb-4 text-warning"><i class="bi bi-cash-coin me-2"></i>Manage Fines</h4>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Member Name</th>
                            <th>Book Name</th>
                            <th>Fine Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $fines = $issue->getUnpaidFines();
                        if($fines && $fines->num_rows > 0): 
                            while($row = $fines->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><?php echo $row['student_name']; ?></td>
                            <td><?php echo $row['book_name']; ?></td>
                            <td class="text-danger fw-bold">Rs. <?php echo $row['fine_amount']; ?></td>
                            <td><span class="badge bg-danger"><?php echo $row['fine_status']; ?></span></td>
                            <td>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="s_no" value="<?php echo $row['s_no']; ?>">
                                    <button type="submit" name="settle_fine" class="btn btn-sm btn-success rounded-pill px-3">Mark Settled</button>
                                </form>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                        <tr><td colspan="5" class="text-center py-4">No unpaid fines!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Retrieve active tab from local storage
    var activeTab = localStorage.getItem('adminActiveTab');
    if (activeTab) {
        var targetBtn = document.querySelector('button[data-bs-target="' + activeTab + '"]');
        if (targetBtn) {
            var tab = new bootstrap.Tab(targetBtn);
            tab.show();
        }
    }

    // Save active tab to local storage on click
    var tabElements = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabElements.forEach(function(tabEl) {
        tabEl.addEventListener('shown.bs.tab', function (event) {
            var target = event.target.getAttribute('data-bs-target');
            localStorage.setItem('adminActiveTab', target);
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
