<?php

class Distributor extends WebClient {

    public $distributor_url = 'https://apis.narnoo.com/api/v1/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = array("Authorization: bearer ".$authenticate);
    }
    
    public function business_listing( $op_id ) {

        $method = 'business/listing/';

        $this->setUrl($this->distributor_url . $method . '/' . $op_id );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAccount() {

        $method = 'business/profile';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
    public function getAlbums( $page=NULL ) {

        $method = 'album/list';

        $this->setUrl($this->distributor_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getImages($page=NULL) {

        $method = 'image/list';
        if(!empty($page)){
            $this->setUrl($this->distributor_url . $method.'?page='.$page);
        }else {
            $this->setUrl($this->distributor_url . $method);
        }

        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
    
}

?>
