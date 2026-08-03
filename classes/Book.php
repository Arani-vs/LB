<?php
require_once 'database.php';

class Book {
    private $conn;
    private $table_books = "books";
    private $table_authors = "authors";
    private $table_category = "category";

    private $book_name;
    private $author_name;
    private $cat_id;
    private $book_no;
    private $book_price;
    private $copies;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAllBooks() {
        $sql = "SELECT b.book_id, b.book_name, b.book_no, b.book_price, b.copies, a.author_name, c.cat_name 
                FROM " . $this->table_books . " b 
                LEFT JOIN " . $this->table_authors . " a ON b.author_id = a.author_id 
                LEFT JOIN " . $this->table_category . " c ON b.cat_id = c.cat_id";
        $result = $this->conn->query($sql);
        return $result;
    }

    public function searchBook($query) {
        $query = "%".$query."%";
        $sql = "SELECT b.book_id, b.book_name, b.book_no, b.book_price, b.copies, a.author_name, c.cat_name 
                FROM " . $this->table_books . " b 
                LEFT JOIN " . $this->table_authors . " a ON b.author_id = a.author_id 
                LEFT JOIN " . $this->table_category . " c ON b.cat_id = c.cat_id
                WHERE b.book_name LIKE ? OR a.author_name LIKE ? OR b.book_no LIKE ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $query, $query, $query);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Setter Methods
    public function setBookName($book_name) { $this->book_name = $book_name; }
    public function setAuthorName($author_name) { $this->author_name = $author_name; }
    public function setCatId($cat_id) { $this->cat_id = $cat_id; }
    public function setBookNo($book_no) { $this->book_no = $book_no; }
    public function setBookPrice($book_price) { $this->book_price = $book_price; }
    public function setCopies($copies) { $this->copies = $copies; }

    public function addBook() {
        $author_id = $this->getOrCreateAuthor($this->author_name);
        
        $sql = "INSERT INTO " . $this->table_books . " (book_name, author_id, cat_id, book_no, book_price, copies) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("siiiii", $this->book_name, $author_id, $this->cat_id, $this->book_no, $this->book_price, $this->copies);
        if($stmt->execute()){
            return true;
        }
        return false;
    }
    
    public function getOrCreateAuthor($author_name) {
        $sql = "SELECT author_id FROM " . $this->table_authors . " WHERE author_name = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $author_name);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            return $row['author_id'];
        } else {
            $sql2 = "INSERT INTO " . $this->table_authors . " (author_name) VALUES (?)";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bind_param("s", $author_name);
            $stmt2->execute();
            return $this->conn->insert_id;
        }
    }
    
    public function deleteBook($book_id) {
        $sql = "DELETE FROM " . $this->table_books . " WHERE book_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $book_id);
        if($stmt->execute()){
            return true;
        }
        return false;
    }
    
    public function getAllAuthors() {
        $sql = "SELECT * FROM " . $this->table_authors;
        return $this->conn->query($sql);
    }
    
    public function getAllCategories() {
        $sql = "SELECT * FROM " . $this->table_category;
        return $this->conn->query($sql);
    }
}
?>
