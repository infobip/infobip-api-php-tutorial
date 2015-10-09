<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <title>Fully featured textual message</title>
</head>
<body>
<div class="container">

    <div class="page-header">
        <h1>Fully featured textual message</h1>
    </div>
    <p class="lead">Send advanced SMS with the all available features and parameters.</p>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="send_form"
          class="form-horizontal">
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

        <div class="panel panel-default">
            <div class="panel-heading"><h4>Send message</h4></div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="in_from" class="col-sm-2 control-label">Sender</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_from" placeholder="Sender (Can be alphanumeric)"
                               name="fromInput">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_to" class="col-sm-2 control-label">Phone number</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_to"
                               placeholder="Phone number in international format (example: 41793026727)" name="toInput">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_messageId" class="col-sm-2 control-label">Message ID</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_messageId" placeholder="Message ID"
                               name="messageIdInput">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_text" class="col-sm-2 control-label">Message text</label>

                    <div class="col-sm-10">
                <textarea type="text" class="form-control" id="in_text" placeholder="Message text" name="textInput"
                          rows="4"></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_notify_url" class="col-sm-2 control-label">Notify URL</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_notify_url" placeholder="Notify URL"
                               name="notifyUrlInput">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_notify_contentType" class="col-sm-2 control-label">Notify content type</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_notify_contentType"
                               placeholder="Notify content type (application/json, application/xml)"
                               name="notifyContentTypeInput">
                    </div>
                </div>
                <div class="form-group">
                    <label for="in_callback_data" class="col-sm-2 control-label">Callback data</label>

                    <div class="col-sm-10">
                        <input type="text" class="form-control" id="in_callback_data" placeholder="Callback data"
                               name="callbackDataInput">
                    </div>
                </div>
            </div>
        </div>

        <input class="btn btn-default btn-lg" type="submit" value="Send">
    </form>
    <hr>

    <?php
    if (isset($_POST['toInput'])) {
        $to = $_POST['toInput'];
        if ($to <> '') {
            $from = $_POST['fromInput'];
            $messageId = $_POST['messageIdInput'];
            $text = $_POST['textInput'];
            $notifyUrl = $_POST['notifyUrlInput'];
            $notifyContentType = $_POST['notifyContentTypeInput'];
            $callbackData = $_POST['callbackDataInput'];
            $username = $_POST['username'];
            $password = $_POST['password'];

            $postUrl = "https://api.infobip.com/sms/1/text/advanced";

            // creating an object for sending SMS
            $destination = array("messageId" => $messageId,
                "to" => $to);

            $message = array("from" => $from,
                "destinations" => array($destination),
                "text" => $text,
                "notifyUrl" => $notifyUrl,
                "notifyContentType" => $notifyContentType,
                "callbackData" => $callbackData);

            $postData = array("messages" => array($message));
            $postDataJson = json_encode($postData);

            $ch = curl_init();
            $header = array("Content-Type:application/json", "Accept:application/json");

            curl_setopt($ch, CURLOPT_URL, $postUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postDataJson);

            // response of the POST request
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseBody = json_decode($response);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                $messages = $responseBody->messages;
                echo '<h4>Response</h4><br>';
                ?>
                <div>
                    <table id="logs_table" class="table table-condensed">
                        <thead>
                        <tr>
                            <th>Message ID</th>
                            <th>To</th>
                            <th>Status Group ID</th>
                            <th>Status Group Name</th>
                            <th>Status ID</th>
                            <th>Status Name</th>
                            <th>Status Description</th>
                            <th>SMS Count</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($messages as $message) {
                            echo "<tr>";
                            echo "<td>" . $message->messageId . "</td>";
                            echo "<td>" . $message->to . "</td>";
                            echo "<td>" . $message->status->groupId . "</td>";
                            echo "<td>" . $message->status->groupName . "</td>";
                            echo "<td>" . $message->status->id . "</td>";
                            echo "<td>" . $message->status->name . "</td>";
                            echo "<td>" . $message->status->description . "</td>";
                            echo "<td>" . $message->smsCount . "</td>";
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
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
        } else {
            ?>
            <div class="alert alert-danger" role="alert">
                <b>An error occurred!</b> Reason: Phone number is missing
            </div>
            <?php
        }
    }
    ?>
</div>
</body>
</html>
