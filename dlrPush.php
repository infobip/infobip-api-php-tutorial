<html>
<head>
    <title>Delivery reports on Notify URL</title>
</head>
<body>

<?php
function isJson($string)
{
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}

$responseBody = file_get_contents('php://input');

if (strpos(trim($responseBody), '<reportResponse>') === 0) {
    $xmlData = simplexml_load_string($responseBody);
    $result = $xmlData->results->result;
} elseif ($responseBody <> '' && isJson($responseBody)) {
    $jsonObject = json_decode($responseBody);
    $result = $jsonObject->results;
}

if (isset($result)) {
    foreach ($result as $message) {
        $formatedSentAt = date("M d, Y - H:i:s P T", strtotime($message->sentAt));

        $deliveryReport = array(
            "message_id" => $message->messageId,
            "to" => $message->to,
            "err_gname" => $message->error->groupName,
            "err_desc" => $message->error->description,
            "sent_at" => $formatedSentAt,
            "general_status" => $message->status->groupName,
            "status_description" => $message->status->description
        );
        $arrayOfDeliveryReport[] = $deliveryReport;
    }

    ?>
    <table cellspacing="0" id="dlr_table" border="1">
        <thead>
        <tr class="headings">
            <th>Message ID</th>
            <th>To</th>
            <th>Error Group Name</th>
            <th>Error Description</th>
            <th>Sent At</th>
            <th>General Status</th>
            <th>Status Description</th>
        </tr>

        </thead>

        <tbody>


        <?php

        foreach ($arrayOfDeliveryReport as $pushedDlr) {
            ?>
            <tr>
                <td>
                    <?php echo $pushedDlr["message_id"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["to"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["err_gname"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["err_desc"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["sent_at"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["general_status"]; ?>
                </td>
                <td>
                    <?php echo $pushedDlr["status_description"]; ?>
                </td>
            </tr>
            <?php

        } ?>

        </tbody>

    </table>

    <?php
} else {
    echo '<span style="color:#FF0000"><b>No delivery report pushed to callback server.</b></span>';
}
?>

</body>
</html>
