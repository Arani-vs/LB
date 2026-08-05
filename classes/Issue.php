<?php
require_once 'Database.php';

class Issue {
    private $conn;
    private $table_name = "issued_books";
    private $table_users = "users";


    private $book_no;
    private $book_name;
    private $book_author;
    private $member_id;
    private $expected_return_date;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

 
    public function setBookNo($book_no) { $this->book_no = $book_no; }
    public function setBookName($book_name) { $this->book_name = $book_name; }
    public function setBookAuthor($book_author) { $this->book_author = $book_author; }
    public function setMemberId($member_id) { $this->member_id = $member_id; }
    public function setExpectedReturnDate($expected_return_date) { $this->expected_return_date = $expected_return_date; }

    public function issueBook() {
        $avail_query = $this->conn->query("SELECT copies, (SELECT COUNT(*) FROM " . $this->table_name . " WHERE book_no = b.book_no AND status = 1) as issued_count FROM books b WHERE book_no = " . $this->book_no);
        if ($avail_query && $avail_query->num_rows > 0) {
            $data = $avail_query->fetch_assoc();
            if (($data['copies'] - $data['issued_count']) <= 0) {
                return false; // Out of stock
            }
        }
        
        $status = 1; // 1 means issued
        $sql = "INSERT INTO " . $this->table_name . " (book_no, book_name, book_author, member_id, status, expected_return_date) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issiis", $this->book_no, $this->book_name, $this->book_author, $this->member_id, $status, $this->expected_return_date);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function getIssuedBooksByUser($member_id) {
        $sql = "SELECT * FROM " . $this->table_name . " WHERE member_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    public function getAllIssuedBooks() {
        $sql = "SELECT i.*, u.name as student_name FROM " . $this->table_name . " i
                LEFT JOIN " . $this->table_users . " u ON i.member_id = u.member_id";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function returnBook($s_no) {
        $fine_amount = 0;
        $fine_status = 'Settled';
        $query = $this->conn->query("SELECT expected_return_date FROM " . $this->table_name . " WHERE s_no = $s_no");
        if($query && $query->num_rows > 0){
            $row = $query->fetch_assoc();
            $expected = new DateTime($row['expected_return_date']);
            $today = new DateTime();
            if($today > $expected){
                $days_late = $today->diff($expected)->days;
                $fine_amount = $days_late * 10; // Rs 10 per day late
                $fine_status = 'Unpaid';
            }
        }
        
        $status = 0; // 0 means returned
        $sql = "UPDATE " . $this->table_name . " SET status = ?, fine_amount = ?, fine_status = ? WHERE s_no = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisi", $status, $fine_amount, $fine_status, $s_no);
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function settleFine($s_no) {
        $sql = "UPDATE " . $this->table_name . " SET fine_status = 'Settled' WHERE s_no = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $s_no);
        return $stmt->execute();
    }

    public function getUnpaidFines() {
        $sql = "SELECT i.*, u.name as student_name FROM " . $this->table_name . " i
                LEFT JOIN " . $this->table_users . " u ON i.member_id = u.member_id
                WHERE i.fine_status = 'Unpaid' AND i.fine_amount > 0";
        return $this->conn->query($sql);
    }

    public function getDefaulters() {
        // Assuming a book is defaulted if it is not returned (status=1) and we're past the expected_return_date
        $sql = "SELECT i.*, u.name as student_name, u.mobile, u.email 
                FROM " . $this->table_name . " i
                LEFT JOIN " . $this->table_users . " u ON i.member_id = u.member_id
                WHERE i.status = 1 AND NOW() > i.expected_return_date";
        $result = $this->conn->query($sql);
        return $result;
    }
}
?>