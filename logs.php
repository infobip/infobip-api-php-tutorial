<html>
<head>
    <title>Sent messages logs</title>
</head>
<body>

<?php
if (isset($_POST['username'])) {
$username = $_POST['username'];
$password = $_POST['password'];

$getUrl = 'https://api.infobip.com/sms/1/logs?limit=5763';
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $getUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept:application/xml'));
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$responseBodyXml = new SimpleXMLElement($response);

curl_close($curl);

if ($httpcode >= 200 && $httpcode < 300) {

$result = $responseBodyXml->results->result;
foreach ($result as $message) {
    $formatedSentAt = date("M d, Y - H:i:s P T", strtotime($message->sentAt));

    $sentMessageLog = array(
        "message_id" => $message->messageId,
        "to" => $message->to,
        "from" => $message->from,
        "text" => $message->text,
        "sent_at" => $formatedSentAt,
        "general_status" => $message->status->groupName,
        "status_description" => $message->status->description
    );
    $arrayOfMessageLogs[] = $sentMessageLog;
}
?>

<table cellspacing="0" id="logs_table" border="1">
    <thead>
    <tr class="headings">
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

    foreach ($arrayOfMessageLogs as $sentMessageLog) {
        ?>
        <tr>
            <td>
                <?php echo $sentMessageLog["message_id"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["to"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["from"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["text"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["general_status"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["status_description"]; ?>
            </td>
            <td>
                <?php echo $sentMessageLog["sent_at"]; ?>
            </td>
        </tr>
        <?php
    }
    } else {
        echo $responseBodyXml->requestError->serviceException->messageId;
        echo '<br>' . $responseBodyXml->requestError->serviceException->text;
    }
    }
    ?>

    </tbody>
</table>
</body>
</html>
