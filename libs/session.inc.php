<?php

class DBSessionHandler {
    function __construct() {
        $this->pl=new Platform;
        $this->lo = new Logic;
    }

    function open($sess_path, $sess_name) {
        return true;
    }

    function close() {
        return true;
    }

    function read($id) {
        $row = $this->lo->getSessionData($id);
        if(!count($row)) {
            $currentTime = time();
            $this->lo->setSession($id, $currentTime);
            return '';
        }
        return $row[0]['data'];
    }

    function write($id, $data) {
        $this->lo->writeSession($id, $data);
        return true;
    }

    function destroy($id) {
        $this->lo->removeSession($id);
        return true;
    }

    function gc($maxlifetime) {
        $this->lo->removeSessionGC($maxlifetime);
        return true;
    }
}
