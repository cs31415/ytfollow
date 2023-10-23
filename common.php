<?php
    function console_log($log_msg) {
        $log_filename = "../log";
        
        if (!file_exists($log_filename)) 
        {
            // create directory/folder uploads.
            mkdir($log_filename, 0777, true);
        }
        
        $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.txt';
        
        file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
    }

    function http_request($method, $url, $headers, $body = NULL) {
        $curl = curl_init();
        
        curl_setopt($curl, CURLOPT_URL, $url);
        
        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        elseif ($method === "PUT") {
            curl_setopt($curl, CURLOPT_PUT, true);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        
        $result = curl_exec($curl);
        
        curl_close($curl);

        return $result;
    }

    function random() {
        return (float)rand()/(float)getrandmax();
    }

    /**
     * Generates a random string containing numbers and letters
     * @param  {number} length The length of the string
     * @return {string} The generated string
     */
    function generateRandomString($length) {
        $text = "";
        $possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for ($i = 0; $i < $length; $i++) {
            $rand = random();
            $len = strlen($possible);
            $idx = floor(random() * strlen($possible)); 
            $text .= $possible[$idx];
        }

        return $text;
    };

    function encrypt($plain_text) {
        $key = config()["key"];
        $ivlen = openssl_cipher_iv_length($cipher="aes-256-cbc");
        $iv = openssl_random_pseudo_bytes($ivlen);
        
        $ciphertext_raw = openssl_encrypt($plain_text, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        
        return base64_encode( $iv.$hmac.$ciphertext_raw );
    }

    function decrypt($cipher_text) {
        $key = config()["key"];
        $c = base64_decode($cipher_text);
        $ivlen = openssl_cipher_iv_length($cipher="aes-256-cbc");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len=32);
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
        
        if (hash_equals($hmac, $calcmac))// timing attack safe comparison
        {
            return $original_plaintext;
        }    
        
        return "";
    }

    function config() {
        $ini = parse_ini_file('../ytfollow.ini');

        return $ini;
    }
?>
