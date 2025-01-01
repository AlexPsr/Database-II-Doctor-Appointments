<?php
// Εισαγωγή αρχείου σύνδεσης με τη βάση δεδομένων
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Appointment System</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Doctor Appointment System</h1>

    <!-- Φόρμα για την επιλογή ερωτήματος από τον χρήστη -->
    <div class="form-container">
        <form method="GET">
            <label for="query">Επιλέξτε Ερώτημα:</label>
            <select name="query" id="query">
                <option value="1">Εύρεση μέσου όρου κόστους υπηρεσιών</option>
                <option value="2">Εύρεση αριθμών ραντεβού ανά ασθενή με ραντεβού > 1</option>
                <option value="3">Ταξινόμηση υπηρεσιών με βάση την διάρκεια και το κόστος</option>
                <option value="4">Εμφάνιση ραντεβού με στοιχεία ασθενών, γιατρών και υπηρεσιών</option>
                <option value="5">Εύρεση ραντεβού με στοιχεία γραμματέων, ασθενών, γιατρών και υπηρεσιών</option>
                <option value="6">Εύρεση ασθενών που έχουν κλείσει ραντεβού για συγκεκριμένη ημερομηνία</option>
                <option value="7">Εύρεση όλων των ραντεβού που προσφέρουν υπηρεσίες Dental Cleaning</option>
                <option value="8">Εύρεση μέγιστης διάρκειας ραντεβού</option>
                <option value="9">Εύρεση συνολικού κόστους ραντεβού ανά γραμματεία</option>
                <option value="10">Ταξινόμηση ραντεβού με βάση ημερομηνία και status</option>
            </select>
            <button type="submit">Εκτέλεση</button>
        </form>
    </div>

    <!-- Εμφάνιση αποτελεσμάτων για το επιλεγμένο ερώτημα -->
    <?php
    if (isset($_GET['query'])) {
        $queryId = (int)$_GET['query'];

        // Πίνακας ερωτημάτων SQL
        $queries = [
            1 => "SELECT AVG(service.service_cost) FROM service;",
            2 => "SELECT patient_amka, COUNT(*) AS total_appointments FROM appointment GROUP BY patient_amka HAVING total_appointments > 1;",
            3 => "SELECT service.service_name, service.service_cost, service.service_duration FROM service ORDER BY service.service_duration, service.service_cost;",
            4 => "SELECT a.appointment_date_time, u.first_name AS patient_name, d.specialty AS doctor_specialty, s.service_name FROM appointment a INNER JOIN user u ON a.patient_amka = u.amka INNER JOIN doctor d ON a.doctor_amka = d.amka INNER JOIN service s ON a.service_name = s.service_name;",
            5 => "SELECT a.appointment_date_time, u.first_name AS secretary_name, p.first_name AS patient_name, d.first_name AS doctor_name, ser.service_name FROM appointment a LEFT JOIN user u ON a.secretary_amka = u.amka LEFT JOIN user p ON a.patient_amka = p.amka LEFT JOIN user d ON a.doctor_amka = d.amka LEFT JOIN service ser ON a.service_name = ser.service_name;",
            6 => "SELECT u.first_name, u.last_name FROM user u WHERE u.amka IN (SELECT patient_amka FROM appointment WHERE DATE(appointment_date_time) = '2024-12-09');",
            7 => "SELECT a.appointment_date_time, s.service_name, d.first_name AS doctor_name, p.first_name AS patient_name, p.last_name AS patient_surname FROM appointment a JOIN service s ON a.service_name = s.service_name JOIN user d ON a.doctor_amka = d.amka JOIN user p ON a.patient_amka = p.amka WHERE s.service_name = 'Dental Cleaning';",
            8 => "SELECT MAX(service_duration) FROM service;",
            9 => "SELECT sec.first_name, sec.last_name, sec.amka, SUM(s.service_cost) FROM appointment a JOIN service s ON a.service_name = s.service_name JOIN user sec ON a.secretary_amka = sec.amka GROUP BY sec.first_name;",
            10 => "SELECT p.first_name AS patient_name, p.last_name AS patient_surname, d.first_name AS doctor_name, d.last_name AS doctor_surname, a.appointment_date_time, a.status FROM appointment a INNER JOIN user p ON p.amka = a.patient_amka INNER JOIN user d ON d.amka = a.doctor_amka ORDER BY a.appointment_date_time, a.status;"
        ];

        // Εκτέλεση του ερωτήματος αν είναι έγκυρο
        if (array_key_exists($queryId, $queries)) {
            $stmt = $pdo->query($queries[$queryId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Εμφάνιση αποτελεσμάτων σε πίνακα
            if ($results) {
                echo "<table>";
                echo "<tr>";
                foreach (array_keys($results[0]) as $column) {
                    echo "<th>" . htmlspecialchars($column) . "</th>";
                }
                echo "</tr>";
                foreach ($results as $row) {
                    echo "<tr>";
                    foreach ($row as $value) {
                        echo "<td>" . htmlspecialchars($value) . "</td>";
                    }
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Δεν βρέθηκαν αποτελέσματα.</p>";
            }
        } else {
            echo "<p>Μη έγκυρη επιλογή ερωτήματος.</p>";
        }
    }
    ?>
</body>
</html>
