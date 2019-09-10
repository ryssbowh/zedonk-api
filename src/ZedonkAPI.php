<?php

namespace ZedonkAPI;

class ZedonkAPI
{
    /**
     * This class instance
     * @var ZedonkAPI
     */
    private static $instance = null;

    /**
     * default filters
     * @var array
     */
    private $filters = [];

    /**
     * API connector
     * @var ZedonkConnector
     */
    private $connector;

    protected function __construct(){}

    protected function __clone(){}

    /**
     * Get this class instance
     * @return ZedonkAPI
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Sets a default filter
     * 
     * @param string $name
     * @param string $operator
     * @param string $value
     */
    public function addDefaultFilter($filter, $operator)
    {
        $this->filters[] = [$filter, $operator];
    }

    /**
     * Resets filters
     */
    public function resetFilters()
    {
        $this->filters = [];
    }

    /**
     * Set credentials for api calls
     * 
     * @param string $url
     * @param string $key
     * @param string $user
     * @param string $password
     */
    public static function setCredentials($url, $key, $user, $password)
    {
        $instance = static::getInstance();
        $instance->setConnector(new ZedonkConnector($url, $key, $user, $password));
    }

    /**
     * Gets the connector
     * 
     * @return ZedonkConnector
     */
    public function getConnector()
    {
        if(is_null($this->connector)){
            throw ZedonkAPIException::connectorNotSet();
        }
        return $this->connector;
    }

    /**
     * Sets the connector
     * 
     * @param ZedonkConnector $connector
     */
    public function setConnector($connector)
    {
        $this->connector = $connector;
    }

    /**
     * Set the season for all future calls. 
     * If not set it will use Zedonk default one for the report you're calling
     * 
     * @param ?string $season
     */
    public static function setSeason($season = null)
    {
        $instance = static::getInstance();
        $instance->addDefaultFilter('season = \''.trim($season)."'", 'and');
        $instance->getConnector()->setApi('SeasonNameExecuteDataAPI');
    }

    /**
     * Set the API to use, could be 'SeasonNameExecuteDataAPI' (if you're adding season as a filter) or 'ExecuteDataAPI' to use the default season
     * 
     * @param string $api
     */
    public static function setApi($api)
    {
        $instance = static::getInstance();
        $instance->getConnector()->setApi($api);
    }

    /**
     * Turns an array of filters into a string
     * 
     * @param  array $filters
     * @return string
     */
    protected function makeFilters($filters)
    {
        if($filters){
            $str = '&filter=1%3D1';
            foreach($filters as $filter){
                $strFilter = ' '.$filter[1].' ('.$filter[0].')';
                $str .= rawurlencode($strFilter);
            }
            return $str;
        }
        return '';
    }

    /**
     * Makes an api call, returns objects fetched
     * 
     * @param  string $apiName
     * @param  array $filters
     * @return array|false
     * @throws ZedonkAPIException
     */
    protected function makeCall($apiName, $filters)
    {
        if(is_null($this->connector)){
            throw ZedonkAPIException::connectorNotSet();
        }
        $filters = array_merge($filters, $this->filters);
        $filters = $this->makeFilters($filters);
        try{
            $data = $this->connector->call($apiName, $filters);
            $xml = new \SimpleXMLElement($data);
        }
        catch(\Exception $e){
            return false;
        }
        if($xml->code != "200"){
            throw ZedonkAPIException::apiError($apiName, $xml->description);
        }
        return $this->xmlToEntities($xml);
    }

    /**
     * Turns a xml iterator into an array
     * 
     * @param SimpleXmlIterator $sxi
     * @return array
     */
    protected function sxiToArray($sxi){
        $a = [];
        for( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
            if($sxi->hasChildren()){
                $a[$sxi->key()] = $this->sxiToArray($sxi->current());
            }
            else{
                $a[$sxi->key()] = strval($sxi->current());
            }
        }
        return $a;
    }

    /**
     * Turns a Zedonk returned xml object into an array
     * 
     * @param  SimpleXMLElement $xml
     * @return array
     */
    protected function xmlToEntities($xml)
    {
        $entities = [];
        foreach($xml->Data->Reports as $report){
            $sxi = new \SimpleXmlIterator($report->asXml());
            $entities[] = new ZedonkEntity($this->sxiToArray($sxi));
        }
        return $entities;
    }

    /**
     * Include schema in api calls
     */
    public static function includeSchema()
    {
        $instance = static::getInstance();
        $instance->getConnector()->includeSchema();
    }

    /**
     * Exclude schema in api calls
     */
    public static function excludeSchema()
    {
        $instance = static::getInstance();
        $instance->getConnector()->excludeSchema();
    }

    public static function call($name, $filters = [])
    {
        $instance = static::getInstance();
        return $instance->makeCall($name, $filters);
    }

    /**
     * get products
     * 
     * @param  array  $filters
     * @return array
     */
    public static function getProducts($filters = [])
    {
        $instance = static::getInstance();
        return $instance->makeCall('FullProductList', $filters);
    }

    /**
     * get customers
     * 
     * @param  array  $filters
     * @return array
     */
    public static function getCustomers($filters = [])
    {
        $instance = static::getInstance();
        return $instance->makeCall('CustomerList', $filters);
    }

    /**
     * get orders
     * 
     * @param  array  $filters
     * @return array
     */
    public static function getOrders($filters = [])
    {
        $instance = static::getInstance();
        return $instance->makeCall('FullOrderList', $filters);
    }

    /**
     * get inventory
     * 
     * @param  array  $filters
     * @return array
     */
    public static function getInventory($filters = [])
    {
        $instance = static::getInstance();
        return $instance->makeCall('ProductInventory', $filters);
    }

    /**
     * Gets the latest call made to the api.
     * 
     * @return string
     */
    public static function getLastCall()
    {
        $instance = static::getInstance();
        return $instance->getConnector()->getLastCall();
    }
}