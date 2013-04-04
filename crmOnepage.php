<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * 
 */

require_once ('\pest/pestJSON.php');

    
class crmOnepage {

    /* Define Onepage api server url
     * 
     */
    private $crmUrl = 'https://app.onepagecrm.com/api';
    
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
            'login' => $login,
            'password' => $password
        );
        
        try {
            $this->crmUser = $this->connection->post('/auth/login.json', $loginData);
            $this->loginSuccess = true;
        } catch(Exception $e) {
            $this->loginSuccess = false;
            echo 'Login failed! ', $e->getMessage();
        }
        

    }
    
    /* addContact: Adding contact to Salesforce;
     * 
     * @params array params    array of 3:
     * params[0]: string          contact name, required
     * params[1]: string          contact email
     * params[2]: string          contact phone
     * 
     * @return array addResult    Returns an array of 2;
     * addResult[0]: bool         true on operation success
     * addResult[1]: string       error message, if any 
     * 
     */
    
    public function addContact(array $params) {
        
        /* Check for successful login before sending any api calls */
        
        if(isset($this->loginSuccess) && ($this->loginSuccess == true)) {
            
            /* 
             * Prepare an array representing the new contact
             */
            
            $contact = array(
                'company' => $params[0]
            );
            
            
            /* 
             * Prepare authentication data for Onepage
             */
            
            $crmUserId = $this->crmUser['data']['uid'];
            $crmRawBody = http_build_query($contact);
            $crmFullUrl = $this->crmUrl.'/contacts.json';
            var_dump($crmFullUrl);
            $crmApiKey = $this->crmUser['data']['key'];
            $crmTimestamp = time();
            
            /* 
             * Prepare the authentication string and hash it 
             * to receive the auth key required by Onepage
             */
            
           
            $authString = $crmUserId.'.'.$crmTimestamp.'.POST.'.sha1($crmFullUrl).'.'.sha1($crmRawBody);     
            
            /* 
             * Prepare headers to send to the server
             */
            var_dump($authString);
            $headers = array(
                '0' => 'X-OnePageCRM-UID: '.$crmUserId,
                '1' => 'X-OnePageCRM-TS: '.$crmTimestamp,
                '2' => 'X-OnePageCRM-Auth: '.hash_hmac('sha256', $authString, $crmApiKey)
            );
            
            
            /* 
             * Send the data to the Onepage server
             */
            
            try {
                $result = $this->connection->post('/contacts.json', $contact, $headers);
            } catch (Exception $e) {
                return array(false, $e->getMessage());
            }
            
            /* Return data: success status and/or error message */
        
            $addResult = array();
            $addResult[1] = $result['data']['message'];
            if($result['data']['status'] == 0) {
                $addResult[0] = true;
            } else {
                $addResult[0] = false;
            }
            
        } else {
          $addResult = array(false,'Login unsuccessful, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
