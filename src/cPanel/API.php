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
    private $whm_hash;
    private $whm_password;

    public $whm_server;
    public $auth;

    private $contents;
    private $args;
    private $options;

    public $debug;

    public function __construct($whmHost = '', $whmUser = '', $whmHash = ''){
        if (empty($whmHost)) {
            throw new Exception('Servidor não configurado.');
        }

        if (empty($whmUser)) {
            throw new Exception('Usuário não configurado.');
        }

        if (empty($whmHash)) {
            throw new Exception('Remote Key não configurado.');
        }

        $this->setUser($whmUser);
        $this->setHost($whmHost);
        $this->setHash($whmHash);
    }

    public function setUser($username){
        $this->whm_user = $username;
    }

    public function setHost($host){
        $this->whm_server = $host;
    }

    public function setHash($hash){
        $this->whm_hash = $hash;
    }

    private function query($param = '', $args = array())
    {        
        $curl = curl_init();        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 10000);
        //curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, 10000);
        //$header[0] = "Authorization: Basic " . base64_encode($config['whm_user'].":".$config['whm_password']) . "\n\r";
        $header[0] = "Authorization: WHM ".$this->whm_user.":" . preg_replace("'(\r|\n)'","",$this->whm_hash);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);  
        curl_setopt($curl, CURLOPT_URL, $this->buildUrl($param, $args));  

        $result = curl_exec($curl);
        curl_close($curl);

        if ($result == false) {
            return [
                'status' => 0,
                'error' => 'conn_error',
                'verbose' => 'Verifique IP ou Hostname.'
            ];
        }else{
            return json_decode($result);
        }        
    }

    private function buildUrl($resource, $args)
    {
        //Merge args(apiKey, Format, noJsonCallback)
        $query = http_build_query($args);

        $url = $this->whm_server.":2087/json-api/";
        $url .= $resource . '?' . $query;

        return $url;
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
