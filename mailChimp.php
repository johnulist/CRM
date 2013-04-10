<?php

namespace Mailing;

/* Require Pest library for cleaner REST integration
 * and sanitize.php for account name in url sanitization
 */

require_once ('\pest/pestJSON.php');
require_once ('sanitize.php');

    
class mailChimp {

    private $crmUrl;
    private $connection;

    private $apiKey;
    private $crmUser;
    
    private $listId;
    
    /* Constructor
     * @param string $apiKey
     */
    
    public function __construct($apiKey) {
        
        $endpoint = explode('-', $apiKey);
        /*
         * Saving auth details for later
         */
        $this->apiKey = $apiKey;
        $this->crmUrl = 'http://'.format_uri($endpoint[1]).'.api.mailchimp.com/1.3';
        
        $this->connection = new \PestJSON($this->crmUrl);


      
    }
    
    /*getLists: fetching a list id of the given list name
     * 
     * @params string $listName
     */
    
    
    public function getLists($listName) {
        
        if(isset($this->apiKey)) {
            
            /*
             * Prepare input array
             */
            
            $listsData = array(
                'apikey' => $this->apiKey,
                'filters' => array(
                    'list_name' => $listName
                )
            );
            
            try {
                $result = $this->connection->post('/?method=lists', $listsData);
                $this->listId = $result['data'][0]['id'];
                return array(true, '');
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }

            
        } else return array(false, 'Unable to retrieve list ID');
        
    }
    
    
    
    /* addContact: Adding contact to MailChimp;
     * 
     * @params array $params    array of 4:
     * params[0]: string          mailing list name, required
     * params[1]: string          contact email, required
     * params[2]: string          contact name
     * params[3]: string          contact phone
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
             * Get list ID of the list name passed to the function
             */
            
            $isListId = $this->getLists($params[0]);
            if($isListId[0] == true) {
                
                /* 
                 * Prepare an array representing the new contact
                 * along with the auth details
                 */

                $contact = array(
                    'apikey' => $this->apiKey,
                    'id' => $this->listId,
                    'email_address' => $params[1],
                    'update_existing' => true,
                    'double_optin' => false,
                    'send_welcome' => true
                );
                if(isset($params[2]) && ($params[2] != '')) {
                    $contact['merge_vars']['LNAME'] = $params[2];
                }
                if(isset($params[3]) && ($params[3] != '')) {
                    $contact['merge_vars']['phone'] = $params[3];
                }    

                /* 
                 * Send the data to the MailChimp server
                 */

                try {
                    $result = $this->connection->post('/?method=listSubscribe', $contact);
                 } catch (\Exception $e) {
                     return array(false, $e->getMessage());
                 }

                /* Return data: success status and/or error message */

                if(!isset($result['error'])) {
                    $addResult = array(true, 'Contact added! ');
                } else {
                    $addResult = array(false, $result['error']);
                }
                
                return $addResult;
            }

        } else {
            return array(false, 'Login failed. Unable to add contacts!');
        }
    }
}
?>
