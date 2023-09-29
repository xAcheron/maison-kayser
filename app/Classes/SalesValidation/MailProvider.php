<?php

namespace App\Classes\SalesValidation;

class MailProvider 
{   
    private $server;
    private $user;
    private $password;
    private $mailBox;

    public function __construct($server="",$user="",$password="") {
        $this->server = $server;
        $this->user = $user;
        $this->password = $password;
        $this->mailBox = $this->connect($server,$user,$password);
    }

    private function connect($server,$user,$password) {
        $mbox = imap_open($server,$user,$password);
        if($mbox===false)
            echo "Error de Conexion IMAP";
        return $mbox;
    }

    function getMails($criteria = 'UNSEEN')
    {
        return imap_search( $this->mailBox, $criteria);
    }
    
    function getFileFromMail($idmail=0) {

        $structure = imap_fetchstructure($this->mailBox, $idmail);

        $attachments = array();

        if(isset($structure->parts) && count($structure->parts)) {

            for($i = 0; $i < count($structure->parts); $i++) {

                    $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );

                if($structure->parts[$i]->ifdparameters) {
                    foreach($structure->parts[$i]->dparameters as $object) {
                        if(strtolower($object->attribute) == 'filename') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }  
                }

                if($structure->parts[$i]->ifparameters) {
                    foreach($structure->parts[$i]->parameters as $object) {
                        if(strtolower($object->attribute) == 'name') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if($attachments[$i]['is_attachment']) {
                    $attachments[$i]['attachment'] = imap_fetchbody($this->mailBox, $idmail, $i+1);
                    if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }

            }
        }

        if(!empty($attachments[1]))
        {    
            $filePath = sys_get_temp_dir()."/".$attachments[1]["filename"];

            if(file_put_contents($filePath, $attachments[1]['attachment']))
                return $filePath;
            else
                return "";
        }
        else
        {
            $status = imap_setflag_full($this->mailBox, $idmail, "\\Seen");
            echo "vacio".$status ;
            return "";
        }
    }
    

}