<?php

namespace CRM;

/* 
 * Require Pest library for cleaner REST integration
 */

require_once ('\pest/pestJSON.php');

    
class crmInsightly {

    /* 
     * Define InsightlyCRM api server url
     */
    
    private $crmUrl = 'https://api.insight.ly/v1';
    
    private $connection;
    private $crmUser;
    
    /* Constructor
     * @param string $apiKey
     */
    
    public function __construct($apiKey) {
        
        $this->connection = new \PestJSON($this->crmUrl);
        $this->connection->setupAuth(base64_encode($apiKey), 'X');
        
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
         * Prepare an array representing the new contact
         */
            
        $contact = array(
            'last_name' => $params[0],
            'visible_to' => 'everyone'
        );
        
        if(isset($params[1]) && ($params[1] != '')) {
            $contact['contactinfos'][] = array(
                    'type' => 'email',
                    'label' => 'work',
                    'detail' => $params[1]
                );
        }
        if(isset($params[2]) && ($params[2] != '')) {
            $contact['contactinfos'][] = array(
                    'type' => 'phone',
                    'label' => 'work',
                    'detail' => $params[2]
                );
        }
        
        /* 
         * Send the data to the InsightlyCRM server
         */
            
        try {
            $result = $this->connection->post('/contacts', $contact);
        } catch (\Exception $e) {
            return array(false, strip_tags($e->getMessage()));
        }
            
        /* Return data: success status and/or error message */
        
        $addResult = array();
        if(isset($result['contact_id'])) {
            $addResult[0] = true;
            $addResult[1] = '';
        } else {
            $addResult[0] = false;
            $addResult[1] = 'An unexpected error occured';
        }
        
        return $addResult;
        
    }
    
}


?>
