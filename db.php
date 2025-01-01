<?php
// Ρυθμίσεις σύνδεσης με τη βάση δεδομένων
$host = 'localhost';        // Διεύθυνση του διακομιστή MySQL
$dbname = 'database2';      // Όνομα της βάσης δεδομένων
$user = 'root';             // Όνομα χρήστη για πρόσβαση στη MySQL
$password = '';             // Κωδικός πρόσβασης (κενός για τον root από προεπιλογή στο XAMPP)

try {
    // Δημιουργία σύνδεσης με τη MySQL χρησιμοποιώντας PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    
    // Ρύθμιση PDO για εμφάνιση σφαλμάτων σε περίπτωση αποτυχίας
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Αν η σύνδεση είναι επιτυχής, συνεχίζεται η εκτέλεση του προγράμματος
} catch (PDOException $e) {
    // Αν η σύνδεση αποτύχει, τερματίζεται το πρόγραμμα και εμφανίζεται το μήνυμα σφάλματος
    die("Σφάλμα σύνδεσης με τη βάση: " . $e->getMessage());
}
?>
