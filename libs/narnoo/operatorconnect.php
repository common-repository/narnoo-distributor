<?php

class Operatorconnect extends WebClient {

    public $url = 'https://test-connect.narnoo.com/connect/';
    public $new_url = 'https://apis.narnoo.com/api/v1/';
    public $authen;

    public function __construct($authenticate) {

        $this->authen = array( "Authorization:bearer ".$authenticate );
    }

    public function getImages($id,$page=NULL) {

        $method = 'image/list/operator';


        $this->setUrl($this->new_url . $method .'/'. $id.'?page='. $page);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getVideos($id,$page=NULL) {

        $method = 'video/list/operator';

        $this->setUrl($this->new_url . $method .'/'. $id.'?page='. $page);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getBrochures($id,$page=NULL) {

        $method = 'brochure/operator_list';

        $this->setUrl($this->new_url . $method .'/'. $id.'?page='. $page);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAccount($id) {

        $method = 'connect/operator';


        $this->setUrl($this->new_url . $method .'/'. $id);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function getAlbums($op_id) {

        $method = 'album/list/operator';


        $this->setUrl($this->new_url . $method .'/'. $op_id );
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
   

    public function downloadBrochure($op_id,$bro_id) {

        $method = 'brochure/download/' . $bro_id . '/operator/' . $op_id;

        $this->setUrl($this->new_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function downloadImage($op_id,$img_id) {

        $method = 'image/download/' . $img_id . '/operator/' . $op_id;

        $this->setUrl($this->new_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    public function downloadVideo($op_id,$video_id) {

        $method = 'video/download/' . $video_id . '/operator/' . $op_id;


        $this->setUrl($this->new_url . $method);
        $this->setGet();
        try {
            return json_decode( $this->getResponse($this->authen) );
        } catch (Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}

?>
