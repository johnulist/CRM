<?php

namespace CRM;

class crmNutshell {

    /* 
     * Define InsightlyCRM api server url
     */
    
    private $crmUrl;  
    private $connection;

    private $login;
    private $apiKey;
    
    /* Constructor
     * @param string $login  (Nutshell company name or user email)
     * @param string $apiKey
     */
    
    public function __construct($login, $apiKey) {
        
        /*
         * Pull the wsdl method list and build the proper url
         * based on user name
         */
        
        try {
            $connection = new \SoapClient('http://api.nutshell.com/v1/soap?wsdl');
            $domains = $connection->getApiForUsername($login);
        } catch (\SoapFault $e) {
            throw new \Exception($e->getMessage());
        }
        $this->crmUrl = 'https://'.$domains['api'].'/api/v1/soap';
        
        /*
         * Save the login and api key for authentication later
         */
        
        $this->login = $login;
        $this->apiKey = $apiKey;
        
    }
    
    /* addContact: Adding contact to BaseCRM;
     * 
     * @params array $params    array of 3:
     * $params[0]: string          contact name, required
     * $params[1]: string          contact email
     * $params[2]: string          contact phone
     * 
     * @return array $addResult    Returns an array of 2;
     * $addResult[0]: bool         true on operation success
     * $addResult[1]: string       error message, if any 
     * 
     */
    
    public function addContact(array $params) {
        
        /*
         * Create a new SOAP connection to the user url 
         * built at construction stage, use login and 
         * api key for HTTP basic authentication
         */
        
        $this->connection = new \SoapClient($this->crmUrl.'?wsdl', array('login' => $this->login, 'password' => $this->apiKey));

        /* 
         * Prepare an array representing the new contact
         */
        
        $contact = array();
        if(isset($params[0]) && ($params[0] != '')) {
            $contact['name'] = $params[0];
        }        
        if(isset($params[1]) && ($params[1] != '')) {
            $contact['email'] = $params[1];
        }
        if(!isset($params[2]) && ($params[2] != '')) {
            $contact['phone'] = $params[2];
        }
        
        /* 
         * Send the data to the InsightlyCRM server
         */
        
        try {
            $result = $this->connection->newContact($contact);
        } catch (\Exception $e) {
            return array(false, $e->getMessage());
        }
            
        /* Return data: success status and/or error message */
        
        $addResult = array();
        if(isset($result['entityType']) && ($result['entityType'] == 'Contacts')) {
            $addResult[0] = true;
            $addResult[1] = 'Contact created!';
        } else {
            $addResult[0] = false;
            $addResult[1] = 'An unexpected error occured';
        }
        
        return $addResult;
        
    }
    
}


?>
