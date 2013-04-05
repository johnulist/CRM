<?php

namespace CRM;

/* 
 * Require Pest library for cleaner REST integration
 */

require_once ('\pest/pestXML.php');
    
class crmZoho {

    /* 
     * Define ZohoCRM api server url
     */
    private $crmUrl;
    private $authToken;
    private $scope;
    
    private $connection;
    private $crmUser;
    
    /* Constructor
     * @param string $login      - User login
     * @param string $password
     * @param string $authtoken  - optional, a new one will be
     *  generated if null or empty string is given
     */
    
    public function __construct($login, $password, $authToken = null) {
        
        /*
         * Check for authtoken, if null or empty string a new auth token 
         * will be generated (any old ones remain valid!)
         */
        
        if(isset($authToken) && ($authtoken != '')) {
            $this->authToken = $authToken;   
        } else {
            $genToken = $this->generateAuthToken($login, $password);
            if($genToken[0]) {
                $this->authToken = $genToken[1];
            }
        }
        
        /*
         * Set target url for REST requests
         */
        
        $this->crmUrl = 'https://crm.zoho.com/crm/private/xml/Contacts';
        $this->scope = 'crmapi';
        
        $this->connection = new \PestXML($this->crmUrl);        

    }
    
    /*
     * Generate an auth token if necessary
     */
    
    private function generateAuthToken($login, $password) {
        
        /*
         * Set scope and REST url for token generation
         */
        
        $scope = 'ZohoCRM/crmapi';
        $authUrl = 'https://accounts.zoho.com/apiauthtoken/nb';
        
        $authConnection = new \Pest($authUrl);
        
        
        /*
         * Prepare required data - user login, password and scope
         */
        
        $data = array(
            'SCOPE' => $scope,
            'EMAIL_ID' => $login,
            'PASSWORD' => $password
        );
                
        /*
         * Send data to Zoho server, response contains new auth token
         */
        
        try {
            $result = $authConnection->post('/create', $data);
        } catch (\Exception $e) {
            return array(false, $e->getMessage());
        }

        /*
         * Get the auth token from the response string format:
         * {
         * One line of text \n
         * Second line of text \n
         * AUTHTOKEN={actualauthToken} \n
         * Another line of text \n
         * }
         */
        
        $result = explode("\n", $result);
        $authToken = explode("=", $result[2]);
        if(strcmp($authToken[0], 'AUTHTOKEN') == 0) {
            return array(true, $authToken[1]);
        } else {
            return array(false, $authToken[1]);
        }
        
        
    }
    
    
    /* addContact: Adding contact to HighriseCRM;
     * 
     * @params array $params    array of 3:
     *  $params[0]: string          contact name, required
     *  $params[1]: string          contact email
     *  $params[2]: string          contact phone
     * 
     * @return array $addResult    Returns an array of 2;
     *  $addResult[0]: bool         true on operation success
     *  $addResult[1]: string       error/success message, if any 
     * 
     */
    
    public function addContact(array $params) {
        
        /* 
         * Check for successful login and available auth token 
         * before sending any api calls, otherwise terminate
         */
        
        if(isset($this->authToken)) {
                       
            /* 
             * Prepare the data representing the new contact
             */
            
            if(!isset($params[1])) {
                $params[1] = '';
            }
            if(!isset($params[2])) {
                $params[2] = '';
            }
            
            $data = array(
                'authtoken' => $this->authToken,
                'scope' => $this->scope,
                'newFormat' => 1,
                'xmlData' => '<Contacts><row no="1"><FL val="Last Name">'.$params[0].'</FL><FL val="Email">'
                    .$params[1].'</FL><FL val="Phone">'.$params[2].'</FL></row></Contacts>'
            );
            
            /* 
             * Send the data to the ZohoCRM server
             */
            
            try {
                $result = $this->connection->post('/insertRecords', $data);
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }
            
            /* Return data: success status and/or error message */
        
            $addResult = array();
            if(isset($result->result)) {
                $addResult[0] = true;
                $addResult[1] = $result->result->message;
            } elseif (isset($result->error)){
                $addResult[0] = false;
                $addResult[1] = $result->error->message;
            }
            
            
            
        } else {
          $addResult = array(false,'Login unsuccessful, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
