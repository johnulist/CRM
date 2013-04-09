<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * and sanitize.php for account name in url sanitization
 */

require_once ('\pest/pestXML.php');
require_once ('sanitize.php');

    
class crmAmo {

    private $crmUrl;
    private $connection;
    
    private $login;
    private $apiKey;
           
    private $crmUser;
    
    /* Constructor
     * @param string $login
     * @param string $apiKey
     * @param string $account  - account name (part of url between "http://" and ".amocrm.com")
     */
    
    public function __construct($login, $apiKey, $account) {
      
        $this->crmUrl = 'https://'.format_uri($account).'.amocrm.com';
        $this->connection = new \PestXML($this->crmUrl);
        
        /*
         * Saving auth details for later
         */
        
        $this->login = $login;
        $this->apiKey = $apiKey;
        
        $loginData = array(
           'USER_LOGIN' => $login,
           'USER_HASH' => $apiKey
        );
        
        /*
         * Checking whether auth details are valid
         */
        
        try {
            $this->crmUser = $this->connection->post('/private/api/auth.php', $loginData);
        } catch (\Exception $e) {
            echo $e->getMessage();
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
        
        /*
         * Was the auth valid during construction phase?
         */
        
        if(isset($this->crmUser->auth) && $this->crmUser->auth = 'true') {
        
            /* 
             * Prepare an array representing the new contact
             * along with the auth details
             */

            $contact = array(
                'USER_LOGIN' => $this->login,
                'USER_HASH' => $this->apiKey,
                'ACTION' => 'ADD_PERSON',    
                'contact' => array(
                    'person_name' => $params[0]
                )
            );

            if(isset($params[1]) && ($params[1] != '')) {
                $contact['contact']['contact_data']['email_addresses'][0]['address'] = $params[1];
                $contact['contact']['contact_data']['email_addresses'][0]['location'] = 'Work';
            }
            if(isset($params[2]) && ($params[2] != '')) {
                $contact['contact']['contact_data']['phone_numbers'][0]['number'] = $params[2];
                $contact['contact']['contact_data']['phone_numbers'][0]['location'] = 'Work';
            }

            /* 
             * Send the data to the AmoCRM server
             */

            try {
                $result = $this->connection->post('/private/api/contact_add.php', $contact);
             } catch (\Exception $e) {
                 return array(false, $e->getMessage());
             }

            /* Return data: success status and/or error message */

            if(isset($result->result)) {
                $addResult = array(true, 'Contact added! ');
            } else {
                $addResult = array(false, 'An unexpected error occured! ');
            }

            return $addResult;

        } else {
            return array(false, 'Login failed. Unable to add contacts!');
        }
    }
}
?>
