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
                <textarea class="form-control" id="in_text" placeholder="Message text" name="textInput"
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

    require_once __DIR__ . '/vendor/autoload.php';

    use infobip\api\client\SendMultipleTextualSmsAdvanced;
    use infobip\api\configuration\BasicAuthConfiguration;
    use infobip\api\model\Destination;
    use infobip\api\model\sms\mt\send\Message;
    use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;

    if (isset($_POST['toInput'])) {
        // Create configuration object that will tell the client how to authenticate API requests
        // Additionally, note the use of http protocol in base path.
        // That is for tutorial purposes only and should not be done in production.
        // For production you can leave the baseUrl out and rely on the https based default value.
        $configuration = new BasicAuthConfiguration($_POST['username'], $_POST['password'], 'http://api.infobip.com/');
        // Create a client for sending sms texts by passing it the configuration object
        $client = new SendMultipleTextualSmsAdvanced($configuration);

        // Destination holds recipient's phone number along with id used to uniquely identify the message later on
        $destination = new Destination();
        $destination->setTo($_POST['toInput']);
        $destination->setMessageId($_POST['messageIdInput']);

        // Message has text and the sender of the sms along with other metadata useful for tracking delivery
        $message = new Message();
        // One message can be sent to multiple destinations, that is why it takes an array of Destination objects
        // In this example we send sms only to a single phone number so an array with only one destination is set
        $message->setDestinations([$destination]);
        $message->setFrom($_POST['fromInput']);
        $message->setText($_POST['textInput']);
        $message->setNotifyUrl($_POST['notifyUrlInput']);
        $message->setNotifyContentType($_POST['notifyContentTypeInput']);
        $message->setCallbackData($_POST['callbackDataInput']);

        // SMSAdvancedTextualRequest model is sent to the API client
        $requestBody = new SMSAdvancedTextualRequest();
        // One request can have multiple different text messages, in this example we only send one
        $requestBody->setMessages([$message]);

        try {
            // Executing request
            $apiResponse = $client->execute($requestBody);
            ?>
            <h4>Response</h4><br>
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
                    $messages = $apiResponse->getMessages();
                    foreach ($messages as $message) {
                        echo "<tr>";
                        echo "<td>" . $message->getMessageId() . "</td>";
                        echo "<td>" . $message->getTo() . "</td>";
                        echo "<td>" . $message->getStatus()->getGroupId() . "</td>";
                        echo "<td>" . $message->getStatus()->getGroupName() . "</td>";
                        echo "<td>" . $message->getStatus()->getId() . "</td>";
                        echo "<td>" . $message->getStatus()->getName() . "</td>";
                        echo "<td>" . $message->getStatus()->getDescription() . "</td>";
                        echo "<td>" . $message->getSmsCount() . "</td>";
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <?php
        } catch (Exception $apiCallException) {
            // Handling errors in request execution
            ?>
            <div class="alert alert-danger" role="alert">
                <b>An error occurred!</b> Reason:
                <?php
                $errorMessage = $apiCallException->getMessage();
                $errorResponse = json_decode($apiCallException->getMessage());
                if (json_last_error() == JSON_ERROR_NONE) {
                    $errorReason = $errorResponse->requestError->serviceException->text;
                } else {
                    $errorReason = $errorMessage;
                }
                echo $errorReason;
                ?>
            </div>
            <?php
        }
    }
    ?>
</div>
</body>
</html>
