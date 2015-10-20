# Infobip API PHP tutorial

Welcome to Infobip beginners’ tutorial for creating your own SMS web app. We will guide you one step at a time through implementation of Infobip SMS API and SMS services. Tutorial includes three examples for some of the main features for sending SMS messages and checking their status:

  - [Fully featured textual message](#a-fully-featured-textual-message)
  - [Sent messages logs](#sent-messages-logs)
  - [Delivery reports on Notify URL](#delivery-reports-on-notify-url)

We will [start](https://github.com/infobip/infobip-api-php-tutorial/blob/master/index.php) with examples and presentations so you can choose which action you want to perform.
To be able to follow this tutorial, to write and test on your own, you need to set the environment (and we don’t mean to set lights and make some coffee). In order to send messages, get logs, and receive your delivery reports, you have to enable [cURL] php extension in your web server.For the purpose of this tutorial, you can use some solution from [AMP] solution stack (wamp, xampp, ...). Those are software stacks for the various OSes consisting of Apache web server, MySQL database and PHP programming language support. You should enable **phpcurl** extension for the one you choose.> **Note:** In order to have a secure sending of SMS messages, these examples should be hosted on HTTPS (using TLS) when going live. Just for sake of simplicity in this tutorial, we have used plain HTTP.

## A fully featured textual message

The fully featured textual message page ([advancedSms.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/advancedSms.php)) contains the form for sending a message. Submit button will send the request to a page specified in `action` attribute of the form. In this example it will post it to itself.


```
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" ... >
```

### Building the request

Before you start to manipulate values, you have to check if you set them in the right way. In this example we have only checked **toInput** field. You don’t have to check them all because POST HTTP method will set everything automatically (if any of the input fields is empty, its value will be an empty string). If you’re loading the page for the first time keep in mind that none of these fields will be present. We are just two steps away from setting everything. After checking of fields you need to define the **URL for sending request**, and the **body of the request** that is going to be sent. 

The POST request body will be presented as JSON string. Forming the request body will look like:

```
// URL for sending request
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
// encoding object
$postDataJson = json_encode($postData);
```

In the example the SMS is sent to one destination. If you want to send this message to multiple destinations, all you have to do is to create another *destination* object and add it to the *destinations* array.
As **to** is the only required field, you must check if it is empty before sending a request, if so skip all the logic, and notify your user about the missing field.```	if (isset($_POST['toInput'])) { 		// all the logic goes here 	}
```

For sending the request we chose [cURL].


```
$ch = curl_init();
$header = array("Content-Type:application/json", "Accept:application/json");
// setting options
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
- [Here][cURL] you can se other [cURL] options for sending.

When you set all options you need to execute the request with `curl_exec($ch)`. This method will provide you with response that you can present as JSON and is suitable for future parsing. After you execute the request, information about HTTP response code will be available and we will use it later in this tutorial -`curl_getinfo($ch, CURLINFO_HTTP_CODE)`.

### Parsing the response

If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from the response body and present it to your user. In our example we chose to present: *Message ID*, *To*, *SMS Count*, *Status Group*, *Status Group Name*, *Status ID*, *Status Name* and *Status Description*, but you can choose which ever you want.The foreach loop shown below will iterate through the array of sent message responses and write a single row for each of sent messages:

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

>**Note:** In this example we’ll be sending only one message to one destination, so the sent message response array will contain only one element and there is no need for looping it. Anyway, if you decide to send a message to more than one destination, you should iterate through the array of responses.

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

[logs.php](https://github.com/infobip/infobip-api-php-tutorial/blob/master/logs.php) is the page on which the sent messages logs will be presented. You should define **URL for sending request** with sent message logs endpoint, and send request to it with GET HTTP method this time (`CURLOPT_HTTPGET` option set to `TRUE`):
```
$getUrl = 'https://api.infobip.com/sms/1/logs?limit=20';
$curl = curl_init();
// setting options
curl_setopt($curl, CURLOPT_URL, $getUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json'));
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($curl, CURLOPT_USERPWD, $username . ":" . $password);
curl_setopt($curl, CURLOPT_HTTPGET, TRUE);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
// response of the GET request
$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$responseBody = json_decode($response);
curl_close($curl);
```

After execution of request - **culr_exec($curl)**, and getting the HTTP response code, we started parsing the body depending of the response code value, similar we did in [fully featured textual message](#fully-featured-textual-message) chapter.

### Parsing the response

If all went right and HTTP response code from 2xx family was received (200 OK, 201 CREATED, etc), you can extract needed information from response body and present it to your user. In our example we chose: *Message ID*, *To*, *From*, *Text*, *Status Group Name*, *Status Description* and *Sent At*, but you can choose whatever you want like before:

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
        // format the date
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

The code above shows that `file_get_contents('php://input')` method is used for getting a raw POST data as a string. Later lines show you how to inspect whether the data is parsed as XML or JSON, and how to extract pushed delivery reports.For XML we inspect if response body string starts with *<reportResponse>*, and if not, try to decode it without errors - *isJson()* function. If all conditions are `FALSE`, *$result* variable stays unset which means we should say to user that no delivery report was pushed to callback server.

### Parsing the result

Parsing of pushed delivery reports is very similar to parsing the response of [fully featured textual message](#fully-featured-textual-message) and [sent message logs](#sentlogs) methods, except we do not check the HTTP response code (because there is no response at all). All we have to do is to choose which information from pushed delivery reports we want to show, and write it to the page.

[//]: #

   [fftm]: <http://dev.infobip.com/docs/fully-featured-textual-message>
   [sentlogs]: <http://dev.infobip.com/docs/message-logs>
   [dlrnotify]: <http://dev.infobip.com/docs/notify-url>
   [cURL]: <http://php.net/manual/en/function.curl-setopt.php>
   [AMP]: <https://en.wikipedia.org/wiki/List_of_Apache%E2%80%93MySQL%E2%80%93PHP_packages>