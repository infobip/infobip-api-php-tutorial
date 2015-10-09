

# PHP tutorial

This tutorial will guide you through implementing Infobip's SMS services. It includes three examples for some of the main features for sending SMS messages and checking their status:

  - [Fully featured textual message]([fftm])  - [Sent messages logs]#entlogs[]]
  - [Delivery reports on Notify URL][dlrnotify]

Ithishe tutorial, these examples are presented on a start (**index.php**) page, so you can choose which action you want to preform.

To be able to follow this tutorial and also to write and test your own, you need to set the environment. Because we used [cURL] for sending messages, getting logs and receiving delivery reports, you have to enable php extension for it in your web server. For the purpose of this tutorial, you can use some solution from [AMP] solution stack (wamp, xampp, ...). Those are software stacks for the various OSes consisting of Apache web server, MySQL database and PHP programming language support. You should enable **php_curl** extension for the one you choose.

Note that in order to have secure sending of SMS messages, these examples should be hosted on HTTPS (using TLS) when going live because some parameters (like username and password) are sent as HTTP POST parameters for sake of simplicity.

## [Fully featured textual message][fftm]

When you choose this option it opens **advancedSms.php** page with form for sending "Fully featured textual message". Submit button will **POST** those fields to a page specified in **action** attribute of the form. In this example it will post it to itself.

```
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="send_form">
```

### Building the request
Before manipulating values in this form you have to check if they are set. In this example we have only checked for **toInput** field. You do not have to check for all fields, because POST HTTP method will set all, whereas none will be set when it is called for the first loading of page, ie. using GET request. After this check you should define **URL for sending request**, and **body of request** which is going to be sent. Body of request will be an XML or JSON structured string, whose structure depends of input fields in request form described above. Forming the request body will look like the following:

```
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

```

As only required field is **"to"**, you must check if it is empty before sending request. If it is empty, skip all the logic, and notify the user about that. For sending the request we chose [cURL].

```
    ch = curl_init();
    $header = array('Content-Type:application/xml', 'Accept:application/xml');

    curl_setopt($ch, CURLOPT_URL, $postUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER , $header);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD , $username . ":" . $password);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,2);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS,2);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlString);

    // response of the POST request
    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseBodyXml = new SimpleXMLElement($response);
    curl_close($ch);
```

In quoted code we used many options for setting request, which is initialized with **curl_init()**:

- *CURLOPT_URL* - setting of URL with method endpoint in it
- *CURLOPT_HTTPHEADER* - Content-Type and Accept headers of request
- *CURLOPT_HTTPAUTH, CURLOPT_USERPWD* - Authentication type, username and password
- *CURLOPT_POST* - used HTTP method - POST
- *CURLOPT_POSTFIELDS* - XML or JSON structured string previously built
- other [cURL] options for sending

After all options are set, you execute the request with `curl_exec($ch)`. This method returns response which you can present as XML, suitable for future parsing of response. After it's execution, information about HTTP response code is also available and will be used later - `curl_getinfo($ch, CURLINFO_HTTP_CODE)`.

### Parsing the response
If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from response body and present it to the user. We chose: *Message ID, To, SMS Count, Status Group, Group Name, ID, Name and Description*, but you can choose whatever you want:

```
    if ($httpcode >=200 && $httpcode<300) {
        $result= $responseBodyXml -> messages -> message;
        
        foreach ($result as $message) {
            $sentMessageResponse = array(
                "message_id" => $message -> messageId,
                "to" => $message -> to,
                "status_groupId" => $message -> status -> groupId,
                "status_groupName" => $message -> status -> groupName,
                "status_id" => $message -> status -> id,
                "status_name" => $message -> status -> name,
                "status_description" => $message -> status -> description,
                "sms_count" => $message -> smsCount
            );
            $arrayOfSentMessageResponses[] = $sentMessageResponse;
        }
```

Next, *foreach loop* will iterate through array of sent message responses and write a single row with appropriate columns in them. **NOTE:** In this example, you can send only one message to one destination, so array of sent message responses is not necessary, but is mandatory if you implement a form for sending a message to multiple destinations. At the end, you can parse the body of request if the exception occurred:

```
} else {
	echo $responseBodyXml->requestError->serviceException->messageId;
	echo '<br>' . $responseBodyXml->requestError->serviceException->text;
}
```

## [Sent messages logs][sentlogs]

When you choose this option it opens **logsRequestForm.php** page with input form for getting "Sent messages logs". Similar to sending SMS messages, this form forwards the input field values to another page (this time only user credentials are passed via **POST** parameters). In this case, it is another page which does all the logic for sending request, receiving response, parsing it and showing to the user.

```
<form action="logs.php" method="POST">
```

### Building the request

As quoted in the code above, **logs.php** is the page which catches the posted data. You should define **URL for sending request** with sent message logs endpoint, and send request to it with **GET** HTTP method this time (CURLOPT_HTTPGET option set to TRUE):

```
$getUrl = 'https://api.infobip.com/sms/1/logs?limit=42';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $getUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER , array('Accept: application/xml'));
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD , $username. ":" . $password);
curl_setopt($curl, CURLOPT_HTTPGET , TRUE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

$response = curl_exec($curl);
$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$responseBodyXml = new SimpleXMLElement($response);

curl_close($curl);
```

After execution of request - **culr_exec($curl)**, and getting the HTTP response code, we started parsing the body depending of the response code value, similar we did in "Fully featured textual message" chapter.

### Parsing the response
If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from response body and present it to user. This time we chose: *Message ID, To, From, Text, Status Group Name and Description*, but you can choose whatever you want like before:

```
if ($httpcode >=200 && $httpcode<300) {
		$result= $responseBodyXml -> results -> result;
        foreach ($result as $message) {
            $formatedSentAt = date("M d, Y - H:i:s P T", strtotime($message -> sentAt));
            $sentMessageLog = array(
                "message_id" => $message -> messageId,
                "to" => $message -> to,
                "from" => $message -> from,
                "text" => $message -> text,
                "sent_at" => $formatedSentAt,
                "general_status" => $message -> status -> groupName,
                "status_description" => $message -> status -> description
            );
            $arrayOfMessageLogs[] = $sentMessageLog;
        }
```

Next *foreach loop* will iterate through array of sent message logs and write a single row with appropriate columns in them. In this case, foreach loop is necessary because we asked to retrieve all message logs. At the end, you can parse the body of request if the exception occurred, like we did for sending message method:

```
} else {
	echo $responseBodyXml->requestError->serviceException->messageId;
	echo '<br>' . $responseBodyXml->requestError->serviceException->text;
}
```

## [Delivery reports on Notify URL][dlrnotify]

This feature is slightly different to previous two - page **dlrPush.php** is not used for requesting some information, it is waiting for it, and when it arrives, information about delivery reports have to be parsed and showed to the user in appropriate way.

**NOTE:** The delivery reports are pushed from the "Fully featured textual message" page by entering **this page URL** in **Notify URL** field. Also, **Notify ContentType** field in that page defines which type of body is about to arrive.

### Receiving pushed delivery report

```
$responseBody = file_get_contents('php://input');

if (strpos(trim($responseBody), '<reportResponse>') === 0) {
$xmlData = simplexml_load_string($responseBody);
$result = $xmlData->results->result;
} elseif ($responseBody <> '' && isJson($responseBody)){
	$jsonObject= json_decode($responseBody);
	$result = $jsonObject->results;
}
```

Above code shows that **file_get_contents('php://input')** method is used for getting raw POST data as a string. Later lines shows hot to inspect if the data can be parsed as XML or JSON, and if so, extract the **results** containing pushed delivery reports. For XML we inspect if string starting with **<reportResponse>**, and if not, try to decode it without errors - **isJson()** function. If all conditions are FALSE, **$result** variable stays unset which means we should say to user that "No delivery report pushed to callback server".

### Parsing the result
Parsing of pushed delivery reports is very similar to parsing the response of **Fully featured textual message** and **Sent message logs** methods except we do not check for the HTTP response code (because there are no response at all). All we have to do is to choose which information from arrived delivery reports we want to show, pack it into an array and write to the table like we did before.

[//]: #

   [fftm]: <http://dev.infobip.com/docs/fully-featured-textual-message>
   [sentlogs]: <http://dev.infobip.com/docs/message-logs>
   [dlrnotify]: <http://dev.infobip.com/docs/notify-url>
   [cURL]: <http://php.net/manual/en/function.curl-setopt.php>
   [AMP]: <https://en.wikipedia.org/wiki/List_of_Apache%E2%80%93MySQL%E2%80%93PHP_packages>
