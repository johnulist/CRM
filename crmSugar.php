<?php

namespace CRM;

/* Require Pest library for cleaner REST integration
 * And sanitize.php to exclude malicious $account passed
 * to the constructor
 */

require_once ('sanitize.php');

    
class crmSugar {

    /* Define SugarCRM api server url
     * 
     */
    private $crmUrl;
    
    private $crmUser;
    
    private $connection;

    
    /* Constructor
     * @param string $login 
     * @param string $password
     * @param string $crmUrl       - 
     */
    
    public function __construct($login, $password, $crmUrl) {
        
        $this->crmUrl = 'https://trial.sugarcrm.com/'.format_uri($crmUrl).'/service/v4/soap.php?wsdl';
        $this->connection = new \SoapClient($this->crmUrl);
        
        /*
         * Prepare login data
         */
        $user_auth=  array(
            'user_name' => $login,
            'password' => md5($password)
        );
        $application_name = 'landingi';
        $name_value_list = array(
            array(
                'name' => 'language',
                'value' => 'pl_PL'
            ),
            array(
                'name' => 'notifyonsave',
                'value' => true
            )
        );
        
        /*
         * Login to SugarCRM cloud
         */
        
        try {
            $this->crmUser = $this->connection->login($user_auth, $application_name, $name_value_list);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    }
    
    /* addContact: Adding contact to SugarCRM;
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
        
        if(isset($this->crmUser->id)) {
            
            /* 
             * Prepare an array representing the new contact
             */
           
            $contact = array(
                array(
                    'name' => 'meh',
                    'value' => $params[0]
                )
            );
            
            if(isset($params[1]) && ($params[1] != '')) {
                $contact[] = array (
                    'name' => 'email',
                    'value' => $params[1]
                );
            }
            if(isset($params[2]) && ($params[2] != '')) {
                $contact[] = array (
                    'name' => 'phone_work',
                    'value' => $params[2]
                );
            }            
                   
            /* 
             * Send the data to the SugarCRM server
             */
            
            try {
                $result = $this->connection->set_entry($this->crmUser->id, 'Contacts', $contact);
                var_dump($result);
            } catch (\Exception $e) {
                return array(false, $e->getMessage());
            }
            
            /* Return data: success status and/or error message */
            if(isset($result->id)) {
                $addResult = array(true, 'Contact created! ');
            } else { 
                /*
                 * This condition shouldn't occur, all errors
                 * should be handled by the exception catch
                 */ 
                $addResult = array(false, 'An unexpected error occured!');
            }

    
        } else {
          $addResult = array(false,'Login failed, unable to add contacts! ');
        }
        
        return $addResult;
        
    }
    
}


?>
