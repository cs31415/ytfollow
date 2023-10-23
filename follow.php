<?php header('Access-Control-Allow-Origin: *'); ?>

<?php
    require_once 'common.php';
    require_once __DIR__ . '/vendor/autoload.php';
    use Spatie\Async\Pool;

    function add_subscription($channel, $access_token, &$result_json) {
        // Define the $subscription object, which will be uploaded as the request body.
        try {
            $client = new Google_Client();
            $client->setApplicationName('Auricle Collective');
            $client->setScopes([
                'https://www.googleapis.com/auth/youtube.force-ssl',
                'https://www.googleapis.com/auth/userinfo.profile'
            ]);
            
            $client->setAuthConfig('../client_secret.json');
            $client->setAccessType('offline');

            // Exchange authorization code for an access token.
            $client->setAccessToken($access_token);

            $service = new Google_Service_YouTube($client);

            $subscription = new Google_Service_YouTube_Subscription();

            // Add 'snippet' object to the $subscription object.
            $subscriptionSnippet = new Google_Service_YouTube_SubscriptionSnippet();
            $resourceId = new Google_Service_YouTube_ResourceId();
            $resourceId->setChannelId($channel);
            $resourceId->setKind('youtube#channel');
            $subscriptionSnippet->setResourceId($resourceId);
            $subscription->setSnippet($subscriptionSnippet);

            $response = $service->subscriptions->insert('snippet', $subscription);
            array_push($result_json, array(
                "id" => $channel,
                "thumbnails" => $response->snippet->thumbnails,
                "title" => $response->snippet->title
            ));
        }
        catch(Exception $e) {
            // error reasons: https://developers.google.com/youtube/v3/docs/errors#subscriptions_youtube.subscriptions.insert
            $reason = $e->getErrors()[0]["reason"];
            // get user image and link
            $response = $service->channels->listChannels('snippet,contentDetails,statistics', ['id' => $channel]);
            $channelInfo = $response->items[0];
            if ($reason <> "subscriptionDuplicate") {
                array_push($result_json, array(
                    "id" => $channel,
                    "thumbnails" => $channelInfo->snippet->thumbnails,
                    "title" => $channelInfo->snippet->title,
                    "error" => json_decode($e->getMessage(), true)["error"]["message"], 
                    "hasError" => true, 
                    "code" => $e->getCode(), 
                    "reason" => $reason,
                    "response" => $response
                ));
            }
            else {
                array_push($result_json, array(
                    "id" => $channel,
                    "thumbnails" => $channelInfo->snippet->thumbnails,
                    "title" => $channelInfo->snippet->title));
            }
        }
    }

    // Nip out the access token from querystring
    // this is not even required, since the access token is being cookied
    $t = $_COOKIE['t'];
    $access_token = json_decode(decrypt($t), true);

    $display_name = $_COOKIE["display_name"];
    $config = config();
    $channel_ids = config()["channel_ids"];
   
    $channels = explode(",", $channel_ids);
    $result_json = [];
    $followed = true;
    $futures = [];
    $pool = Pool::create();
    

    for ($i=0; $i < count($channels); $i++) {
        $channel = $channels[$i];    

        $pool[] = async(function() use ($channel, $access_token, &$result_json) {
            add_subscription($channel, $access_token, $result_json);
        })->then(function ($output) {
        })
        ->catch(function ($e) use($channel, &$result_json) {
            $followed = false;
            array_push($result_json, array(
                "id" => $channel,
                "title" => $channel,
                "error" => json_decode($e->getMessage(), true)["error"]["message"], 
                "hasError" => true, 
                "code" => $e->getCode() 
            ));
        });
    }

    await($pool);

    if ($followed) {
        mail($config["admin_email"], $display_name . " followed all Youtube artists on Auricle collective", "");
    }
    else {
        mail($config["admin_email"], $display_name . " failed to follow all Youtube artists on Auricle collective", "");
    }

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($followed ? 200 : 500);
    echo(json_encode($result_json));
?>
