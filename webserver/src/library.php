<?php
// Σύνδεση στη Βάση Δεδομένων
$servername = "mysql";
$username = "root";
$password = "123456";
$dbname = "bookstore";

$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος Σύνδεσης
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ερώτημα για Εμφάνιση Βιβλίων
$sql = "SELECT id, title, author, publication_year FROM books";
$result = $conn->query($sql);

// Εμφάνιση Δεδομένων
if ($result->num_rows > 0) {
    echo "<h2>Λίστα Βιβλίων</h2>";
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row["title"] . " - " . $row["author"] . " (" . $row["publication_year"] . ")</li>";
    }
    echo "</ul>";
} else {
    echo "Δεν βρέθηκαν βιβλία.";
}

// Κλείσιμο Σύνδεσης
$conn->close();
?>