<?php

set_time_limit(0);
try {
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );

    $db = new PDO('mysql:host=localhost;dbname=covidtracker;', 'root', '', $options);
} catch (PDOException $e) {
    die("can not connect to db");
}

$sqlA = "SELECT * FROM countries";
$stmt = $db->query($sqlA);



$sql1 = "SELECT * FROM cases";
$result = $db->query($sql1);




if ($result->rowCount() == 0) {


    while ($country = $stmt->fetch()) {
        $data1 = file_get_contents("https://api.covid19api.com/country/{$country['Slug']}?from=2021-01-01T00:00:00Z&to=2021-06-03T00:00:00Z");
        $data1 = json_decode($data1, true);

        $sqlA = "INSERT INTO `cases` (`country_id`, `active`, `deaths`, `recovered`, `confirmed`, date)
    VALUES (:country_id, :active, :deaths, :recovered, :confirmed, :date)";

        $stmtInsertCases = $db->prepare($sqlA);

        $stmtInsertCases->bindParam('country_id', $countryId);
        $stmtInsertCases->bindParam('active', $active);
        $stmtInsertCases->bindParam('recovered', $recovered);
        $stmtInsertCases->bindParam('deaths', $deaths);
        $stmtInsertCases->bindParam('confirmed', $confirmed);
        $stmtInsertCases->bindParam('date', $date);




        foreach ($data1 as $case) {
            $active = $case['Active'];
            $recovered = $case['Recovered'];
            $deaths = $case['Deaths'];
            $confirmed = $case['Confirmed'];
            $date = date("Y-m-d", strtotime($case['Date']));
            $countryId = $country['id'];


            $stmtInsertCases->execute();
        }
    }
}
