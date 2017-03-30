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
	    <div class="panel panel-default">
		    <div class="panel-heading"><h4>Filter logs</h4></div>
		    <div class="panel-body">
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
		    </div>
	    </div>
        <input class="btn btn-default btn-lg" type="submit" value="Get logs">
    </form>
    <hr>

    <?php

    require_once __DIR__ . '/vendor/autoload.php';

    use infobip\api\client\GetSentSmsLogs;
    use infobip\api\configuration\BasicAuthConfiguration;
    use infobip\api\model\sms\mt\logs\GetSentSmsLogsExecuteContext;

    // Using GMT timezone when not specified
    date_default_timezone_set('Europe/London');

    if (isset($_POST['username'])) {
        // Create configuration object that will tell the client how to authenticate API requests
	    // Additionally, note the use of http protocol in base path.
	    // That is for tutorial purposes only and should not be done in production.
	    // For production you can leave the baseUrl out and rely on the https based default value.
        $configuration = new BasicAuthConfiguration($_POST['username'], $_POST['password'], 'http://api.infobip.com/');
        // Create a client for getting sms logs by passing it the configuration object
        $client = new GetSentSmsLogs($configuration);

        // Creating execution context that will be used to filter the logs
        $context = new GetSentSmsLogsExecuteContext();
        $context->setMessageId($_POST['messageIdInput']);
        $context->setTo($_POST['toInput']);
        // Configuring context to fetch at most 20 logs (there other properties available)
        $context->setLimit(20);

	    try {
            // Executing request
            $apiResponse = $client->execute($context);
            ?>
		    <h4>Response</h4><br>
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
                    $logs = $apiResponse->getResults();
                    foreach ($logs as $log) {
                        echo "<tr>";
                        echo "<td>" . $log->getMessageId() . "</td>";
                        echo "<td>" . $log->getTo() . "</td>";
                        echo "<td>" . $log->getFrom() . "</td>";
                        echo "<td>" . $log->getText() . "</td>";
                        echo "<td>" . $log->getStatus()->getGroupName() . "</td>";
                        echo "<td>" . $log->getStatus()->getDescription() . "</td>";
                        // Note that $log->getSentAt() will return object of DateTime type that we can directly
	                    // format into a string representation
                        $formattedSentAt = $log->getSentAt()->format("M d, Y - H:i:s P T");
                        echo "<td>" . $formattedSentAt . "</td>";
                        echo "</tr>";
                    } ?>
			    </tbody>
		    </table>
            <?php
        } catch (Exception $apiCallException) {
	    	// Handling error request execution
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
