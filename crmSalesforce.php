<?php
namespace CRM;
require_once ('\soapclient/SforcePartnerClient.php');

class crmSalesforce {

    private $connection;
    private $loginSuccess;
    
    public $errors = array();
    
    
    /* Constructor 
     * 
     * @param string login
     * @param string password     has to be one string, 
     *  concatenated actual password and security token
     *  ie. $password = passwordtoken;
     * 
     *  */
    
    public function __construct($login, $password) {
        
        /* Disabling soap cache */
        
        ini_set('soap.wsdl_cache_enabled', 0);
        ini_set('soap.wsdl_cache_ttl', 0);
        
        /* Connecting to Salesforce server */
        
        $this->connection = new \SforcePartnerClient();
        try {
            $conn = $this->connection->createConnection('soapclient/partner.wsdl.xml');
        } catch(\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
        
        /* Logging in to Salesforce */
                
        try {
            $login = $this->connection->login($login, $password);
            $this->loginSuccess = true;
        } catch (\Exception $e) {
            $this->loginSuccess = false;
            $this->errors[] = $e->getMessage();
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
        
        /* Make sure any unset arguments are converted to empty strings */ 
          
        if(!isset($params[1])) {
            $params[1] = '';
        }
        if(!isset($params[2])) {
            $params[2] = '';
        }

        /* Create a contact object to insert to Salesforce  */ 
        
        $contact = new \SObject();
        $contact->type = 'Contact';
        $contact->fields = array(
            'LastName' => $params[0],
            'Email' => $params[1],
            'Phone' => $params[2]
        );
        
        /* Create contact in Salesforce */
        
        try {
            $result = $this->connection->create(array($contact));
        } catch (\Exception $e) {
            return array(false, $e->getMessage());
        }
        
        /* Return data: success status and/or error message */
        
        $addResult = array();
        if(isset($result[0]->success)) {
            $addResult[0] = $result[0]->success;
        }
        if(isset($result[0]->errors)) {
            $addResult[1] = $result[0]->errors[0]->message;
        } else {
            $addResult[1] = '';
        }
      } else {
          $addResult = array(false,'Login unsuccessful, unable to add contacts! ');
      }
        return $addResult;

    }
    
    public function logout() {
        
        $this->connection->logout();
        
    }
    
}

?>
