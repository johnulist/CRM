<?php
    require ('crmSalesforce.php');
    require ('crmOnepage.php');
        
    try {
        $client = new CRM\crmOnepage('api@studiotg.pl', 'stgapi2013');
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    
    $newContact = array('StudioTgTest');
    
    try {
        $newContactResult = $client->addContact($newContact);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    
    if(isset($newContactResult)) {
        if($newContactResult[0]) {
            print 'Sukces! '.$newContactResult[1];
        } else {
            print 'Blad! '.$newContactResult[1];
        }
    }    
    
    
/*  ### Salesforce
 *   try {
        $client = new CRM\crmSalesforce('studio@studiotg.pl', 'stgapi2013wYqvWKMkj2X3ICNfSpC9RJDoK');
        if(isset($client->errors)) {
            throw new Exception;
        }
    } catch (Exception $e) {
        foreach ($client->errors as $error) {
            echo $error.'<br />';
        }
    }
    
    $newContact = array('StudioTgTest', 'api@studiotg.pl', '123456789');
    
    $newContactResult = $client->addContact($newContact);
        
    if(isset($newContactResult)) {
        if($newContactResult[0]) {
            print 'Sukces! '.$newContactResult[1];
        } else {
            print 'Blad! '.$newContactResult[1];
        }
    }
    
    
     $client->logout(); */
 ?>