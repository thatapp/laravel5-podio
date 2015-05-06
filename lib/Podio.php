<?php

use Illuminate\Support\Facades\Session;

class Podio
{
    public $oauth, $debug, $logger, $session_manager, $last_response, $auth_type;
    protected $url, $client_id, $client_secret, $secret, $ch, $headers;

    const VERSION = '4.0.3';

    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    private static $clients = array();

    public static function FromSession($client_id = null, $client_secret = null, $options = array('save_session' => true, 'curl_options' => array()))
    {
        if (!Session::has('podio-entry') || !isset(self::$clients[Session::get('podio-entry')])) {
            Session::put('podio-entry', $client_id);
            self::$clients[$client_id] = new Podio($client_id, $client_secret, $options);
        }
        return self::$clients[Session::get('podio-entry')];
    }


    public function __construct($client_id, $client_secret, $options = array('save_session' => true, 'curl_options' => array()))
    {
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

        if ($options && !empty($options['curl_options'])) {
            curl_setopt_array($this->ch, $options['curl_options']);
        }

        if ($options && !empty($options['save_session']) && $options['save_session']) {
            $this->session_manager = new PodioSessionManager();
            $this->oauth = $this->session_manager->get();
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

    public function refresh_access_token()
    {
        return $this->authenticate('refresh_token', array('refresh_token' => $this->oauth->refresh_token));
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
            $this->oauth = new PodioOAuth($body['access_token'], $body['refresh_token'], $body['expires_in'], $body['ref']);

            // Don't touch auth_type if we are refreshing automatically as it'll be reset to null
            if ($grant_type !== 'refresh_token') {
                $this->auth_type = $auth_type;
            }

            if ($this->session_manager) {
                $this->session_manager->set($this->oauth, $this->auth_type);
            }

            return true;
        }
        return false;
    }

    public function clear_authentication()
    {
        $this->oauth = new PodioOAuth();

        if ($this->session_manager) {
            $this->session_manager->set($this->oauth, $this->auth_type);
        }
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

    public static function request($method, $url, $attributes = array(), $options = array())
    {
        $podio = Podio::FromSession();

        if (!$podio || !$podio->ch) {
            throw new Exception('Client has not been setup with client id and client secret.');
        }

        // Reset attributes so we can reuse curl object
        curl_setopt($podio->ch, CURLOPT_POSTFIELDS, null);

        unset($podio->headers['Content-length']);
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
                curl_setopt($podio->ch, CURLOPT_CUSTOMREQUEST, self::GET);
                $podio->headers['Content-type'] = 'application/x-www-form-urlencoded';

                $separator = strpos($url, '?') ? '&' : '?';
                if ($attributes) {
                    $query = Podio::encode_attributes($attributes);
                    $url = $url . $separator . $query;
                }

                $podio->headers['Content-length'] = "0";
                break;
            case self::DELETE:
                curl_setopt($podio->ch, CURLOPT_CUSTOMREQUEST, self::DELETE);
                $podio->headers['Content-type'] = 'application/x-www-form-urlencoded';

                $separator = strpos($url, '?') ? '&' : '?';
                if ($attributes) {
                    $query = Podio::encode_attributes($attributes);
                    $url = $url . $separator . $query;
                }

                $podio->headers['Content-length'] = "0";
                break;
            case self::POST:
                curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, self::POST);
		if (!empty($options['upload'])) {
		  curl_setopt(self::$ch, CURLOPT_POST, TRUE);
		  curl_setopt(self::$ch, CURLOPT_SAFE_UPLOAD, FALSE);
		  curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $attributes);
		  self::$headers['Content-type'] = 'multipart/form-data';
		}
		elseif (empty($options['oauth_request'])) {
		  // application/json
		  $encoded_attributes = json_encode($attributes);
		  curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $encoded_attributes);
		  self::$headers['Content-type'] = 'application/json';
		}
		else {
		  // x-www-form-urlencoded
		  $encoded_attributes = self::encode_attributes($attributes);
		  curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $encoded_attributes);
		  self::$headers['Content-type'] = 'application/x-www-form-urlencoded';
		}
		break;
            case self::PUT:
                $encoded_attributes = json_encode($attributes);
                curl_setopt($podio->ch, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($podio->ch, CURLOPT_POSTFIELDS, $encoded_attributes);
                $podio->headers['Content-type'] = 'application/json';
                break;
        }

        // Add access token to request
        if (isset($podio->oauth) && !empty($podio->oauth->access_token) && !(isset($options['oauth_request']) && $options['oauth_request'])) {
            $token = $podio->oauth->access_token;
            $podio->headers['Authorization'] = "OAuth2 {$token}";
        } else {
            unset($podio->headers['Authorization']);
        }

        // File downloads can be of any type
        if (empty($options['file_download'])) {
            $podio->headers['Accept'] = 'application/json';
        } else {
            $podio->headers['Accept'] = '*/*';
        }

        curl_setopt($podio->ch, CURLOPT_HTTPHEADER, $podio->curl_headers());
        curl_setopt($podio->ch, CURLOPT_URL, empty($options['file_download']) ? $podio->url . $url : $url);

        $response = new PodioResponse();
        $raw_response = curl_exec($podio->ch);
        $raw_headers_size = curl_getinfo($podio->ch, CURLINFO_HEADER_SIZE);
        $response->body = substr($raw_response, $raw_headers_size);
        $response->status = curl_getinfo($podio->ch, CURLINFO_HTTP_CODE);
        $response->headers = Podio::parse_headers(substr($raw_response, 0, $raw_headers_size));
        $podio->last_response = $response;

        if (!isset($options['oauth_request'])) {
            $curl_info = curl_getinfo($podio->ch, CURLINFO_HEADER_OUT);
            $podio->log_request($method, $url, $encoded_attributes, $response, $curl_info);
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
                    $podio->clear_authentication();
                    throw new PodioInvalidGrantError($response->body, $response->status, $url);
                    break;
                } else {
                    throw new PodioBadRequestError($response->body, $response->status, $url);
                }
                break;
            case 401 :
                $body = $response->json_body();
                if (strstr($body['error_description'], 'expired_token') || strstr($body['error'], 'invalid_token')) {
                    if ($podio->oauth->refresh_token) {
                        // Access token is expired. Try to refresh it.
                        if ($podio->authenticate('refresh_token', array('refresh_token' => $podio->oauth->refresh_token))) {
                            // Try the original request again.
                            return Podio::request($method, $original_url, $attributes);
                        } else {
                            $podio->clear_authentication();
                            throw new PodioAuthorizationError($response->body, $response->status, $url);
                        }
                    } else {
                        // We have tried in vain to get a new access token. Log the user out.
                        $podio->clear_authentication();
                        throw new PodioAuthorizationError($response->body, $response->status, $url);
                    }
                } elseif (strstr($body['error'], 'invalid_request') || strstr($body['error'], 'unauthorized')) {
                    // Access token is invalid.
                    $podio->clear_authentication();
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

    public static function get($url, $attributes = array(), $options = array())
    {
        return Podio::request(self::GET, $url, $attributes, $options);
    }

    public static function post($url, $attributes = array(), $options = array())
    {
        return Podio::request(self::POST, $url, $attributes, $options);
    }

    public static function put($url, $attributes = array())
    {
        return Podio::request(self::PUT, $url, $attributes);
    }

    public static function delete($url, $attributes = array())
    {
        return Podio::request(self::DELETE, $url, $attributes);
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
        return $this->last_response->headers['x-rate-limit-remaining'];
    }

    public function rate_limit()
    {
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
        // Write any new access and refresh tokens to session.
        if ($this->session_manager) {
            $this->session_manager->set($this->oauth, $this->auth_type);
        }

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
