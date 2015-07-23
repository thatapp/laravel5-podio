<?php

use Illuminate\Support\Facades\Cache;

class Podio
{
    public $oauth, $debug, $logger, $last_response, $auth_type, $api;
    protected $url, $client_id, $client_secret, $secret, $ch, $headers;


    const VERSION = '4.0.3';

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * @param $session_id The session identifier. We will need to store this in order to be able to pull the correct
     *                    Podio object from the session
     * @throws Exception. If given session id is not an int or string
     */
    public function save_to_cache($session_id)
    {
        /*
         * We find Podio objects based on the given refresh code from Podio.
         * Because those usually don't change very often; not like access tokens
         * or the like.
         */

        if (!is_string($session_id) && !is_int($session_id))
            throw new Exception('Given session id is not a valid key');

        Cache::forever($session_id, serialize($this->oauth));
    }

    /**
     * @param $session_id The session identifier. We use this to pull the correct podio object from session.
     * @throws Exception If @save_to_session() hasn't been called, or if given session id is not an int or string
     * @return Podio The podio object from cache.
     */
    public static function get_from_cache($session_id)
    {
        if (!is_string($session_id) && !is_int($session_id))
            throw new Exception('Given session id is not a valid key');

        if (!Cache::has($session_id))
            return null;

        $oauth = unserialize(Cache::get($session_id));
        return self::_from_oauth($oauth);
    }

    /**
     * @param $session_id, The Id of the session to purge from the cache
     * @throws Exception If given session id is not an int or string
     */
    public static function purge_from_cache($session_id)
    {

        if (!is_string($session_id) && !is_int($session_id))
            throw new Exception('Given session id is not a valid key');

        if (!Cache::has($session_id))
            return; // Doesn't exist. Let's not make a fuss about it //

        Cache::forget($session_id);
    }


    /* Creates a podio object from the oauth. Useful for caching podio credentials without actually caching podio */
    private static function _from_oauth(PodioOAuth $_oauth)
    {
        $podio = new Podio($_oauth->client_id, $_oauth->client_secret);
        $podio->oauth = $_oauth;
        return $podio;
    }

    /**
     * @param $client_id Client id required by Podio for authorization
     * @param $client_secret Client secret required by Podio for authorization
     * @param array $curl_option Optional cURL options when doing requests
     */
    public function __construct($client_id, $client_secret, $curl_option = array())
    {
        $this->api = new ApiHelper($this);

        // Setup client info
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;

        // Setup curl
        $this->url = empty($options['api_url']) ? 'https://api.podio.com' : $options['api_url'];
        $this->debug = $this->debug ? $this->debug : false;
        $this->ch = curl_init();
        $this->headers = array(
            'Accept' => 'application/json',
        );

        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Podio PHP Client/' . self::VERSION);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);

        if (!empty($curl_option)) {
            curl_setopt_array($this->ch, $options['curl_options']);
        }
    }

    public function authenticate_with_app($app_id, $app_token)
    {
        return $this->authenticate('app', array('app_id' => $app_id, 'app_token' => $app_token));
    }

    public function authenticate_with_password($username, $password)
    {
        return $this->authenticate('password', array('username' => $username, 'password' => $password));
    }

    public function authenticate_with_authorization_code($authorization_code, $redirect_uri)
    {
        return $this->authenticate('authorization_code', array('code' => $authorization_code, 'redirect_uri' => $redirect_uri));
    }

    public function refresh_access_token($refresh_token = null)
    {
        return $this->authenticate('refresh_token', array('refresh_token' => $refresh_token ? $refresh_token : $this->oauth->refresh_token));
    }

    public function authenticate($grant_type, $attributes)
    {
        $data = array();
        $auth_type = array('type' => $grant_type);

        switch ($grant_type) {
            case 'password':
                $data['grant_type'] = 'password';
                $data['username'] = $attributes['username'];
                $data['password'] = $attributes['password'];

                $auth_type['identifier'] = $attributes['username'];
                break;
            case 'refresh_token':
                $data['grant_type'] = 'refresh_token';
                $data['refresh_token'] = $attributes['refresh_token'];
                break;
            case 'authorization_code':
                $data['grant_type'] = 'authorization_code';
                $data['code'] = $attributes['code'];
                $data['redirect_uri'] = $attributes['redirect_uri'];
                break;
            case 'app':
                $data['grant_type'] = 'app';
                $data['app_id'] = $attributes['app_id'];
                $data['app_token'] = $attributes['app_token'];

                $auth_type['identifier'] = $attributes['app_id'];
                break;
            default:
                break;
        }

        $request_data = array_merge($data, array('client_id' => $this->client_id, 'client_secret' => $this->client_secret));
        if ($response = $this->request(self::POST, '/oauth/token', $request_data, array('oauth_request' => true))) {
            $body = $response->json_body();
            $this->oauth = new PodioOAuth($this->client_id, $this->client_secret, $body['access_token'], $body['refresh_token'], $body['expires_in'], $body['ref']);

            // Don't touch auth_type if we are refreshing automatically as it'll be reset to null
            if ($grant_type !== 'refresh_token') {
                $this->auth_type = $auth_type;
            }

            return true;
        }
        return false;
    }

    public function clear_authentication()
    {
        $this->oauth = new PodioOAuth();
    }

    public function authorize_url($redirect_uri)
    {
        $parsed_url = parse_url($this->url);
        $host = str_replace('api.', '', $parsed_url['host']);
        return 'https://' . $host . '/oauth/authorize?response_type=code&client_id=' . $this->client_id . '&redirect_uri=' . rawurlencode($redirect_uri);
    }

    public function is_authenticated()
    {
        return $this->oauth && $this->oauth->access_token;
    }

    public function request($method, $url, $attributes = array(), $options = array())
    {
        if (!$this->ch) {
            throw new Exception('Client has not been setup with client id and client secret.');
        }

        // Reset attributes so we can reuse curl object
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, null);

        unset($this->headers['Content-length']);
        $original_url = $url;
        $encoded_attributes = null;

        if (is_object($attributes) && substr(get_class($attributes), 0, 5) == 'Podio') {
            $attributes = $attributes->as_json(false);
        }

        if (!is_array($attributes) && !is_object($attributes)) {
            throw new PodioDataIntegrityError('Attributes must be an array');
        }

        switch ($method) {
            case self::GET:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, self::GET);
                $this->headers['Content-type'] = 'application/x-www-form-urlencoded';

                $separator = strpos($url, '?') ? '&' : '?';
                if ($attributes) {
                    $query = Podio::encode_attributes($attributes);
                    $url = $url . $separator . $query;
                }

                $this->headers['Content-length'] = "0";
                break;
            case self::DELETE:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, self::DELETE);
                $this->headers['Content-type'] = 'application/x-www-form-urlencoded';

                $separator = strpos($url, '?') ? '&' : '?';
                if ($attributes) {
                    $query = Podio::encode_attributes($attributes);
                    $url = $url . $separator . $query;
                }

                $this->headers['Content-length'] = "0";
                break;
            case self::POST:
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, self::POST);
                if (!empty($options['upload'])) {
                    $cfile = curl_file_create(substr($attributes["source"], 1));
                    $attributes["source"] = $cfile;
                    curl_setopt($this->ch, CURLOPT_POST, TRUE);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $attributes);
                    $this->headers['Content-type'] = 'multipart/form-data';
                }
                elseif (empty($options['oauth_request'])) {
                    // application/json
                    $encoded_attributes = json_encode($attributes);
                    curl_setopt($this->ch, CURLOPT_POSTFIELDS, $encoded_attributes);
                    $this->headers['Content-type'] = 'application/json';
                }
                else {
                  // x-www-form-urlencoded
                  $encoded_attributes = self::encode_attributes($attributes);
                  curl_setopt($this->ch, CURLOPT_POSTFIELDS, $encoded_attributes);
                    $this->headers['Content-type'] = 'application/x-www-form-urlencoded';
                }
                break;
            case self::PUT:
                $encoded_attributes = json_encode($attributes);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $encoded_attributes);
                $this->headers['Content-type'] = 'application/json';
                break;
        }

        // Add access token to request
        if (isset($this->oauth) && !empty($this->oauth->access_token) && !(isset($options['oauth_request']) && $options['oauth_request'])) {
            $token = $this->oauth->access_token;
            $this->headers['Authorization'] = "OAuth2 {$token}";
        } else {
            unset($this->headers['Authorization']);
        }

        // File downloads can be of any type
        if (empty($options['file_download'])) {
            $this->headers['Accept'] = 'application/json';
        } else {
            $this->headers['Accept'] = '*/*';
        }

        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->curl_headers());
        curl_setopt($this->ch, CURLOPT_URL, empty($options['file_download']) ? $this->url . $url : $url);

        $response = new PodioResponse();
        $raw_response = curl_exec($this->ch);
        $raw_headers_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $response->body = utf8_encode(substr($raw_response, $raw_headers_size));
        $response->status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $response->headers = Podio::parse_headers(substr($raw_response, 0, $raw_headers_size));
        $this->last_response = $response;

        if (!isset($options['oauth_request'])) {
            $curl_info = curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
            $this->log_request($method, $url, $encoded_attributes, $response, $curl_info);
        }

        switch ($response->status) {
            case 200 :
            case 201 :
            case 204 :
                return $response;
                break;
            case 400 :
                // invalid_grant_error or bad_request_error
                $body = $response->json_body();
                if (strstr($body['error'], 'invalid_grant')) {
                    // Reset access token & refresh_token
                    $this->clear_authentication();
                    throw new PodioInvalidGrantError($response->body, $response->status, $url);
                    break;
                } else {
                    throw new PodioBadRequestError($response->body, $response->status, $url);
                }
                break;
            case 401 :
                $body = $response->json_body();
                if (strstr($body['error_description'], 'expired_token') || strstr($body['error'], 'invalid_token')) {
                    if ($this->oauth->refresh_token) {
                        // Access token is expired. Try to refresh it.
                        if ($this->authenticate('refresh_token', array('refresh_token' => $this->oauth->refresh_token))) {
                            // Try the original request again.
                            return $this->request($method, $original_url, $attributes);
                        } else {
                            $this->clear_authentication();
                            throw new PodioAuthorizationError($response->body, $response->status, $url);
                        }
                    } else {
                        // We have tried in vain to get a new access token. Log the user out.
                        $this->clear_authentication();
                        throw new PodioAuthorizationError($response->body, $response->status, $url);
                    }
                } elseif (strstr($body['error'], 'invalid_request') || strstr($body['error'], 'unauthorized')) {
                    // Access token is invalid.
                    $this->clear_authentication();
                    throw new PodioAuthorizationError($response->body, $response->status, $url);
                }
                break;
            case 403 :
                throw new PodioForbiddenError($response->body, $response->status, $url);
                break;
            case 404 :
                throw new PodioNotFoundError($response->body, $response->status, $url);
                break;
            case 409 :
                throw new PodioConflictError($response->body, $response->status, $url);
                break;
            case 410 :
                throw new PodioGoneError($response->body, $response->status, $url);
                break;
            case 420 :
                throw new PodioRateLimitError($response->body, $response->status, $url);
                break;
            case 500 :
                throw new PodioServerError($response->body, $response->status, $url);
                break;
            case 502 :
            case 503 :
            case 504 :
                throw new PodioUnavailableError($response->body, $response->status, $url);
                break;
            default :
                throw new PodioError($response->body, $response->status, $url);
                break;
        }
        return false;
    }

    public function get($url, $attributes = array(), $options = array())
    {
        return $this->request(self::GET, $url, $attributes, $options);
    }

    public function post($url, $attributes = array(), $options = array())
    {
        return $this->request(self::POST, $url, $attributes, $options);
    }

    public function put($url, $attributes = array())
    {
        return $this->request(self::PUT, $url, $attributes);
    }

    public function delete($url, $attributes = array())
    {
        return $this->request(self::DELETE, $url, $attributes);
    }

    public function curl_headers()
    {
        $headers = array();
        foreach ($this->headers as $header => $value) {
            $headers[] = "{$header}: {$value}";
        }
        return $headers;
    }

    public static function encode_attributes($attributes)
    {
        $return = array();
        foreach ($attributes as $key => $value) {
            $return[] = urlencode($key) . '=' . urlencode($value);
        }
        return join('&', $return);
    }

    public static function url_with_options($url, $options)
    {
        $parameters = array();

        if (isset($options['silent']) && $options['silent']) {
            $parameters[] = 'silent=1';
        }

        if (isset($options['hook']) && !$options['hook']) {
            $parameters[] = 'hook=false';
        }

        if (!empty($options['fields'])) {
            $parameters[] = 'fields=' . $options['fields'];
        }

        return $parameters ? $url . '?' . join('&', $parameters) : $url;
    }

    public static function parse_headers($headers)
    {
        $list = array();
        $headers = str_replace("\r", "", $headers);
        $headers = explode("\n", $headers);
        foreach ($headers as $header) {
            if (strstr($header, ':')) {
                $name = strtolower(substr($header, 0, strpos($header, ':')));
                $list[$name] = trim(substr($header, strpos($header, ':') + 1));
            }
        }
        return $list;
    }

    public function rate_limit_remaining()
    {
        if (!$this->last_response) return -1;
        return $this->last_response->headers['x-rate-limit-remaining'];
    }

    public function rate_limit()
    {
        if (!$this->last_response) return -1;
        return $this->last_response->headers['x-rate-limit-limit'];
    }

    /**
     * Set debug config
     *
     * @param $toggle True to enable debugging. False to disable
     * @param $output Output mode. Can be "stdout" or "file". Default is "stdout"
     */
    public function set_debug($toggle, $output = "stdout")
    {
        if ($toggle) {
            $this->debug = $output;
        } else {
            $this->debug = false;
        }
    }

    public function log_request($method, $url, $encoded_attributes, $response, $curl_info)
    {
        if ($this->debug) {
            $timestamp = gmdate('Y-m-d H:i:s');
            $text = "{$timestamp} {$response->status} {$method} {$url}\n";
            if (!empty($encoded_attributes)) {
                $text .= "{$timestamp} Request body: " . $encoded_attributes . "\n";
            }
            $text .= "{$timestamp} Reponse: {$response->body}\n\n";

            if ($this->debug === 'file') {
                if (!$this->logger) {
                    $this->logger = new PodioLogger();
                }
                $this->logger->log($text);
            } elseif ($this->debug === 'stdout' && php_sapi_name() === 'cli') {
                print $text;
            } elseif ($this->debug === 'stdout' && php_sapi_name() === 'cli') {
                require_once 'vendor/kint/Kint.class.php';
                Kint::dump("{$method} {$url}", $encoded_attributes, $response, $curl_info);
            }

            $this->logger->call_log[] = curl_getinfo($this->ch, CURLINFO_TOTAL_TIME);
        }

    }

    public function shutdown()
    {
        // Log api call times if debugging
        if ($this->debug && $this->logger) {
            $timestamp = gmdate('Y-m-d H:i:s');
            $count = sizeof($this->logger->call_log);
            $duration = 0;
            if ($this->logger->call_log) {
                foreach ($this->logger->call_log as $val) {
                    $duration += $val;
                }
            }

            $text = "\n{$timestamp} Performed {$count} request(s) in {$duration} seconds\n";
            if ($this->debug === 'file') {
                if (!$this->logger) {
                    $this->logger = new PodioLogger();
                }
                $this->logger->log($text);
            } elseif ($this->debug === 'stdout' && php_sapi_name() === 'cli') {
                print $text;
            }
        }
    }


}
