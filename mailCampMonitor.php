<?php

namespace Mailing;

/* Require Pest library for cleaner REST integration
 * and sanitize.php for account name in url sanitization
 */

require_once ('\pest/pestJSON.php');

    
class mailCampMonitor {

    private $crmUrl;
    private $connection;

    private $apiKey;
    private $crmUser;
    
    private $listId;
    
    /* Constructor
     * @param string $apiKey
     */
    
    public function __construct($apiKey) {
        
        /*
         * Saving auth details for later
         */
        $this->apiKey = $apiKey;
        $this->crmUrl = 'https://api.createsend.com/api/v3';
        
        $this->connection = new \PestJSON($this->crmUrl);
        $this->connection->setupAuth($this->apiKey, 'X');
      
    }

    /* addContact: Adding contact to MailChimp;
     * 
     * @params array $params    array of 4:
     * params[0]: string          mailing list ID, required
     * params[1]: string          contact email, required
     * params[2]: string          contact name
     * 
     * @return array addResult    Returns an array of 2;
     * addResult[0]: bool         true on operation success
     * addResult[1]: string       error message, if any 
     * 
     */
    
    public function addContact(array $params) {
        
        /*
         * Check for stored api key
         */
        
        if(isset($this->apiKey)) {

            /* 
             * Prepare an array representing the new contact
             * along with the auth details
             */

            $contact = array(
                'EmailAddress' => $params[1],
            );
            if(isset($params[2]) && ($params[2] != '')) {
                $contact['Name'] = $params[2];
            }
  
            /* 
             * Send the data to the Campaign Monitor server
             */

            try {
                $result = $this->connection->post('/subscribers/'.$params[0].'.json', $contact);
             } catch (\Exception $e) {
                 return array(false, $e->getMessage());
             }

            /* Return data: success status and/or error message */

            if(isset($result) && ($result == $params[1])) {
                $addResult = array(true, 'Contact added! ');
            } else {
                $addResult = array(false, $result['error']['Message']);
            }

            return $addResult;


        } else {
            return array(false, 'Login failed. Unable to add contacts!');
        }
    }
}
?>
