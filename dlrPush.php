<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <title>Delivery reports on Notify URL</title>
</head>
<body>
<div class="container">

    <div class="page-header">
        <h1>Delivery reports on Notify URL</h1>
    </div>
    <p class="lead">Receive a Delivery reports on your callback server's Notify URL.</p>

    <?php

    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    $responseBody = file_get_contents('php://input');
    if ($responseBody) {
        file_put_contents("dlr.txt", $responseBody);
    } else {
        $fileBody = file_get_contents("dlr.txt");
        if ($fileBody <> "") {
            if (isJson($fileBody)) {
                $responseJson = json_decode($fileBody);
                $results = $responseJson->results;
            } else if (strpos(trim($fileBody), '<reportResponse>') === 0) {
                $responseXml = simplexml_load_string($fileBody);
                $results = $fileBody->results->result;
            }
        }
    }

    if (isset($results)) {
        // Using GMT timezone when not specified
        date_default_timezone_set('Europe/London');
        ?>

        <table id="dlr_table" class="table table-condensed">
            <thead>
            <tr class="headings">
                <th>Message ID</th>
                <th>To</th>
                <th>Sent at</th>
                <th>Price per message</th>
                <th>Currency</th>
                <th>Status</th>
                <th>Error</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($results as $message) {
                echo "<tr>";
                echo "<td>" . $message->messageId . "</td>";
                echo "<td>" . $message->to . "</td>";

                $formattedSentAt = date("M d, Y - H:i:s P T", strtotime($message->sentAt));
                echo "<td>" . $formattedSentAt . "</td>";
                echo "<td>" . $message->price->pricePerMessage . "</td>";
                echo "<td>" . $message->price->currency . "</td>";
                echo "<td>" . $message->status->name . "</td>";
                echo "<td>" . $message->error->name . "</td>";
                echo "</tr>";
            } ?>
            </tbody>
        </table>

        <?php
    } else {
        ?>
        <div class="alert alert-info" role="alert">
            No delivery report pushed to callback server.
        </div>
        <?php
    }
    ?>
</div>
</body>
</html>
