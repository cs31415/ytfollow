<?php header('Access-Control-Allow-Origin: *'); ?>

<?php
/*    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");*/
?>

<?php
    require_once 'common.php';
    require_once __DIR__ . '/vendor/autoload.php';

    // get code from querystring
    $error = isset($_GET["error"]) ? $_GET["error"] : NULL;
    $code = $_GET["code"];
    $url = "/ytfollow";    

    if (!is_null($error)) {
        header("Location: " . $url . "?error=" . $error);    
    }
    else {
        $client = new Google_Client();
        $client->setApplicationName('Auricle Collective');
        $client->setScopes([
            'https://www.googleapis.com/auth/youtube.force-ssl',
            'https://www.googleapis.com/auth/userinfo.profile'
        ]);
        
        $client->setAuthConfig('../client_secret.json');
        $client->setAccessType('offline');
    
        // Exchange authorization code for an access token.
        $access_token = $client->fetchAccessTokenWithAuthCode($code);
        $client->setAccessToken($access_token);

        // Get the user's display name    
        $oauth2 = new Google_Service_Oauth2($client);
        $userInfo = $oauth2->userinfo->get();
        $display_name = $userInfo->name;

        if (!is_null($code))  {
            // Encrypt and cookie access token
            $enc_access_token = encrypt(json_encode($access_token)); 

            setcookie("t", $enc_access_token, time()+1200); 
            setcookie("display_name", $display_name);

            // Redirect to home
            header("Location: /ytfollow");
        }
        else {
            header("Location: /ytfollow?error=authorization_error");
        }
    }
?>
