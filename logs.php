<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <title>Sent messages logs</title>
</head>
<body>
<div class="container">

    <div class="page-header">
        <h1>Sent messages logs</h1>
    </div>
    <p class="lead">Get logs for sent SMS.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="form-horizontal">
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Authentication</h4></div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="in_username" class="col-sm-2 control-label">Username</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_username" placeholder="Username" name="username">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_password" class="col-sm-2 control-label">Password</label>

                    <div class="col-sm-10">
                        <input type="password" class="form-control" id="in_password" placeholder="Password"
                               name="password">
                    </div>
                </div>
            </div>
        </div>
        <input class="btn btn-default btn-lg" type="submit" value="Get logs">
    </form>
    <hr>

    <?php
    // Using GMT timezone when not specified
    date_default_timezone_set('Europe/London');

    if (isset($_POST['username'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $getUrl = 'https://api.infobip.com/sms/1/logs?limit=20';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $getUrl);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $responseBody = json_decode($response);
        curl_close($curl);

        if ($httpCode >= 200 && $httpCode < 300) {
            $logs = $responseBody->results;
            echo '<h4>Response</h4><br>';
            ?>

            <table id="logs_table" class="table table-condensed">
                <thead>
                <tr>
                    <th>Message ID</th>
                    <th>To</th>
                    <th>From</th>
                    <th>Text</th>
                    <th>General Status</th>
                    <th>Status Description</th>
                    <th>Sent At</th>
                </tr>
                </thead>
                <tbody>
                <?php

                foreach ($logs as $log) {
                    echo "<tr>";
                    echo "<td>" . $log->messageId . "</td>";
                    echo "<td>" . $log->to . "</td>";
                    echo "<td>" . $log->from . "</td>";
                    echo "<td>" . $log->text . "</td>";
                    echo "<td>" . $log->status->groupName . "</td>";
                    echo "<td>" . $log->status->description . "</td>";

                    $formattedSentAt = date("M d, Y - H:i:s P T", strtotime($log->sentAt));
                    echo "<td>" . $formattedSentAt . "</td>";
                    echo "</tr>";
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            ?>
            <div class="alert alert-danger" role="alert">
                <b>An error occurred!</b> Reason:
                <?php
                echo $responseBody->requestError->serviceException->text;
                ?>
            </div>
            <?php
        }
    }
    ?>
</div>
</body>
</html>
