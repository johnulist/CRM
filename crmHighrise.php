<?php
/*
 ****** Class constructor requires 3 arguments!
 */

namespace CRM;

/* 
 * Require Pest library for cleaner REST integration
 */

require_once ('\pest/pestXML.php');
require_once ('sanitize.php');

    
class crmHighrise {

    /* 
     * Define HighriseCRM api server url
     */
    private $crmUrl;
    
    private $connection;
    private $crmUser;
    
    private $loginSuccess;

    
    /* Constructor
     * @param string $login      - User login
     * @param string $password
     * @param string $account    -  Highrise account name
     */
    
    public function __construct($login, $password, $account) {
        
        /*
         *  Using format_uri to sanitize malicious strings
         */
        
        $this->crmUrl = 'https://'.format_uri($account).'.highrisehq.com';
        $this->connection = new \PestXML($this->crmUrl);
        
        /*
         *  Setting up HTTP basic authentication
         */
        
        $this->connection->setupAuth($login, $password);
        
        try {
            $this->crmUser = $this->connection->get('/me.xml');
            $this->loginSuccess = true;
        } catch(\Exception $e) {      
            $this->loginSuccess = false;
            echo 'Login failed! ', $e->getMessage();
        }

    }
    
    /* addContact: Adding contact to HighriseCRM;
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
         * Check for successful login and available auth token 
         * before sending any api calls, otherwise terminate
         */
        
        if(($this->loginSuccess == true) && isset($this->crmUser->token)) {
            
            /* 
             * Prepare the data representing the new contact
             */
            
            $contact = array(
                'person' => array(
                    'first_name' => $params[0]
                )
            );            
            
            if(isset($params[1]) && ($params[1] != '')) {
                $contactEmail = array(
                    'email_address' => array(
                        'address' => $params[1],
                        'location' => 'Work'
                    )
                );
                $contact['person']['contact_data']['email_addresses'] = $contactEmail;
            }
            if(isset($params[2]) && ($params[2] != '')) {
                $contactPhone = array(
                    'phone_number' => array (
                         'number' => $params[2],
                         'location' => 'Work'
                    ) 
                );
                $contact['person']['contact_data']['phone_numbers'] = $contactPhone;
            }
            
            
            /*
             * Prepare the authentication token for HTTP basic auth
             * of the request; Highrise doesn't require passwords 
             * at this point, using dummy 'X'
             */
            
            $this->connection->setupAuth($this->crmUser->token, 'X');
            
            /* 
             * Send the data to the HighriseCRM server
             */
            
            try {
                $result = $this->connection->post('/people.xml', $contact);
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }
            
            /* Return data: success status and/or error message */
        
            $addResult = array();
            if(isset($result->{'created-at'})) {
                $addResult[0] = true;
                $addResult[1] = '';
            } else {
            /* 
             * This condition should't occur, with
             * Highrise any errors should be caught 
             * at the exception handling stage
             */
                $addResult[0] = false;
                $addResult[1] = 'An unexpected error ocured';
            }
            
            
            
        } else {
          $addResult = array(false,'Login unsuccessful, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
