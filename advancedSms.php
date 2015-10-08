<html>
<head>
    <title>Fully featured textual message</title>
</head>
<body>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="send_form">
    <span style="color:#FF0000"><b>Authentication</b></span><br>
    <label for="in_username">Username:</label><br>
    <input id="in_username" type="text" name="username" title="Username">
    <br>
    <label for="in_password">Password:</label><br>
    <input id="in_password" type="password" name="password" title="Password">
    <hr>
    <br>

    <label for="in_from">From:</label><br>
    <input id="in_from" type="text" name="fromInput" title="From">
    <br>
    <label for="in_to">To*<span style="color:#FF0000">(required)</span>:</label><br>
    <input id="in_to" type="text" name="toInput" title="To">
    <br>
    <label for="in_messageId">Message ID:</label><br>
    <input id="in_messageId" type="text" name="messageIdInput" title="Message ID">
    <br>
    <label for="in_text">Text:</label><br>
    <textarea id="in_text" rows="4" cols="50" name="textInput" form="send_form" title="Message Text"></textarea>
    <br>
    <label for="in_notify_url">Notify URL:</label><br>
    <input id="in_notify_url" type="text" name="notifyUrlInput" title="Notify URL">
    <br>
    <label for="in_notify_contentType">Notify ContentType:</label><br>
    <input id="in_notify_contentType" type="text" name="notifyContentTypeInput"
           title="Notify content type (application/xml, application/json, ...)">
    <br>
    <label for="in_callback_data">Callback Data:</label><br>
    <input id="in_callback_data" type="text" name="callbackData" title="User defined callback data"><br>
    <input type="submit" value="Send">
</form>

<?php
if (isset($_POST['toInput'])) {
    $to = $_POST['toInput'];
    if ($to <> '') {
        $from = $_POST['fromInput'];
        $messageId = $_POST['messageIdInput'];
        $text = $_POST['textInput'];
        $notifyUrl = $_POST['notifyUrlInput'];
        $notifyContentType = $_POST['notifyContentTypeInput'];
        $callbackData = $_POST['callbackData'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        $postUrl = "https://api.infobip.com/sms/1/text/advanced";

        $xmlString = '<request>
					<messages>
						<message>';

        if ($from <> '')
            $xmlString .= '<from>' . $from . '</from>';

        $xmlString .= '<destinations>
					  <destination>
					  <to>' . $to . '</to>';

        if ($messageId <> '')
            $xmlString .= '<messageId>' . $messageId . '</messageId>';

        $xmlString .= '</destination></destinations>';

        if ($text <> '')
            $xmlString .= '<text>' . $text . '</text>';

        if ($notifyUrl <> '')
            $xmlString .= '<notifyUrl>' . $notifyUrl . '</notifyUrl>';

        if ($notifyContentType <> '')
            $xmlString .= '<notifyContentType>' . $notifyContentType . '</notifyContentType>';

        if ($callbackData <> '')
            $xmlString .= '<callbackData>' . $callbackData . '</callbackData>';

        $xmlString .= '</message>
				</messages>
						</request>';

        $ch = curl_init();
        $header = array('Content-Type:application/xml', 'Accept:application/xml');

        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);

        // response of the POST request
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $responseBodyXml = new SimpleXMLElement($response);
        curl_close($ch);

        if ($httpcode >= 200 && $httpcode < 300) {
            $result = $responseBodyXml->messages->message;

            foreach ($result as $message) {
                $sentMessageResponse = array(
                    "message_id" => $message->messageId,
                    "to" => $message->to,
                    "status_groupId" => $message->status->groupId,
                    "status_groupName" => $message->status->groupName,
                    "status_id" => $message->status->id,
                    "status_name" => $message->status->name,
                    "status_description" => $message->status->description,
                    "sms_count" => $message->smsCount
                );
                $arrayOfSentMessageResponses[] = $sentMessageResponse;
            }

            echo '<span style="color:#FF0000"><b>Response:</b></span><br>';
            ?>

            <table cellspacing="0" id="logs_table" border="1">
                <thead>
                <tr class="headings">
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
                foreach ($arrayOfSentMessageResponses as $sentMessageResponse) {
                    echo "<tr>";
                    echo "<td>" . $sentMessageResponse["message_id"] . "</td>";
                    echo "<td>" . $sentMessageResponse["to"] . "</td>";
                    echo "<td>" . $sentMessageResponse["status_groupId"] . "</td>";
                    echo "<td>" . $sentMessageResponse["status_groupName"] . "</td>";
                    echo "<td>" . $sentMessageResponse["status_id"] . "</td>";
                    echo "<td>" . $sentMessageResponse["status_name"] . "</td>";
                    echo "<td>" . $sentMessageResponse["status_description"] . "</td>";
                    echo "<td>" . $sentMessageResponse["sms_count"] . "</td>";
                    echo "</tr>";
                } ?>
                </tbody>
            </table>
            <?php
        } else {
            echo $responseBodyXml->requestError->serviceException->messageId;
            echo '<br>' . $responseBodyXml->requestError->serviceException->text;
        }
    } else echo "You did not enter the destination.";
}
?>
</body>
</html>
