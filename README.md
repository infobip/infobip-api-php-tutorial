# Infobip API PHP tutorial

This tutorial will guide you through implementing Infobip's SMS services. It includes three examples for some of the main features for sending SMS messages and checking their status:

  - [Fully featured textual message](#fully-featured-textual-message)
  - [Sent messages logs](#sent-message-logs)
  - [Delivery reports on Notify URL](#delivery-reports-on-notify-url)

In the tutorial, these examples are presented on a start ([index.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/index.php)) page, so you can choose which action you want to perform.

To be able to follow this tutorial and also to write and test on your own, you need to set the environment. In order to send messages, get logs and receive delivery reports, you have to enable [cURL] php extension in your web server.

For the purpose of this tutorial, you can use some solution from [AMP] solution stack (wamp, xampp, ...). Those are software stacks for the various OSes consisting of Apache web server, MySQL database and PHP programming language support. You should enable **php_curl** extension for the one you choose.

>**Note:** In order to have secure sending of SMS messages, these examples should be hosted on HTTPS (using TLS) when going live because some parameters (like username and password) are sent as HTTP POST parameters for sake of simplicity.

## Fully featured textual message

When you choose this option it opens [advancedSms.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/advancedSms.php) page with form for [sending fully featured textual message][fftm]. Submit button will **POST** those fields to a page specified in **action** attribute of the form. In this example it will post it to itself.

```
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" ... >
```

### Building the request

Before manipulating values in this form you have to check if they are set. In this example we have only checked **toInput** field. You do not have to check all fields, because POST HTTP method will set all, whereas none will be set when it is called for the first loading of page, i.e. using GET request. After this check you should define **URL for sending request**, and **body of request** which is going to be sent. Body of request will be JSON or XML structured string, whose structure depends of input fields in request form described above. Forming the request body will look like the following:

```
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
```

As only required field is **"to"**, you must check if it is empty before sending request. If it is empty, you should skip all the logic, and notify the user about missing field. 

For sending the request we chose [cURL].

```
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
```

In quoted code we used many options for setting request, which is initialized with **curl_init()**:

- *CURLOPT_URL* - setting of URL with method endpoint in it,
- *CURLOPT_HTTPHEADER* - Content-Type and Accept headers of request,
- *CURLOPT_HTTPAUTH, CURLOPT_USERPWD* - Authentication type, username and password,
- *CURLOPT_POST* - used HTTP method - POST,
- *CURLOPT_POSTFIELDS* - XML or JSON structured string previously built,
- other [cURL] options for sending.

After all options are set, you have to execute the request with `curl_exec($ch)`. This method returns response which you can present as JSON, suitable for future parsing of response. After its execution, information about HTTP response code is also available and will be used later in this tutorial - `curl_getinfo($ch, CURLINFO_HTTP_CODE)`.

### Parsing the response

If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from response body and present it to the user. We chose to present: *Message ID, To, SMS Count, Status Group, Status Group Name, Status ID, Status Name and Status Description*, but you can choose whatever you want.

The *foreach loop* shown below will iterate through the array of sent message responses and write a single row for each sent message:

```
if ($httpCode >= 200 && $httpCode < 300) {
		$messages = $responseBody->messages;        
		
		...
		
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
}
```

>**Note:** In this example, only one message to one destination will be sent, so the sent message responses array will contain one element and there is no need for the loop. Anyway, if you decide to send a message to more than one destination, you should iterate through the array of responses.

If the exception occurs, this is how you can present the message to the user:

```
<div class="alert alert-danger" role="alert">
	<b>An error occurred!</b> Reason:
    <?php
    echo $responseBody->requestError->serviceException->text;
    ?>
</div>
```

## Sent messages logs

When you choose this option it opens [logs.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/logs.php) page with input form for [getting sent messages logs][sentlogs]. Submit button will **POST** those fields to itself.

### Building the request

As quoted in the code above, **logs.php** is the page which catches the posted data. You should define **URL for sending request** with sent message logs endpoint, and send request to it with **GET** HTTP method this time (CURLOPT_HTTPGET option set to TRUE):

```
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
```

After execution of request - **culr_exec($curl)**, and getting the HTTP response code, we started parsing the body depending of the response code value, similar we did in [fully featured textual message](#fully-featured-textual-message) chapter.

### Parsing the response

If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from response body and present it to user. This time we chose: *Message ID, To, From, Text, Status Group Name and Description*, but you can choose whatever you want like before:

```
if ($httpCode >= 200 && $httpCode < 300) {
	$logs = $responseBody->results;

	...

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
    }
}
```

Next *foreach loop* will iterate through array of sent message logs and write a single row with appropriate columns in them. In this case, foreach loop is necessary because we asked to retrieve all message logs. 

At the end, you can parse the body of request if the exception occurred, like we did for sending message method:

```
<div class="alert alert-danger" role="alert">
	<b>An error occurred!</b> Reason:
    <?php
	    echo $responseBody->requestError->serviceException->text;
    ?>
</div>
```

## [Delivery reports on Notify URL][dlrnotify]

This feature is slightly different to previous two - the page [dlrPush.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/dlrPush.php) is not used for requesting some data, it is waiting for it. When the data is pushed to this page, it can be parsed and showed to the user in appropriate way.

>**Note:** The delivery reports are pushed from the fully featured textual message page by entering *this page URL* in *Notify URL* field. Also, *Notify ContentType* field in that page defines which type of body is about to arrive.

### Receiving pushed delivery report

```
$responseBody = file_get_contents('php://input');

if ($responseBody <> "") {
	if (isJson($responseBody)) {
	    $responseJson = json_decode($responseBody);
        $results = $responseJson->results;
    } else if (strpos(trim($responseBody), '<reportResponse>') == 0) {
        $responseXml = simplexml_load_string($responseBody);
        $results = $responseBody->results->result;
    }
}
```

Above code shows that `file_get_contents('php://input')` method is used for getting raw POST data as a string. Later lines show how to inspect whether the data can be parsed as XML or JSON, and extract the pushed delivery reports. 

For XML we inspect if string starting with **<reportResponse>**, and if not, try to decode it without errors - **isJson()** function. If all conditions are FALSE, **$result** variable stays unset which means we should say to user that "No delivery report pushed to callback server".

### Parsing the result

Parsing of pushed delivery reports is very similar to parsing the response of [fully featured textual message](#fully-featured-textual-message) and [sent message logs](#sentlogs) methods, except we do not check the HTTP response code (because there is no response at all). All we have to do is to choose which information from pushed delivery reports we want to show, and write it to the page.

[//]: #

   [fftm]: <http://dev.infobip.com/docs/fully-featured-textual-message>
   [sentlogs]: <http://dev.infobip.com/docs/message-logs>
   [dlrnotify]: <http://dev.infobip.com/docs/notify-url>
   [cURL]: <http://php.net/manual/en/function.curl-setopt.php>
   [AMP]: <https://en.wikipedia.org/wiki/List_of_Apache%E2%80%93MySQL%E2%80%93PHP_packages>