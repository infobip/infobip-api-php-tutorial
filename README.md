# Infobip API PHP tutorial

Welcome to Infobip beginners’ tutorial for creating your own SMS web app. We will guide you one step at a time 
through usage of Infobip API client. Tutorial includes three examples for some of the main features for sending SMS 
messages and checking 
their status:

  - [Fully featured textual message](#a-fully-featured-textual-message)
  - [Sent messages logs](#sent-messages-logs)
  - [Delivery reports on Notify URL](#delivery-reports-on-notify-url)

We will [start](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/index.php) with examples and presentations so you can choose which action you want to perform.

To be able to follow this tutorial, to write and test on your own, you need to set the environment (and we don’t mean to set lights and make some coffee). In order to send messages, get logs, and receive your delivery reports, you have to enable [cURL] php extension in your web server.

For the purpose of this tutorial, you can use some solution from [AMP] solution stack (wamp, xampp, ...). Those are software stacks for the various OSes consisting of Apache web server, MySQL database and PHP programming language support. You should enable **phpcurl** extension for the one you choose.

Additionally, you should have [composer] installed. It is a tool for resolving dependencies in PHP projects and will 
simplify the download and usage of Infobip API client that our project will use. You can find detailed instruction 
on how to install the [composer] on their website and this tutorial will cover the instruction for actually using it.

> **Note:** In order to have a secure sending of SMS messages, these examples should be hosted on HTTPS (using TLS) when going live. Just for sake of simplicity in this tutorial, we have used plain HTTP.

## Infobip API client

In order to use Infobip API client library you will first need to download it into your project. To ease this process
it is recommended you use [composer]. Whit it all you have to do is define a version of the client you wish to use. 
You do that in a special file named [composer.json](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/composer.json):
 
```json
{
  "require": {
    "infobip/infobip-api-php-client": "2.0.0"
  }
}
```

With the *composer.json* file in place you can instruct [composer] to fetch listed dependencies by running the 
following command from the terminal:

```
composer install
```

Whit that there should now be a directory named *vendor* next to the *composer.json* file. If you look inside it 
there is a file named *autoload.php* that will come in handy later. Additionally, *vendor* directory has separate 
subdirectories for infobip api client library code and it's dependencies.

## A fully featured textual message

The fully featured textual message page ([advancedSms.php](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/advancedSms.php))
contains the form for [sending an sms message][fftm]. Submit button will send the request to a page specified in 
`action` attribute of the form. In this example it will post it to itself.


```php
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" ... >
```

Before you make an API call, you should check if the user sent all the fields that we need. In this example we have 
only checked **toInput** field. You don’t have to check them all because not all of the properties are required by 
the API and any missing properties will simply be ignored by the client. If you’re loading the page for the first 
time keep in mind that none of these fields will be present.

### Using Infobip API client

We will be using the Infobip API client to make http requests. That will shorten the code considerably, but first 
you need to tell php where to find necessary classes. You do that by *require-ing* the before mentioned *autoload.php* 
file from *vendors* directory and then specifying parts of client library to use:
 
 ```php
require_once __DIR__ . '/vendor/autoload.php';

use infobip\api\client\SendMultipleTextualSmsAdvanced;
use infobip\api\configuration\BasicAuthConfiguration;
use infobip\api\model\Destination;
use infobip\api\model\sms\mt\send\Message;
use infobip\api\model\sms\mt\send\textual\SMSAdvancedTextualRequest;
 ```
 
Do not worry about most of these classes, we will see them in action shortly. The first thing we need to do is to get
an instance of `SendMultipleTextualSmsAdvanced` client. You can get it by providing it with authentication 
configuration object:
 
```php
$configuration = new BasicAuthConfiguration($_POST['username'], $_POST['password'], 'http://api.infobip.com/');
$client = new SendMultipleTextualSmsAdvanced($configuration);
```

Note the last parameter in the `BasicAuthConfiguration` constructor ant its use of http protocol. This is 
done for simplicity only. In your production implementation you should leave that parameter out entirely. In that 
case configuration object will default to using https. See note in the [introduction](#infobip-api-php-tutorial) chapter.

Now you have a `$client` that you can ask to execute requests for you. It will handle converting requests into JSON, 
setting up and executing http requests and parsing API responses for you. All that remains is to fill out the request 
model and display the response to the user.

### Building the request

Generally, API client allows you to send different texts messages, each to multiple phone numbers all in a single 
request. In this example we will only send one text message to one destination. To assemble the request you will need
three objects. We'll start with the `Destination`:

```php
$destination = new Destination();
$destination->setTo($_POST['toInput']);
$destination->setMessageId($_POST['messageIdInput']);
```

`to` property is the phone number that the sms will be sent to while `messageId` is a bit more interesting. That 
property is used later on to uniquely identify the sms message when, for example, fetching the logs for it. We will 
see this used in the [Sent messages logs](#sent-messages-logs) chapter of this tutorial. Note that, while `to` 
property is required, `messageId` is not and the sms will be successfully sent event if it is not set. In that case 
the API will generate a random message id which you will receive in the response to this request. 

Next model is the `Message` itself. It allows you to specify multiple destinations, each of which will receive the 
same text. You can pass it an array with only one destination:
```php
$message = new Message();
$message->setDestinations([$destination]);
$message->setFrom($_POST['fromInput']);
$message->setText($_POST['textInput']);
$message->setNotifyUrl($_POST['notifyUrlInput']);
$message->setNotifyContentType($_POST['notifyContentTypeInput']);
$message->setCallbackData($_POST['callbackDataInput']);
```

Properties `from` and `text` define part of the sms message visible to the message's recipient. Specifically, `from` 
will be displayed as a sender of the message and `text` will, naturally, be the sent text. On the other hand, 
`notifyUrl`, `notifyContentType` and `callbackData` are meta properties that are used to generate the delivery report
and send it back to you. You'll find out more about delivery reports in the [Delivery reports on Notify URL](#delivery-reports-on-notify-url)
chapter.

Finally, you wrap the message in a request model:

```php
$requestBody = new SMSAdvancedTextualRequest();
$requestBody->setMessages([$message]);
```

### Displaying the response

With both `$requestBody` and `$client` ready you can instruct the `$client` to execute the request and parse the 
response with a single line:

```php
$response = $client->execute($requestBody);
```

It is important to handle all the edge cases and inform the user of everything that is going on with our applications.
In this case that means properly handling both the successful and unsuccessful API calls. You can achieve this by 
wrapping the `$client->execute` method call in a *try-catch block*. From a high overview it should look like this:
 
 ```php
try {
	$apiResponse = $client->execute($requestBody);
	// display results
} catch (Exception $apiCallException) {
	// display the error message
}
 ```

To display results iterate through the array of sent message responses with a foreach loop and write a single row for 
each of sent messages. In our example we chose to present: `messageId`, `to`, `smsCount`, `status`, but you can choose 
which ever you want. Code should look like this:
 ```php
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
```
 
>**Note:** In this example we’ll be sending only one message to one destination, so the sent message response array 
will contain only one element and there is no need for looping it. However, if you decide to send a message to more 
than one destination, you have to iterate through the array of responses.
 
If the exception occurs, this is how you can present detailed error message to the user:

```php
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
```

The code above will try to parse error response from the API and if even that fails will print whatever message the 
`$apiCallException` contained.

And that's all the code you'll need to send an sms message! You now have a full functional app for sending messages. 
You can find the full code at [advancedSms.php](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/advancedSms.php).

## Sent messages logs

When you choose this option it opens [logs.php](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/logs.php) page with input form for [getting sent messages logs][sentlogs]. Submit button will **POST** those fields to itself.

### Using Infobip API client

Same as we did in the [fully featured textual message](#fully-featured-textual-message) chapter we'll need to use a 
couple of classes from the API client library. This time `use` statements will look like this:
 
 ```php
require_once __DIR__ . '/vendor/autoload.php';

use infobip\api\client\GetSentSmsLogs;
use infobip\api\configuration\BasicAuthConfiguration;
use infobip\api\model\sms\mt\logs\GetSentSmsLogsExecuteContext;
 ```

And again, you can instantiate a client object by passing the authentication configuration to it's constructor:

```php
$configuration = new BasicAuthConfiguration($_POST['username'], $_POST['password'], 'http://api.infobip.com/');
$client = new GetSentSmsLogs($configuration);
```

Once again, you can leave the last parameter of the `BasicAuthConfiguration` constructor out in your production code.
See note in the [introduction](#infobip-api-php-tutorial) chapter.

### Building the execution context

Unlike the previous chapter where you were sending the data to Infobip API, in this case we are primarily concerned 
with fetching the data from the API. However, you might still wish to filter the retrieved logs by some properties. 
For example, you might want to only retrieve 20 logs sent to a specific phone number. You can specify such filtering 
parameters using the `GetSentSmsLogsExecuteContext` model:

```php
$context = new GetSentSmsLogsExecuteContext();
$context->setMessageId($_POST['messageIdInput']);
$context->setTo($_POST['toInput']);
$context->setLimit(20);
```

### Displaying the response

Just like in the previous chapter we'll want to handle both successful and unsuccessful API calls. Again, you can use 
the *try-catch block* to wrap the call to `$client->execute` and handle the potential `Exception` in the catch block:

 ```php
try {
	$apiResponse = $client->execute($context);
	// display results
} catch (Exception $apiCallException) {
	// display the error message
}
 ```

If all went well and API call was successful, you can iterate through the results array returned by the API with a 
*foreach loop*. In this case the loop is necessary because we asked to retrieve multiple message logs. For each 
log extract needed information and present it to your user in a table row. In our example we chose: `messageId`,
`to`, `from`, `text`, `status`, and `sentAt` properties. Like before, you can choose whatever you want. Our code 
looks like this:

```php
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
    $formattedSentAt = $log->getSentAt()->format("M d, Y - H:i:s P T");
    echo "<td>" . $formattedSentAt . "</td>";
    echo "</tr>";
} ?>
```

Finally, you can handle the exception the same way you did it in the previous chapter:

```php
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
```

Again, the full code for this page can be found at [logs.php](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/logs.php).

## Delivery reports on Notify URL

This feature is slightly different from the previous two - the page [dlrPush.php](https://github.com/infobip/infobip-api-php-tutorial/blob/api-client-example/dlrPush.php)
is not used for requesting some data, [it is waiting for it][dlrnotify]. When the data is pushed to this page, it can be parsed 
and showed to the user in appropriate way.

>**Note:** The delivery reports are pushed from the fully featured textual message page by entering *this page URL* in *Notify URL* field. Also, *Notify ContentType* field in that page defines which type of body is about to arrive.

### Receiving pushed delivery report

```php
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

The code above shows that `file_get_contents('php://input')` method is used for getting a raw POST data as a string. Later lines show you how to inspect whether the data is parsed as XML or JSON, and how to extract pushed delivery reports.

For XML we inspect if response body string starts with `<reportResponse>`, and if not, try to decode it without errors - *isJson()* function. If all conditions are `FALSE`, *$result* variable stays unset which means we should say to user that no delivery report was pushed to callback server.

### Parsing the result

Parsing of pushed delivery reports is similar to parsing the response of [fully featured textual message](#fully-featured-textual-message) and [sent message logs](#sentlogs) methods, except we do not check the HTTP response code (because there is no response at all). All we have to do is to choose which information from pushed delivery reports we want to show, and write it to the page.

[//]: #

   [fftm]: <http://dev.infobip.com/docs/fully-featured-textual-message>
   [sentlogs]: <http://dev.infobip.com/docs/message-logs>
   [dlrnotify]: <http://dev.infobip.com/docs/notify-url>
   [cURL]: <http://php.net/manual/en/function.curl-setopt.php>
   [AMP]: <https://en.wikipedia.org/wiki/List_of_Apache%E2%80%93MySQL%E2%80%93PHP_packages>
   [composer]: <https://getcomposer.org/doc/00-intro.md>