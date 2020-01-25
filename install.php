<?php

include "classes/Database.class.php";

function validateString($string): bool
{
    return (!empty($string) && !is_null($string) && is_string($string));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $checkedServers = array();

    foreach ($data["servers"] as &$row) {
        if (isset($row["adress"]) && isset($row["name"])) {
            if (validateString($row["adress"]) && validateString($row["name"])) {
                $checkedServers[] = array(
                    "adress" => $row["adress"],
                    "name" => $row["name"]
                );
            }
        }
    }

    $dbCredentials = $data["db"];

    try {
        $db = new Database($dbCredentials["username"], $dbCredentials["password"], $dbCredentials["dbname"]);
    } catch (PDOException $e) {
        echo json_encode("DBconError");
        exit();
    }

    try {
        $queries = include_once "config/queries.config.php";

        foreach ($queries as &$query) {
            $db->query($query)->execute();
        }
    } catch (PDOException $e) {
        echo json_encode("DBcreateError");
        exit();
    }

    try {
        foreach ($checkedServers as &$checkedServer) {
            $db->query("INSERT INTO `servers` (`server`, `adress`, `name`, `added`) VALUES (?, ?, ?, NOW())")
                ->bind(1, uniqid())
                ->bind(2, $checkedServer["adress"])
                ->bind(3, $checkedServer["name"])
                ->execute();
        }
    } catch (PDOException $e) {
        echo json_encode("DBinsertError");
        exit();
    }

    $databaseConfig = fopen("config/database.config.php", "w+");
    fwrite($databaseConfig, "<?php
return [
    \"user\" => \"{$dbCredentials["username"]}\",
    \"pass\" => \"{$dbCredentials["password"]}\",
    \"dbname\" => \"{$dbCredentials["dbname"]}\"
];");


    require_once "classes/MinecraftPing.class.php";
    require_once "classes/StatsCollector.class.php";

    $collector = new StatsCollector($db);
    $stats = $collector->saveAllHourlyStats();

    echo json_encode($stats);
    exit();
}
?>
<html>
<head>
    <title>MC Stats Install</title>
    <style>
        body {
            background-color: grey;
        }

        .container {
            position: absolute;
            top: 50%;
            left: 50%;
            -moz-transform: translateX(-50%) translateY(-50%);
            -webkit-transform: translateX(-50%) translateY(-50%);
            transform: translateX(-50%) translateY(-50%);
            background-color: white;
            border: 1px solid black;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            width: 50%;
            padding: 15px;
        }

        h1 {
            text-align: center;
            font-family: Tahoma, Geneva, sans-serif;
        }
    </style>
    <script>
        function addRow() {
            const table = document.getElementById("input");

            const row = table.insertRow();

            const cell1 = row.insertCell(0);
            cell1.setAttribute("contenteditable", "true");
            const cell2 = row.insertCell(1);
            cell2.setAttribute("contenteditable", "true");
        }

        function tableToArray() {
            const table = document.getElementById("input");
            let servers = [];
            for (let i = 1; i < table.rows.length; i++) {
                let row = table.rows[i];
                servers[i - 1] = {};
                for (let j = 0; j < row.cells.length; j++) {
                    let col = row.cells[j];
                    servers[i - 1][table.rows[0].cells[j].innerHTML.toLowerCase()] = col.innerHTML;
                }
            }
            return servers;
        }

        function dbData() {
            let database = {};
            database["username"] = document.getElementById("db-username").value;
            database["password"] = document.getElementById("db-password").value;
            database["dbname"] = document.getElementById("db-name").value;
            return database;
        }

        function packData() {
            let data = {};
            data["db"] = dbData();
            data["servers"] = tableToArray();
            return JSON.stringify(data);
        }

        function sendData() {
            (async () => {
                const rawResponse = await fetch('install.php', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: packData()
                });
                const content = await rawResponse.json();

                if (content == "DBconError") {
                    alert("Unable to connect to Database!");
                } else if (content == "DBcreateError") {
                    alert("Unable to create new Table!");
                } else if (content == "DBinsertError") {
                    alert("Unable to insert to Database!");
                } else {
                    document.getElementById("container").innerHTML = "<h1 style='color: green'>Setup successful!</h1><h2>" +
                        (content.done *1 + content.failed *1) + " clans were created<br>" +
                        "The server was able to query data from " + content.done + " Servers!<br>" +
                        "It failed to query data from " + content.failed + " Servers!</h2>";
                }
            })();
        }
    </script>
</head>
<body>
<div class="container" id="container">
    <h1>MC-Stats one time setup</h1>
    <h2>Setup</h2>
    <p>
        Setup a cronjob to call
        <code><?= $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/cron/query.php" ?></code>
        once every hour.
        Your hoster might not support cronjobs but no worries, you can use an external cronjob. I prefer
        <a href="https://cron-job.org/en/" target="_blank">cron-job.org</a> but there certainly are alternatives
        out there.
    </p>
    <h2>Database</h2>
    <p>
        Please enter the credentials for a MySQL database.<br><br>
        Username: <input id="db-username" type="text" value="">
        Password: <input id="db-password" type="password" value="">
        DBname: <input id="db-name" type="text" value="">
    </p>
    <h2>Servers</h2>
    <p>
        You can add the list of initial servers here. Please note that it's currently not possible to add servers later
        on.
    </p>
    <table border="1" style="width: 100%" id="input">
        <thead style="width: 100%">
        <tr>
            <th style="width: 50%">Name</th>
            <th style="width: 50%">Adress</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td contenteditable="true"></td>
            <td contenteditable="true"></td>
        </tr>
        </tbody>
    </table>
    <p style="text-align: center">
        <button onclick="addRow()">Add row</button>
        <br><br>
        <span style="color: red">This script will delete itself after pressing send and won't be accessible again.</span>
        <br><br>
        <button onclick="sendData()">Send</button>
    </p>
</div>
</body>
</html>
