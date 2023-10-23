<?php header('Access-Control-Allow-Origin: *'); ?>

<?php
    require_once 'common.php';

    /**
     * Sample PHP code for youtube.subscriptions.insert
     * See instructions for running these code samples locally:
     * https://developers.google.com/explorer-help/code-samples#php
     */

    if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
      throw new Exception(sprintf('Please run "composer require google/apiclient:~2.0" in "%s"', __DIR__));
    }
    require_once __DIR__ . '/vendor/autoload.php';

    $client = new Google_Client();
    $client->setApplicationName('Auricle Collective');
    $client->setScopes([
        'https://www.googleapis.com/auth/youtube.force-ssl',
        'https://www.googleapis.com/auth/userinfo.profile'
    ]);

    $client->setAuthConfig('../client_secret.json');
    $client->setAccessType('offline');

    // Request authorization from the user.
    $client->setApprovalPrompt('force');
    $authUrl = $client->createAuthUrl();
    
    header("Location: " . $authUrl . "&_cb=" . generateRandomString(10));

?>
