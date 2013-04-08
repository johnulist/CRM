<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * 
 */

require_once ('\pest/pestJSON.php');

    
class crmPipeline {

    /* Define PiplelineDeals api server url
     * 
     */
    private $crmUrl = 'https://api.pipelinedeals.com/api/v3';
    
    private $connection;
    private $apiKey;


    
    /* Constructor
     * @param string $apiKey
     */
    
    public function __construct($apiKey) {
      
        $this->connection = new \PestJSON($this->crmUrl);
        
        $this->apiKey = $apiKey;
      
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
         * Prepare an array representing the new contact
         */

        $contact = array(
            'person' => array(
                'last_name' => $params[0]
            )
        );
        
        if(isset($params[1]) && ($params[1] != '')) {
            $contact['person']['email'] = $params[1];
        }
        if(isset($params[2]) && ($params[2] != '')) {
            $contact['person']['phone'] = $params[2];
        }
        
        /* 
         * Send the data to the PipelineDeals server
         */

        try {
            $result = $this->connection->post('/people.json?api_key='.$this->apiKey, $contact);
        } catch (\Exception $e) {
            $msg = json_decode($e->getMessage());
            return array(false, $msg[0]->msg);
        }

        /* Return data: success status and/or error message */

        if(isset($result['full_name'])) {
            $addResult = array(true, 'Contact added! ');
        } else {
            $addResult = array(false, 'An unexpected error occured! ');
        }

        return $addResult;
  
    }
}
?>
