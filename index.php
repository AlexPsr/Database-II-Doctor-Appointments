<?php
// Εισαγωγή αρχείου σύνδεσης με τη βάση δεδομένων
require 'db.php';

// Λήψη των ονομάτων των υπηρεσιών από τη βάση δεδομένων
$serviceNames = [];
try {
    $stmt = $pdo->query("SELECT service_name FROM service");
    $serviceNames = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    echo "<p>Σφάλμα κατά την ανάκτηση των ονομάτων των υπηρεσιών: " . htmlspecialchars($e->getMessage()) . "</p>";
}
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
        <form method="GET" onsubmit="hideImageContainer()">
            <label for="query">Επιλέξτε Ερώτημα:</label>
            <select name="query" id="query" onchange="showExtraFields()">
                <option value="" selected disabled hidden>Επιλέξτε ερώτημα</option>
                <option value="1">Εύρεση μέσου όρου κόστους υπηρεσιών</option>
                <option value="2">Εύρεση αριθμών ραντεβού ανά ασθενή ανάλογα με τον αριθμό των ραντεβού</option>
                <option value="3">Ταξινόμηση υπηρεσιών με βάση την διάρκεια και το κόστος</option>
                <option value="4">Εμφάνιση ραντεβού με στοιχεία ασθενών, γιατρών και υπηρεσιών</option>
                <option value="5">Εύρεση ραντεβού με στοιχεία γραμματέων, ασθενών, γιατρών και υπηρεσιών</option>
                <option value="6">Εύρεση ασθενών που έχουν κλείσει ραντεβού για συγκεκριμένη ημερομηνία</option>
                <option value="7">Εύρεση όλων των ραντεβού που προσφέρουν συγκεκριμένη υπηρεσία</option>
                <option value="8">Εύρεση μέγιστης διάρκειας ραντεβού</option>
                <option value="9">Εύρεση συνολικού κόστους ραντεβού ανά γραμματεία</option>
                <option value="10">Ταξινόμηση ραντεβού με βάση ημερομηνία και status</option>
            </select>
            <div id="extra-fields"></div>
            <button type="submit">Εκτέλεση</button>
        </form>
    </div>

    <div id="image-container">
        <img src="images/logo.png" alt="Your Image">
    </div>

    <script>
        function showExtraFields() {
            const query = document.getElementById('query').value;
            const extraFields = document.getElementById('extra-fields');
            extraFields.innerHTML = '';

            if (query == '2') {
                extraFields.innerHTML = `
                    <label for="min_appointments">Ελάχιστος αριθμός ραντεβού:</label>
                    <input type="number" name="min_appointments" id="min_appointments" value="1">
                `;
            } else if (query == '6') {
                extraFields.innerHTML = `
                    <label for="appointment_date">Ημερομηνία:</label>
                    <input type="date" name="appointment_date" id="appointment_date" value="2024-12-09">
                `;
            } else if (query == '7') {
                let serviceOptions = <?php echo json_encode($serviceNames); ?>;
                let optionsHtml = serviceOptions.map(service => `<option value="${service}">${service}</option>`).join('');
                extraFields.innerHTML = `
                    <label for="service_name">Όνομα υπηρεσίας:</label>
                    <select name="service_name" id="service_name">
                        ${optionsHtml}
                    </select>
                `;
            }
        }

        function hideImageContainer() {
            const imageContainer = document.getElementById('image-container');
            imageContainer.style.display = 'none';
        }

        // Call showExtraFields on page load to preserve extra fields after form submission
        document.addEventListener('DOMContentLoaded', function() {
            showExtraFields();
            <?php if (isset($_GET['query'])): ?>
                hideImageContainer();
            <?php endif; ?>
        });
    </script>

    <!-- Εμφάνιση αποτελεσμάτων για το επιλεγμένο ερώτημα -->
    <?php
    if (isset($_GET['query'])) {
        $queryId = (int)$_GET['query'];

        // Πίνακας ερωτημάτων SQL
        $queries = [
            1 => "SELECT AVG(service.service_cost) AS 'Average Cost' FROM service;",
            2 => "SELECT patient_amka AS 'Patient AMKA', COUNT(*) AS 'Total Appointments' FROM appointment GROUP BY patient_amka HAVING COUNT(*) > :min_appointments;",
            3 => "SELECT service.service_name AS 'Service Name', service.service_cost AS 'Service Cost', service.service_duration AS 'Service Duration' FROM service ORDER BY service.service_duration, service.service_cost;",
            4 => "SELECT a.appointment_date_time AS 'Appointment Date', u.first_name AS 'Patient Name', d.specialty AS 'Doctor Specialty', s.service_name AS 'Service Name' FROM appointment a INNER JOIN user u ON a.patient_amka = u.amka INNER JOIN doctor d ON a.doctor_amka = d.amka INNER JOIN service s ON a.service_name = s.service_name;",
            5 => "SELECT a.appointment_date_time AS 'Appointment Date', u.first_name AS 'Secretary Name', p.first_name AS 'Patient Name', d.first_name AS 'Doctor Name', ser.service_name AS 'Service Name' FROM appointment a LEFT JOIN user u ON a.secretary_amka = u.amka LEFT JOIN user p ON a.patient_amka = p.amka LEFT JOIN user d ON a.doctor_amka = d.amka LEFT JOIN service ser ON a.service_name = ser.service_name;",
            6 => "SELECT u.first_name AS 'First Name', u.last_name AS 'Last Name' FROM user u WHERE u.amka IN (SELECT patient_amka FROM appointment WHERE DATE(appointment_date_time) = :appointment_date);",
            7 => "SELECT a.appointment_date_time AS 'Appointment Date', s.service_name AS 'Service Name', d.first_name AS 'Doctor Name', p.first_name AS 'Patient Name', p.last_name AS 'Patient Surname' FROM appointment a JOIN service s ON a.service_name = s.service_name JOIN user d ON a.doctor_amka = d.amka JOIN user p ON a.patient_amka = p.amka WHERE s.service_name = :service_name;",
            8 => "SELECT MAX(service_duration) AS 'Max Duration' FROM service;",
            9 => "SELECT sec.first_name AS 'Secretary First Name', sec.last_name AS 'Secretary Surname', sec.amka, SUM(s.service_cost) AS 'Total Cost' FROM appointment a JOIN service s ON a.service_name = s.service_name JOIN user sec ON a.secretary_amka = sec.amka GROUP BY sec.first_name;",
            10 => "SELECT p.first_name AS 'Patient Name', p.last_name AS 'Patient Surname', d.first_name AS 'Doctor Name', d.last_name AS 'Doctor Surname', a.appointment_date_time AS 'Appointment Date', a.status FROM appointment a INNER JOIN user p ON p.amka = a.patient_amka INNER JOIN user d ON d.amka = a.doctor_amka ORDER BY a.appointment_date_time, a.status;"
        ];

        // Εκτέλεση του ερωτήματος αν είναι έγκυρο
        if (array_key_exists($queryId, $queries)) {
            $stmt = $pdo->prepare($queries[$queryId]);

            // Δέσμευση παραμέτρων για τα ερωτήματα 2, 6 και 7
            if ($queryId == 2 && isset($_GET['min_appointments'])) {
                $stmt->bindValue(':min_appointments', (int)$_GET['min_appointments'], PDO::PARAM_INT);
            } elseif ($queryId == 6 && isset($_GET['appointment_date'])) {
                $stmt->bindValue(':appointment_date', $_GET['appointment_date'], PDO::PARAM_STR);
            } elseif ($queryId == 7 && isset($_GET['service_name'])) {
                $stmt->bindValue(':service_name', $_GET['service_name'], PDO::PARAM_STR);
            }

            try {
                $stmt->execute();
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
            } catch (PDOException $e) {
                echo "<p>Σφάλμα κατά την εκτέλεση του ερωτήματος: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        } else {
            echo "<p>Μη έγκυρη επιλογή ερωτήματος.</p>";
        }
    }
    ?>
</body>
</html>