<?php
# src/mysql.php — Έλεγχος σύνδεσης με τη βάση DockerDB (με PDO)
#
# Τα σχόλια [ΒΑΣΗ Χ] δείχνουν τα σημεία όπου η σελίδα «μιλάει» με τη βάση.

// ---- [ΒΑΣΗ 1] Στοιχεία σύνδεσης ----------------------------------------
// host  : το όνομα του service "mysql" στο docker-compose.yml (όχι localhost)
// dbname/username/password : ίδια με το env/mysql.env
//   MYSQL_DATABASE=DockerDB, MYSQL_USER=duser, MYSQL_PASSWORD=123456
$hostname	= "mysql";
$dbname		= "DockerDB";
$username	= "duser";
$password	= "123456";

try {

	// ---- [ΒΑΣΗ 2] Άνοιγμα σύνδεσης ------------------------------------
	// Το PDO δέχεται τα στοιχεία σε μορφή DSN:
	//   mysql:host=<server>;dbname=<βάση>;charset=<κωδικοποίηση>
	// Αν ο MySQL server δεν απαντά ή τα στοιχεία είναι λάθος,
	// πετάει PDOException και πηγαίνουμε στο catch ([ΒΑΣΗ 4]).
	$conn = new PDO( "mysql:host=$hostname;dbname=$dbname;charset=utf8mb4", $username, $password );

	// ---- [ΒΑΣΗ 3] Ρύθμιση συμπεριφοράς σε σφάλματα -------------------
	// Κάθε μελλοντικό σφάλμα SQL θα γίνεται exception.
	$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

	echo "Connected successfully";
}
catch( PDOException $e ) {

	// ---- [ΒΑΣΗ 4] Αποτυχία σύνδεσης ----------------------------------
	echo "Failed to connect: " . $e->getMessage();
}

// Εδώ θα έμπαιναν τα ερωτήματα SQL (SELECT, INSERT κ.λπ.) μέσω του $conn

// ---- [ΒΑΣΗ 5] Κλείσιμο σύνδεσης ----------------------------------------
// Στο PDO η σύνδεση κλείνει όταν το αντικείμενο πάψει να υπάρχει.
$conn = null;
