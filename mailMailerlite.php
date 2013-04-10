<?php

namespace Mailing;

/* Require Pest library for cleaner REST integration
 * and sanitize.php for account name in url sanitization
 */

    
class mailMailerlite {

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
        $this->crmUrl = 'http://mlsend.com/soapserver.php?wsdl';
        
        $this->connection = new \Soapclient($this->crmUrl);
      
    }
    
    /* addContact: Adding contact to MailerLite;
     * 
     * @params array $params    array of 4:
     * params[0]: string          mailing list name, required
     * params[1]: string          contact email, required
     * params[2]: string          contact name, required
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
             * Fetch group list and extract the ID of the given group
             */
            try {
                $groups = $this->connection->getGroups($this->apiKey);
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }
            
            foreach($groups['Groups'] as $group) {
                if($group->name == $params[0]) {
                    $groupId = $group->id;
                }
            }
            
            if(!isset($groupId)) {
                return array(false, 'Invalid group name!');
            }

            /* 
             * Send the data to the MailChimp server
             */

             try {
                $result = $this->connection->addSubscriber($this->apiKey, $groupId, $params[1], $params[2]);
             } catch (\Exception $e) {
                 return array(false, $e->getMessage());
             }

            /* Return data: success status and/or error message */

            if(isset($result->message) && ($result->message == 'OK')) {
                $addResult = array(true, 'Contact added! ');
            } else {
                $addResult = array(false, $result->message);
            }

            return $addResult;

        } else {
            return array(false, 'Login failed. Unable to add contacts!');
        }
    }
}
?>
