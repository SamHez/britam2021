<?php

class MomoPaymentApi
{

    public $currency = "EUR";
    public $targetEnv = "sandbox";
    
    private $primaryKey = "9cef1ef4936f4f9793dc2adef9379c4a";
    private $secondaryKey = "8ea6903921e5407ca9402191c13ead77";

    private $apiKey = "1b7ea79decd44dd5a6932bcb16054b8a";

    public $uuid = "a3e3b6e8-a0ff-4db7-bb3e-1dafb33879b9";

    public $referenceId;
    public $accessToken;
    

    public function __construct(){
        $this->referenceId = $this->generateUUID();
        $this->accessToken = $this->generateAccessToken();
    }

    public function generateUUID()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // used once to create a user
    public function createUser()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n  \"providerCallbackHost\": \"" . $this->baseURL . "\"\n}",
            CURLOPT_HTTPHEADER => array(
                "X-Reference-Id: " . $this->uuid,
                "Content-Type: application/json",
                "Ocp-Apim-Subscription-Key: " . $this->primaryKey,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    // uses to get user information
    public function getUserInfo()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/" . $this->uuid,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Ocp-Apim-Subscription-Key: " . $this->primaryKey,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    //used once to create am api key
    public function gnerateKey()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/" . $this->uuid . "/apikey",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Length: 0",
                "Ocp-Apim-Subscription-Key: " . $this->primaryKey,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    // used once to create a basic api token used to generate the bearer token
    public function generateBase64EncodedToken()
    {
        $username = $this->uuid;
        $password = $this->apiKey;

        $auth = $username . ":" . $password;
        $credentials = base64_encode($auth);

        return $credentials;
    }

    // used often to generate a new access token
    public function generateAccessToken()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/collection/token/",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER => array(
                "Content-Length: 0",
                "Authorization: Basic " . $this->generateBase64EncodedToken(),
                "Ocp-Apim-Subscription-Key: " . $this->primaryKey,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $token_obj = json_decode($response);

        return $token_obj->access_token;
    }

    // used to request a payment from a customer
    public function requestPayment($amount, $tel)
    {

        $jsonData = json_encode(array(
            "amount" => $amount,
                "currency" => $this->currency,
                "externalId" => $this->generateUUID(),
                "payer" => array(
                  "partyIdType" => "MSISDN",
                  "partyId" => $tel
                ),
                "payerMessage" => "Payent",
                "payeeNote" => "Payment For Britam MTP"            
            ), JSON_FORCE_OBJECT);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer ". $this->accessToken,
            "X-Reference-Id: ". $this->referenceId,
            "X-Target-Environment: ". $this->targetEnv,
            "Content-Type: application/json",
            "Ocp-Apim-Subscription-Key: c648508309f74634825bb7f2a17063a9"
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    // used to check the payment status
    public function checkPaymentStatus($refId=null)
    {

        if (!is_null($refId)) {
            $this->referenceId = $refId;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay/". $this->referenceId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ". $this->accessToken,
                "X-Target-Environment: ". $this->targetEnv,
                "Ocp-Apim-Subscription-Key: ". $this->primaryKey,
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }
}


