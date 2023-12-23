<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$directoryPath = dirname(dirname(__DIR__));

require $directoryPath . '/scripts/PHPMailer/src/Exception.php';
require $directoryPath . '/scripts/PHPMailer/src/PHPMailer.php';
require $directoryPath . '/scripts/PHPMailer/src/SMTP.php';

include $directoryPath . '/scripts/config/smtpconfig.php';

// Replace with your actual API key
$apiKey = 'APIKEY';
// Fetch top headlines from the US
$apiUrl = "https://gnews.io/api/v4/top-headlines?token={$apiKey}&lang=en&country=us";

// Make a GET request to the API endpoint
$response = file_get_contents($apiUrl);

// Check if the request was successful
if ($response !== false) {
    
    // Parse the response as JSON
    $data = json_decode($response, true);

    // Get yesterday's date in the format 'YYYY-MM-DD'
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Filter the articles published yesterday
    $yesterdayArticles = array_filter($data['articles'], function($article) use ($yesterday) {
        return substr($article['publishedAt'], 0, 10) === $yesterday;
    });

    // Check if there are any articles published yesterday
    if (!empty($yesterdayArticles)) {
        // Get a random article from yesterday's articles
        $randomArticle = $yesterdayArticles[array_rand($yesterdayArticles)];

        $title = $randomArticle['title'];
        $description = $randomArticle['description'];
        $url = $randomArticle['url'];
        $publishedAt = $randomArticle['publishedAt'];

        // Format the date
        $date = date('Y-m-d', strtotime($publishedAt));

        // Get the server hostname
        $hostname = gethostname();
        $parts = explode('.', $hostname);
        $firstPart = $parts[0];

        // Set up PHPMailer
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->SMTPDebug = $smtpdebug;
        
        $mail->Host = $smtphost;
        $mail->Port = $smtpport;
        $mail->SMTPSecure = $smtpsecure;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;

        $mail->setFrom($emailFrom, $emailFromName);
        
        $subject = "$firstPart - Today's News Article - " . date('Y-m-d');
        $mail->Subject = $subject;

        // Set the email body as HTML
        $mail->isHTML(true);

        // Format the email body using HTML markup
        $mail->Body = "<h1>$title</h1>";
        $mail->Body .= "<p>$description</p>";
        $mail->Body .= "<p><a href=\"$url\">Read More</a></p>";
        $mail->Body .= "<p>Published Date: $date</p>";
        $mail->Body .= "<p>Regards,<br>GNews</p>";

        // Read the recipient list from file
        $recipientListFile = $directoryPath . '/scripts/news/recipient_list.txt';
        $recipientList = file($recipientListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Send the email to each recipient individually
        foreach ($recipientList as $recipient) {
            $mail->clearAddresses();
            $mail->addAddress($recipient);

            // Send the email
            if ($mail->send()) {
                //echo "Email sent successfully to: $recipient\n";
            } else {
                $errorMessage = "Failed to send the email to: $recipient. Error: " . $mail->ErrorInfo;
                error_log($errorMessage);
            }
        }
    } else {
        $errorMessage = "No articles found for yesterday.";
        error_log($errorMessage);
    }
} else {
    $errorMessage = "Error: Failed to retrieve data from the API.";
    error_log($errorMessage);
}
