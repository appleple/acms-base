<?php

namespace Acms\Plugins\Base;
use RuntimeException;
use SQL;
use DB;

class Api
{
    /**
     * @var string
     */
    private $endpoint = 'https://api.thebase.in/1/';

    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $grantType;

    /**
     * @var string
     */
    private $callbackUri;

    /**
     * @var array
     */
    private $scope;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * AAPP_Base_Api constructor.
     *
     * @param $client_id
     * @param $client_secret
     * @param $callback_uri
     */
    public function __construct($client_id, $client_secret, $callback_uri)
    {
        $this->setClientId($client_id);
        $this->setClientSecret($client_secret);
        $this->setCallbackUri($callback_uri);
    }

    /**
     * client id setter
     *
     * @param string $client_id
     *
     * @return void
     */
    public function setClientId($client_id)
    {
        $this->clientId = $client_id;
    }

    /**
     * client secret setter
     *
     * @param string $client_secret
     *
     * @return void
     */
    public function setClientSecret($client_secret)
    {
        $this->clientSecret = $client_secret;
    }

    /**
     * grant type setter
     *
     * @param string $grant_type
     *
     * @return void
     */
    public function setGrantType($grant_type)
    {
        $this->grantType = $grant_type;
    }

    /**
     * callback url setter
     *
     * @param string $callback_uri
     *
     * @return void
     */
    public function setCallbackUri($callback_uri)
    {
        $this->callbackUri = $callback_uri;
    }

    /**
     * scope setter
     *
     * @param array $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * get authorize redirect uri
     *
     * @param array $params
     *
     * @return string
     */
    public function getAuthUrl($params = array())
    {
        $base = $this->endpoint . 'oauth/authorize';
        $params += array(
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->callbackUri,
            'scope' => implode(' ', $this->scope),
        );

        $query = http_build_query($params);

        return $base . '?' . $query;
    }

    /**
     * request get api
     * ToDo: キャッシュ処理を追加
     *
     * @param string $uri
     * @param array $params
     *
     * @return object
     */
    public function get($uri, $params = array())
    {
        $key = sha1($uri.serialize($params));
        $response = $this->cache($key);

        if ( !$response ) {
            $headers = array(
                'Authorization: Bearer ' . $this->getAccessToken(),
            );
            $response = $this->request('GET', $uri, $headers, $params);
            $this->saveCache($key, $response);
        }

        $json = json_decode($response);
        if ( property_exists($json, 'error') ) {
            throw new RuntimeException($json->error_description);
        }

        return $json;
    }

    /**
     * request access token
     *
     * @param string $val
     * @param string $type code | refresh_token
     *
     * @return string
     */
    public function requestAccessToken($val, $type = "code")
    {
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
        );
        $params = array(
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            $type => $val,
            'grant_type' => $this->grantType,
            'redirect_uri' => $this->callbackUri,
        );

        $response = $this->request('POST', 'oauth/token', $headers, $params);
        $json = json_decode($response);
        $this->accessToken = $json->access_token;
        $this->saveAccessToken($response);

        return $this->accessToken;
    }

    /**
     * save access token
     *
     * @param string $token
     *
     * @throws RuntimeException
     *
     * @return void
     */
    public function saveAccessToken($token)
    {
        $json = json_decode($token);
        if ( property_exists($json, 'error') ) {
            throw new RuntimeException($json->error_description);
        } else if ( !property_exists($json, 'access_token') ) {
            throw new RuntimeException('Failed to get access token for service account.');
        }

        $SQL = SQL::newDelete('base_api');
        $SQL->addWhereOpr('blog_id', BID);
        DB()->query($SQL->get(dsn()), 'exec');

        $SQL = SQL::newInsert('base_api');
        $SQL->addInsert('token_type', $json->token_type);
        $SQL->addInsert('access_token', $json->access_token);
        $SQL->addInsert('refresh_token', $json->refresh_token);
        $SQL->addInsert('expire', date('Y-m-d H:i:s', REQUEST_TIME + intval($json->expires_in) - 600));
        $SQL->addInsert('blog_id', BID);

        DB()->query($SQL->get(dsn()), 'exec');
    }

    /**
     * get access token
     *
     * @return string
     */
    public function getAccessToken()
    {
        if ( !!$this->accessToken ) {
            return $this->accessToken;
        }

        $SQL = SQL::newSelect('base_api');
        $SQL->addSelect('access_token');
        $SQL->addWhereOpr('blog_id', BID);
        $SQL->addWhereOpr('expire', date('Y-m-d H:i:s', REQUEST_TIME), '>');

        if ( $token = DB()->query($SQL->get(dsn()), 'one') ) {
            return $token;
        }

        return $this->requestAccessTokenFromRefreshToken();
    }

    /**
     * request access token from refresh token.
     *
     * @throws RuntimeException
     *
     * @return string
     */
    protected function requestAccessTokenFromRefreshToken()
    {
        $SQL = SQL::newSelect('base_api');
        $SQL->addSelect('refresh_token');
        $SQL->addWhereOpr('blog_id', BID);

        if ( $token = DB()->query($SQL->get(dsn()), 'one') ) {
            $this->setGrantType('refresh_token');
            return $this->requestAccessToken($token, 'refresh_token');
        }

        throw new RuntimeException('Failed to get access token from refresh token.');
    }

    /**
     * @param string $method
     * @param $uri
     * @param $headers
     * @param $params
     *
     * @return bool|string
     */
    protected function request($method = 'GET', $uri, $headers, $params)
    {
        $method = strtoupper($method);
        $query = '';
        $http = array(
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
        );

        if ( $method === 'GET' ) {
            $query = '?' . http_build_query($params);
        } else if ( $method === 'POST' ) {
            $http['content'] = http_build_query($params);
        } else {
            return false;
        }

        $context = stream_context_create(array('http' => $http));

        return file_get_contents($this->endpoint . $uri . $query, false, $context);
    }

    /**
     * save cache data
     *
     * @param string $key
     * @param string $value
     */
    protected function saveCache($key, $value)
    {
        $SQL = SQL::newDelete('base_cache');
        $SQL->addWhereOpr('id', $key, '=', 'OR');
        $SQL->addWhereOpr('expire', date('Y-m-d H:i:s', REQUEST_TIME), '<', 'OR');
        DB()->query($SQL->get(dsn()), 'exec');

        $SQL = SQL::newInsert('base_cache');
        $SQL->addInsert('id', $key);
        $SQL->addInsert('data', $value);
        $SQL->addInsert('expire', date('Y-m-d H:i:s', REQUEST_TIME + config('base_cache_time', 0)));
        DB()->query($SQL->get(dsn()), 'exec');
    }

    /**
     * get cache data
     *
     * @param string $key
     * @return bool|string
     */
    protected function cache($key)
    {
        $SQL = SQL::newSelect('base_cache');
        $SQL->addSelect('data');
        $SQL->addWhereOpr('id', $key);
        $SQL->addWhereOpr('expire', date('Y-m-d H:i:s', REQUEST_TIME), '>');

        if ( $data = DB()->query($SQL->get(dsn()), 'one') ) {
            return $data;
        }

        return false;
    }
}
