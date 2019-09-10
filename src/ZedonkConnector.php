<?php

namespace ZedonkAPI;

class ZedonkConnector
{
    protected $url, $key, $user, $password, $schema, $api, $lastCall;

    public function __construct($url, $key, $user, $password)
    {
        $this->url = $url.'/API/REST/';
        $this->key = $key;
        $this->user = $user;
        $this->password = $password;
        $this->schema = false;
        $this->api = 'ExecuteDataAPI';
    }

    /**
     * Builds the api url for a call
     * 
     * @param  string $apiCall
     * @param  string $filters
     * @return string
     */
    public function makeUrl($apiCall, $filters)
    {
        $schemaParam = $this->getSchemaParameter();
        return $this->url.$this->api.'?dataAPIName='.$apiCall.'&username='.$this->user.'&password='.$this->password.'&key='.$this->key.$schemaParam.$filters;
    }

    /**
     * sets the api to use
     * 
     * @param string $api
     */
    public function setApi($api)
    {
        $this->api = $api;
    }

    /**
     * Get the schema parameter
     * 
     * @return string
     */
    protected function getSchemaParameter()
    {
        if($this->schema){
            return '&includeSchema=true';
        }
        return '&includeSchema=false';
    }

    /**
     * Gets the latest call made to the api.
     * 
     * @return string
     */
    public function getLastCall()
    {
        return $this->lastCall;
    }

    /**
     * Makes an api call using curl, returns response
     * 
     * @param  string $apiCall
     * @param  string $filters
     * @return string
     */
    public function call($apiCall, $filters)
    {    
        $service_url = $this->makeUrl($apiCall, $filters);
        $this->lastCall = $service_url;
        $curl = curl_init($service_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        $info = curl_getinfo($curl);
        if ($curl_response === false) {
            curl_close($curl);
            throw ZedonkAPIException::connection($apiCall);
        }
        else if($info['http_code'] != 200){
            curl_close($curl);
            throw ZedonkAPIException::apiHttpError($apiCall, $info);
        }
        curl_close($curl);
        return $curl_response;
    }

    /**
     * Include schema in api calls
     */
    public function includeSchema()
    {
        $this->schema = true;
    }

    /**
     * Exclude schema in api calls
     */
    public function excludeSchema()
    {
        $this->schema = false;
    }
}