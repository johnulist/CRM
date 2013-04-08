<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * And sanitize.php to exclude malicious $account passed
 * to the constructor
 */

require_once ('\pest/pestJSON.php');
require_once ('sanitize.php');

    
class crmCapsule {

    /* Define CapsuleCRM api server url
     * 
     */
    private $crmUrl;
    
    private $connection;
    private $apiKey;

    
    /* Constructor
     * @param string $account   - account URL (not login! - https://account_name.capsulecrm.com)
     * @param string $apiKey
     */
    
    public function __construct($account, $apiKey) {
        
        $this->crmUrl = 'https://'.format_uri($account).'.capsulecrm.com';
        $this->connection = new \PestJSON($this->crmUrl);
        $this->apiKey = $apiKey;
        
        /*
         * Set up HTTP Basic authentication with a dummy password
         * Capsule requires only the apiKey
         */
        
        $this->connection->setupAuth($apiKey, 'X');
        
    }
    
    /* addContact: Adding contact to CapsuleCRM;
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
        
        if(isset($this->apiKey)) {
            
            /* 
             * Prepare an array representing the new contact
             */
           
            $contact = array(
                'person' => array(
                    'lastName' => $params[0],
                )
            );
            
            if(isset($params[1]) && ($params[1] != '')) {
                $contact['person']['contacts']['email'] = array (
                    'type' => 'Work',
                    'emailAddress' => $params[1]
                );
            }
            if(isset($params[2]) && ($params[2] != '')) {
                $contact['person']['contacts']['phone'] = array (
                    'type' => 'Work',
                    'phoneNumber' => $params[2]
                );
            }            
                   
            /* 
             * Send the data to the CapsuleCRM server
             */
            
            try {
                $result = $this->connection->post('/api/person', $contact);
            } catch (\Exception $e) {
                $msg = json_decode($e->getMessage());
                return array(false, $msg->message);
            }
            
            /* Return data: success status and/or error message */
        
            $addResult = array(true, 'Contact created! ');

    
        } else {
          $addResult = array(false,'Api Key required, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
