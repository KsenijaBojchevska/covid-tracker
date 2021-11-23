<?php
require_once("connectCountries.php");
require_once("connectCases.php");
//require_once("syncData.php");

try {
    $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    );
    $db = new PDO('mysql:host=localhost;dbname=covidtracker;', 'root', '', $options);
} catch (PDOException $e) {
    die();
}
$sql = "SELECT countries.Country, 
last_two_days_data.today_active,
last_two_days_data.today_recovered,
last_two_days_data.today_deaths,
last_two_days_data.today_confirmed,
last_two_days_data.yesterday_active,
last_two_days_data.yesterday_recovered,
last_two_days_data.yesterday_deaths,
last_two_days_data.yesterday_confirmed
FROM (
    SELECT * FROM
    (
      SELECT 
        cases.active as today_active, 
        cases.recovered as today_recovered, 
        cases.deaths as today_deaths, 
        cases.confirmed as today_confirmed, 
        cases.country_id,
        (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) row_number_today
      FROM cases
    ) AS today_data
    JOIN (
      SELECT 
        cases.active as yesterday_active, 
        cases.recovered as yesterday_recovered, 
        cases.deaths as yesterday_deaths, 
        cases.confirmed as yesterday_confirmed, 
        cases.country_id as c_id,
        (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) row_number_yesterday
      FROM cases
    ) as yesterday_data

     ON 
        today_data.country_id = yesterday_data.c_id 
        AND yesterday_data.row_number_yesterday = today_data.row_number_today + 1
        AND today_data.row_number_today <> 1 
     GROUP BY today_data.country_id
) as last_two_days_data 
JOIN countries ON last_two_days_data.country_id = countries.id";
$stmt = $db->query($sql);
$stmt1 = $db->query($sql);

?>

<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://kit.fontawesome.com/2adeb4644b.js" crossorigin="anonymous"></script>

    <title>Ksenija's project 2</title>
</head>

<body>

    <div class="container-fluid">
        <nav id="navbar" class="navbar navbar-expand-lg  ">
            <a class="navbar-brand  text-dark text-center   fw-bold text-shadow1 " href="index.php">
                <img src="images/images.jpg" class="w-25 rounded-circle img-shadow"><br>
                Covid Tracker</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">

            </button>

            <div class="collapse navbar-collapse " id="navbarSupportedContent">
                <ul class="navbar-nav  m-auto marginRight">
                    <li class="navbar-item  mx-5 y ">
                        <a class="nav-link text-dark text-uppercase  fw-bold text-shadow1" href="about.html">About</a>
                    </li>
                    <li class="navbar-item mx-5 ">
                        <a class="nav-link text-dark text-uppercase  fw-bold text-shadow1 " href="takeCare.html">Take
                            Care</a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="row pt-1">
            <div class="col-md-2 offset-md-10">
                <button type="button" id="syncdata"
                    class="btn  text-dark fw-bold  me-5   border border-dark border-2 shadow">Sync
                    Data</button>
            </div>
        </div>

        <h1 class="text-center fw-bold text-uppercase text-dark text-shadow1 p-5 m-5 ">Covid-19 Tracker</h1>

        <div class="row mx-5">


            <div class="col-md-10 offset-md-1">
                <form method="post">
                    <div class="row">
                        <div class="col-md-4 text-center d-grid gap-2"><input id="dailyAll" class="btn btn-primary"
                                type="submit" name="dailyAll" value="Daily cases" /></div>
                        <div class="col-md-4 text-center d-grid gap-2"><input id="monthlyAll" class="btn btn-primary"
                                type="submit" name="monthlyAll" value="Monthly cases" /></div>
                        <div class="col-md-4 text-center d-grid gap-2"><input id="90dayAll" class="btn btn-primary"
                                type="submit" name="90dayAll" value="Last three months cases" /></div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <h3 class="text-center fw-bold text-uppercase m-5 text-shadow1">Coronavirus Cases:</h3>

    <?php

    if ((isset($_POST['dailyAll'])) || (!isset($_POST['dailyAll']) && !isset($_POST['monthlyAll']) && !isset($_POST['90dayAll']))) {
        $sqlToday = "SELECT * 
            FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
            INNER JOIN countries ON countries.id = partitioned_table.country_id
            WHERE partitioned_table.row_num = 1";
        $stmt5 = $db->query($sqlToday);
        if ($stmt5->rowCount()) {
            $dataToday = $stmt5->fetch();
            $date = $dataToday['Date'];
            $sql = "SELECT sum(confirmed),sum(deaths),sum(recovered),sum(active) FROM cases WHERE date='$date'";
            $stmtSum = $db->query($sql);
            while ($country = $stmtSum->fetch()) {
                echo "  <h2 class='fw-bold text-center mt-3'>Confirmed:</h2>";
                echo "<h3  class='text-center fw-bold text-secondary font-weight-bold'>" . number_format($country['sum(confirmed)']) . "</h3 >";
                echo "<h2 class='text-center fw-bold'>Deaths:</h2>";
                echo "<h3  class='text-center fw-bold text-danger font-weight-bold'>" . number_format($country['sum(deaths)']) . " </h3 >";
                echo "<h2  class='text-center fw-bold'>Recovered:</h2>";
                echo "<h3  class='text-center fw-bold text-primary font-weight-bold'>" . number_format($country['sum(recovered)']) . "</h3 >";
                echo "<h2  class='text-center fw-bold'>Active:</h2>";
                echo "<h3  class='text-center fw-bold text-secondary font-weight-bold'>" . number_format($country['sum(active)']) . "</h3 >";
            }
        }
    }




    if (isset($_POST['monthlyAll'])) {
        $sqlToday = "SELECT * 
          FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
          INNER JOIN countries ON countries.id = partitioned_table.country_id
          WHERE partitioned_table.row_num = 1";
        $sqlLastMonth = "SELECT * 
          FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
          INNER JOIN countries ON countries.id = partitioned_table.country_id
          WHERE partitioned_table.row_num = 30";

        $stmt5 = $db->query($sqlToday);
        $stmtLast = $db->query($sqlLastMonth);

        if ($stmt5->rowCount()) {
            $dataToday = $stmt5->fetch();
            $date = $dataToday['Date'];
        }
        if ($stmtLast->rowCount()) {
            $dataLast = $stmtLast->fetch();
            $dateLastMonth = $dataLast['Date'];
        }



        $sql = "SELECT sum(confirmed),sum(deaths),sum(recovered),sum(active) FROM cases WHERE date BETWEEN '$dateLastMonth' AND '$date'";
        $stmtSum = $db->query($sql);
        while ($country = $stmtSum->fetch()) {
            echo "  <h2 class='text-center fw-bold mt-3'>Confirmed:</h2>";
            echo "<h3  class='text-center fw-bold text-secondary font-weight-bold'>" . number_format($country['sum(confirmed)']) . "</h3 >";
            echo "<h2 class='text-center fw-bold'>Deaths:</h2>";
            echo "<h3  class='text-center fw-bold text-danger font-weight-bold'>" . number_format($country['sum(deaths)']) . " </h3 >";
            echo "<h2  class='text-center fw-bold'>Recovered:</h2>";
            echo "<h3  class='text-center fw-bold text-primary font-weight-bold'>" . number_format($country['sum(recovered)']) . "</h3 >";
            echo "<h2  class='text-center fw-bold'>Active:</h2>";
            echo "<h3  class='text-center fw-bold text-secondary font-weight-bold'>" . number_format($country['sum(active)']) . "</h3 >";
        }
    }
    if (isset($_POST['90dayAll'])) {
        $sqlToday = "SELECT * 
   FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
   INNER JOIN countries ON countries.id = partitioned_table.country_id
   WHERE partitioned_table.row_num = 1";
        $sqlLast3Months = "SELECT * 
   FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
   INNER JOIN countries ON countries.id = partitioned_table.country_id
   WHERE partitioned_table.row_num = 90";

        $stmt5 = $db->query($sqlToday);
        $stmtLast3 = $db->query($sqlLast3Months);

        if ($stmt5->rowCount()) {
            $dataToday = $stmt5->fetch();
            $date = $dataToday['Date'];
        }
        if ($stmtLast3->rowCount()) {
            $dataLast3 = $stmtLast3->fetch();
            $dateLast3Months = $dataLast3['Date'];
        }



        $sql = "SELECT sum(confirmed),sum(deaths),sum(recovered),sum(active) FROM cases WHERE date BETWEEN '$dateLast3Months' AND '$date'";
        $stmtSum = $db->query($sql);
        while ($country = $stmtSum->fetch()) {
            echo "  <h2 class='text-center fw-bold mt-3'>Confirmed:</h2>";
            echo "<h3  class='text-center fw-bold  text-secondary font-weight-bold'>" . number_format($country['sum(confirmed)']) . "</h3 >";
            echo "<h2 class='text-center fw-bold '>Deaths:</h2>";
            echo "<h3  class='text-center fw-bold  text-danger font-weight-bold'>" . number_format($country['sum(deaths)']) . " </h3 >";
            echo "<h2  class='text-center fw-bold '>Recovered:</h2>";
            echo "<h3  class='text-center fw-bold  text-primary font-weight-bold'>" . number_format($country['sum(recovered)']) . "</h3 >";
            echo "<h2  class='text-center fw-bold '>Active:</h2>";
            echo "<h3  class='text-center fw-bold  text-secondary font-weight-bold'>" . number_format($country['sum(active)']) . "</h3 >";
        }
    }
    ?>
    </div>


    <?php
    set_time_limit(500);
    try {
        $db = new PDO("mysql:host=localhost;dbname=covidtracker", "root", "");
    } catch (PDOException $e) {
        die("can not connect to db");
    }
    $query = $db->query("SELECT Country FROM countries ");
    $rowCount = $query->rowCount();
    ?>

    <div class="row pt-5">
        <div class="col-md-10 offset-md-2">

            <form method="post">
                <div class="row">
                    <div class="col-md-2 text-center d-grid gap-2"><input class="btn btn-dark" type="submit"
                            name="daily" value="Daily cases" /></div>
                    <div class="col-md-2 text-center d-grid gap-2"><input class="btn btn-dark" type="submit"
                            name="monthly" value="Monthly cases" /></div>
                    <div class="col-md-2 text-center d-grid gap-2"><input class="btn btn-dark" type="submit"
                            name="quartely" value="Quartely cases" /></div>
                    <div class="col-md-2 text-center d-grid gap-2"><input list="country" id="search"
                            placeholder="Select Country...">
                        <datalist class="p-2 shadow rounded btn-primary fw-bold" name="country" id="country">

                            <?php
                            if ($rowCount > 0) {
                                while ($row = $query->fetch()) {
                                    echo '<option class="fw-bold ' . $row['Country'] . '" value="' . $row['Country'] . '">' . $row['Country'] . '</option>';
                                }
                            } else {
                                echo '<option value="">Country not available</option>';
                            }
                            ?>
                        </datalist>
                    </div>
            </form>
        </div><br>
    </div>
    </div>



    <table class="table table-hover container bg-light" id="countryTable">
        <tr class="tHead bg-primary">
            <th class=" fw-bold text-center">Country</th>
            <th class=" fw-bold text-center">Total Cases</th>
            <th class="fw-bold text-center">Active Cases</th>
            <th class="fw-bold text-center">Deaths</th>
            <th class=" fw-bold text-center">Recovered</th>
            <th class=" fw-bold text-center">New cases</th>
            <th class="fw-bold text-center">New deaths</th>
            <th class=" fw-bold text-center">New recovered</th>
        </tr>
        <?php

        while ($country = $stmt->fetch()) {
            $todayConfirmed = $country['today_confirmed'];
            $todayActive = $country['today_active'];
            $todayDeaths = $country['today_deaths'];
            $todayRecovered = $country['today_recovered'];
            $yesterdayConfirmed = $country['yesterday_confirmed'];
            $yesterdayDeaths = $country['yesterday_deaths'];
            $yesterdayRecovered = $country['yesterday_recovered'];

            if (isset($_POST['daily'])) {
                echo "<tr>
                <td class='text-center fw-bold bg-primary'>{$country['Country']}</td>
                <td class='text-center fw-bold bg-secondary'>{$todayConfirmed}</td>
                <td class='text-center fw-bold bg-secondary'>{$todayActive}</td>
                <td class='text-center fw-bold bg-danger'>{$todayDeaths}</td>
                <td class='text-center fw-bold bg-secondary'>{$todayRecovered}</td>
                <td class='text-center fw-bold bg-secondary'>" . ($todayConfirmed - $yesterdayConfirmed) . "</td>
                <td class='text-center fw-bold bg-danger'>" . ($todayDeaths - $yesterdayDeaths) . "</td>
                <td class='text-center fw-bold bg-secondary'>" . ($todayRecovered - $yesterdayRecovered) . "</td>
            </tr>";
            } else if (!isset($_POST['daily']) && !isset($_POST['monthly']) && !isset($_POST['quartely'])) {
                echo "<tr>
            <td class='text-center fw-bold'>{$country['Country']}</td>
            <td class='text-center fw-bold'>{$todayConfirmed}</td>
            <td class='text-center fw-bold'>{$todayActive}</td>
            <td class='text-center fw-bold'>{$todayDeaths}</td>
            <td class='text-center fw-bold'>{$todayRecovered}</td>
            <td class='text-center fw-bold'>" . ($todayConfirmed - $yesterdayConfirmed) . "</td>
            <td class='text-center fw-bold'>" . ($todayDeaths - $yesterdayDeaths) . "</td>
            <td class='text-center fw-bold'>" . ($todayRecovered - $yesterdayRecovered) . "</td>
        </tr>";
            }
        }

        $sqlToday = "SELECT * 
    FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
    INNER JOIN countries ON countries.id = partitioned_table.country_id
    WHERE partitioned_table.row_num = 1";
        $stmt = $db->query($sqlToday);
        while ($country = $stmt->fetch()) {
            $todayTotal = $country['confirmed'];
            $todayActive = $country['active'];
            $todayDeaths = $country['deaths'];
            $todayRecovered = $country['recovered'];
            $dateToday = $country['Date'];
            $newCases = '/';
            $newDeaths = '/';
            $newRecovered = '/';
            $sqlLastDayOfMonth = "SELECT * FROM cases WHERE country_id = {$country['id']} ORDER BY `date` DESC LIMIT 1 OFFSET 30";
            $stmtLastDayOfMonth = $db->query($sqlLastDayOfMonth);
            if ($stmtLastDayOfMonth->rowCount()) {
                $dataLastDayOfMonth = $stmtLastDayOfMonth->fetch();
                $dateLastMonth = $dataLastDayOfMonth['Date'];
                $newCases = $todayTotal - $dataLastDayOfMonth['confirmed'];
                $newDeaths = $todayDeaths - $dataLastDayOfMonth['deaths'];
                $newRecovered = $todayRecovered - $dataLastDayOfMonth['recovered'];
            }
            $id = $country['id'];
            $sqlForLastMonth = "SELECT sum(confirmed) as sum_confirmed,sum(deaths) as sum_deaths,
        sum(recovered) as sum_recovered,sum(active)  as sum_active
        FROM cases WHERE country_id = $id and date BETWEEN  '$dateLastMonth' AND  '$dateToday'";
            $stmtSumLastMonth = $db->query($sqlForLastMonth);
            if ($stmtSumLastMonth->rowCount()) {
                $dataLastMonth = $stmtSumLastMonth->fetch();
                $sum_confirmed = $dataLastMonth['sum_confirmed'];
                $sum_deaths = $dataLastMonth['sum_deaths'];
                $sum_recovered =  $dataLastMonth['sum_recovered'];
                $sum_active =  $dataLastMonth['sum_active'];
            }
            if (isset($_POST['monthly'])) {
                echo "<tr class='row_graph'>
          <td class='text-center fw-bold bg-primary'>" . utf8_encode($country['Country']) . "</td>
          <td class='text-center fw-bold bg-secondary'>{$sum_confirmed}</td>
          <td class='text-center fw-bold bg-secondary'>{$sum_active}</td>
          <td class='text-center fw-bold bg-danger'>{$sum_deaths}</td>
          <td class='text-center fw-bold bg-secondary'>{$sum_recovered}</td>
          <td class='text-center fw-bold bg-secondary'>{$newCases}</td>
          <td class='text-center fw-bold bg-danger'>{$newDeaths}</td>
          <td class='text-center fw-bold bg-secondary'>{$newRecovered}</td>
          </tr>";
            }
        }

        $sqlToday = "SELECT * 
       FROM ( SELECT *, (ROW_NUMBER() OVER (PARTITION BY country_id ORDER BY date DESC)) as row_num FROM cases ) partitioned_table 
       INNER JOIN countries ON countries.id = partitioned_table.country_id
       WHERE partitioned_table.row_num = 1";
        $stmt = $db->query($sqlToday);
        while ($country = $stmt->fetch()) {
            $todayTotal = $country['confirmed'];
            $todayActive = $country['active'];
            $todayDeaths = $country['deaths'];
            $todayRecovered = $country['recovered'];
            $dateToday = $country['Date'];
            $newCases = '/';
            $newDeaths = '/';
            $newRecovered = '/';
            $sqlLastDayOfMonth = "SELECT * FROM cases WHERE country_id = {$country['id']} ORDER BY `date` DESC LIMIT 1 OFFSET 90";
            $stmtLastDayOfMonth = $db->query($sqlLastDayOfMonth);
            if ($stmtLastDayOfMonth->rowCount()) {
                $dataLastDayOfMonth = $stmtLastDayOfMonth->fetch();
                $dateLastMonth = $dataLastDayOfMonth['Date'];
                $newCases = $todayTotal - $dataLastDayOfMonth['confirmed'];
                $newDeaths = $todayDeaths - $dataLastDayOfMonth['deaths'];
                $newRecovered = $todayRecovered - $dataLastDayOfMonth['recovered'];
            }
            $id = $country['id'];
            $sqlForLastMonth = "SELECT sum(confirmed) as sum_confirmed,sum(deaths) as sum_deaths,
           sum(recovered) as sum_recovered,sum(active)  as sum_active
           FROM cases WHERE country_id = $id and date BETWEEN  '$dateLastMonth' AND  '$dateToday'";
            $stmtSumLastMonth = $db->query($sqlForLastMonth);
            if ($stmtSumLastMonth->rowCount()) {
                $dataLastMonth = $stmtSumLastMonth->fetch();
                $sum_confirmed = $dataLastMonth['sum_confirmed'];
                $sum_deaths = $dataLastMonth['sum_deaths'];
                $sum_recovered =  $dataLastMonth['sum_recovered'];
                $sum_active =  $dataLastMonth['sum_active'];
            }
            if (isset($_POST['quartely'])) {
                echo "<tr class='row_graph'>
             <td class='text-center fw-bold bg-primary'>" . utf8_encode($country['Country']) . "</td>
             <td class='text-center fw-bold bg-secondary'>{$sum_confirmed}</td>
             <td class='text-center fw-bold bg-secondary'>{$sum_active}</td>
             <td class='text-center fw-bold bg-danger'>{$sum_deaths}</td>
             <td class='text-center fw-bold bg-secondary'>{$sum_recovered}</td>
             <td class='text-center fw-bold bg-secondary'>{$newCases}</td>
             <td class='text-center fw-bold bg-danger'>{$newDeaths}</td>
             <td class='text-center fw-bold bg-secondary'>{$newRecovered}</td>
             </tr>";
            }
        }
        ?>
    </table>

    </div>
    </div>


    <div class="row ">
        <div class="col-12 pt-2">
            <div class="py-1">
                <h5 class="text-dark  fw-bold text-shadow1 text-center ">Covid Tracker <i
                        class=" px-3 fas fa-viruses"></i>
                </h5>
            </div>
        </div>
    </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-p34f1UUtsS3wqzfto5wAAmdvj+osOnFyQFpp4Ua3gs/ZVWx6oOypYoCJhGGScy+8" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.js"
        integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="sum.js"></script>
    //
    <!-- <script src="syncData.js"></script> -->

</body>

</html>