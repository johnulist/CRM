<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * 
 */

require_once ('\pest/pestJSON.php');

    
class crmBase {

    /* Define BaseCRM api server url
     * 
     */
    private $crmUrl = 'https://sales.futuresimple.com/api/v1';
    
    private $connection;
    private $crmUser;
    
    private $loginSuccess;

    
    /* Constructor
     * @param string login
     * @param string password
     */
    
    public function __construct($login, $password) {
        
        
        $this->connection = new \PestJSON($this->crmUrl);
        
        $loginData = array(
            'email' => $login,
            'password' => $password
        );
        
        try {
            $this->crmUser = $this->connection->post('/authentication.json', $loginData);
            $this->loginSuccess = true;
        } catch(Exception $e) {
            $this->loginSuccess = false;
            echo 'Login failed! ', $e->getMessage();
        }
        

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
        
        /* Check for successful login before sending any api calls */
        
        if(isset($this->loginSuccess) && ($this->loginSuccess == true)) {
            
            /* 
             * Prepare an array representing the new contact
             */
            
            if(!isset($params[1])) {
                $params[1] = '';
            }
            if(!isset($params[2])) {
                $params[2] = '';
            }
            
            $contact = array(
                'contact' => array(
                    'last_name' => $params[0],
                    'email' => $params[1],
                    'phone' => $params[2]
                )
            );
            
            
            /* 
             * Prepare authentication data for BaseCRM
             */
            
            $crmAuthToken = $this->crmUser['authentication']['token'];
            
            /* 
             * Prepare headers to send to the server
             */
            
            $headers = array(
                '0' => 'X-Pipejump-Auth: '.$crmAuthToken,
            );
            
            
            /* 
             * Send the data to the BaseCRM server
             */
            
            try {
                $result = $this->connection->post('/contacts.json', $contact, $headers);
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }
            
            /* Return data: success status and/or error message */
        
            $addResult = array();
            if(isset($result['contact'])) {
                $addResult[0] = true;
                $addResult[1] = '';
            } else {
                $addResult[0] = false;
                $addResult[1] = $result['errors']['contact']['error']['description'];
            }
            
            
            
        } else {
          $addResult = array(false,'Login unsuccessful, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
