<?php

/**
 * The class is designed to fetch user data from facebook graph API using OAuth.
 * It is mainly created for Facebook Login purposes.
 * 
 * @author Ismar Tričić ismar.tricic[at]gmail.com
 * @copyright Copyright 2018 Ismar Tričić ismar.tricic[at]gmail.com
 * @license https://opensource.org/licenses/MIT The MIT License
 */
class Facebook_OAuth_Client
{
    const API_VERSION = "v3.2";
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $scopes;
    private $state;
    private $fields;
    private $user_token;
    private $access_token;
    private $user_id;
    private $last_response;

    public function setClientId(string $client_id)
    {
        $this->client_id = $client_id;
    }

    public function setClientSecret(string $client_secret)
    {
        $this->client_secret = $client_secret;
    }

    public function setRedirectUri(string $redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
    }

    public function setScopes(string $scopes)
    {
        $this->scopes = $scopes;
    }

    public function setPermissions(string $scopes)
    {
        $this->scopes = $scopes;
    }

    public function setFields(string $fields)
    {
        $this->fields = $fields;
    }

    public function setState(string $state)
    {
        $this->state = $state;
    }

    public function setUserToken(string $user_token)
    {
        $this->user_token = $user_token;
    }

    public function getLastResponse(bool $parse_to_array = false)
    {
        return $parse_to_array ? json_decode($this->last_response, true) : $this->last_response;
    }

    /**
     * Triggers E_USER_ERROR if curl extension is not loaded
     */
    public function __construct()
    {
        if (extension_loaded("curl") === false)
        {
            trigger_error(__CLASS__ . " requires curl extension to be loaded.", E_USER_ERROR);
        }
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/#logindialog
     */
    public function createAuthUrl()
    {
        return sprintf(
            "https://www.facebook.com/%s/dialog/oauth?client_id=%s&redirect_uri=%s&state=%s&auth_type=rerequest&scope=%s",
            self::API_VERSION,
            $this->client_id,
            $this->redirect_uri,
            $this->state,
            $this->scopes
        );
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/#exchangecode
     */
    public function fetchAccessTokenWithAuthCode(string $code)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => sprintf(
                "https://graph.facebook.com/%s/oauth/access_token?client_id=%s&redirect_uri=%s&client_secret=%s&code=%s",
                self::API_VERSION,
                $this->client_id,
                $this->redirect_uri,
                $this->client_secret,
                $code
            ),
            CURLOPT_RETURNTRANSFER => 1
        ]);

        $this->last_response = curl_exec($ch);
        
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
        {
            curl_close($ch);
            return false;
        }

        $this->access_token = json_decode($this->last_response, true)["access_token"];
        curl_close($ch);
        return true;
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/#checktoken
     */
    public function fetchUserId()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => sprintf(
                "https://graph.facebook.com/debug_token?input_token=%s&access_token=%s&fields=user_id",
                $this->user_token,
                $this->access_token
            ),
            CURLOPT_RETURNTRANSFER => 1
        ]);

        $this->last_response = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
        {
            curl_close($ch);
            return false;
        }

        $this->user_id = json_decode($this->last_response, true)["data"]["user_id"];
        curl_close($ch);
        return true;
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/#permscheck
     */
    public function fetchPermissions()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => sprintf(
                "https://graph.facebook.com/%s/%s/permissions?access_token=%s",
                self::API_VERSION,
                $this->user_id,
                $this->access_token
            ),
            CURLOPT_RETURNTRANSFER => 1
        ]);

        $this->last_response = curl_exec($ch);

        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
        {
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        return json_decode($this->last_response, true)["data"];
    }

    /**
     * @see https://developers.facebook.com/docs/graph-api/using-graph-api/#reading
     */
    public function fetchData()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => sprintf(
                "https://graph.facebook.com/%s/%s?access_token=%s&fields=%s",
                self::API_VERSION,
                $this->user_id,
                $this->access_token,
                $this->fields
            ),
            CURLOPT_RETURNTRANSFER => 1
        ]);
        
        $this->last_response = curl_exec($ch);
        
        if (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200)
        {
            curl_close($ch);
            return false;
        }
        
        curl_close($ch);
        return json_decode($this->last_response, true);
    }
}