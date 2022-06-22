<?php
namespace cPanel;

set_time_limit(0);

/**
 * Class API
 * @package UptimeRobot
 */
class API{
    use cPanelFunctions;

    private $whm_user;
    private $whm_token;
    private $whm_password;

    public $whm_server;
    public $auth;

    private $contents;
    private $args;
    private $options;

    public $debug;

    public function __construct(){
        if (empty(env('WHM_HOST'))) {
            throw new \Exception('Servidor não configurado.');
        }

        if (empty(env('WHM_USER'))) {
            throw new \Exception('Usuário não configurado.');
        }

        if (empty(env('WHM_TOKEN'))) {
            throw new \Exception('API Token não configurado.');
        }

        $this->setUser(env('WHM_USER'));
        $this->setHost(env('WHM_HOST'));
        $this->setToken(env('WHM_TOKEN'));
    }

    public function setUser($username){
        $this->whm_user = $username;
    }

    public function setHost($host){
        $this->whm_server = $host;
    }

    public function setToken($token){
        $this->whm_token = $token;
    }

    private function query($param = '', $args = array())
    {        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);

        $header[0] = "Authorization: whm {$this->whm_user}:{$this->whm_token}";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);  
        curl_setopt($curl, CURLOPT_URL, $this->buildUrl($param, $args));  

        $result = curl_exec($curl);
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_status != 200) {
            echo "[!] Error: {$http_status} returned\n";
        } else {
            return json_decode($result);
        }
    }

    private function buildUrl($resource, $args)
    {
        $query = http_build_query($args);
        return "https://{$this->whm_server}:2087/json-api/{$resource}?{$query}";
    }

    /**
     * Sets debug information from last curl.
     *
     * @param resource $curl Curl handle
     */
    private function setDebug($curl)
    {
        $this->debug = [
            'errorNum' => curl_errno($curl),
            'error' => curl_error($curl),
            'info' => curl_getinfo($curl),
            'raw' => $this->contents,
        ];
    }
}
