<?php

namespace Mailing;

/* Require Pest library for cleaner REST integration
 * and sanitize.php for account name in url sanitization
 */

require_once ('\pest/pestJSON.php');

    
class mailUnisender {

    private $crmUrl;
    private $connection;

    private $apiKey;

    
    /* Constructor
     * @param string $apiKey
     */
    
    public function __construct($apiKey) {
        
        $this->apiKey = $apiKey;
        $this->crmUrl = 'http://api.unisender.com/en/api';
        
        $this->connection = new \PestJSON($this->crmUrl);


      
    }
    
    /*getLists: fetching a list id of the given list name
     * 
     * @params string $listName
     */
    
    
    public function getLists($listName) {
        
        if(isset($this->apiKey)) {
            
            /*
             * Get all mailing lists, check for given list name
             * and fetch id
             */
            
            try {
                $result = $this->connection->get('/getLists?format=json&api_key='.$this->apiKey);
                
                if(isset($result['result'])) {
                    foreach($result['result'] as $list) {
                        if($list['title'] == $listName) {
                            return array(true, $list['id']);
                        }
                    }
                }
                
                return array(false, 'No list '.$listName.' exists!');
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }

            
        } else return array(false, 'Unable to retrieve list ID');
        
    }
    
    
    
    /* addContact: Adding contact to Unisender;
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
                 * Prepare a string representing the new contact
                 * along with the auth details
                 */

                $contact = '&fields[email]='.urlencode($params[1]);

                
                if(isset($params[2]) && ($params[2] != '')) {
                    $contact .= '&fields[name]='.urlencode($params[2]);
                }
                if(isset($params[3]) && ($params[3] != '')) {
                    $contact .= '&fields[phone]='.urlencode($params[3]);
                }
                
                /* 
                 * Send the data to the Unisender server
                 */

                try {
                    $result = $this->connection->post('/subscribe?format=json&api_key='.$this->apiKey.'&list_ids='.$isListId[1].'&double_optin=0&overwrite=2'.$contact, $contact);
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
