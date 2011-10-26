<?php
// /modules/msg.php
// Message handling module. Gathers and generates output to browser.


class Msg {
    // constructors
    function __construct() {
        $this->results  = null; 
        $this->errors   = null; 
        $this->warnings = null; 
        $this->notes    = null; 
        $this->status   = self::FINE;
        $this->success  = true;
    }

    // results logger
    // logs a message m in an assoc array with key k if k does not exist, f 
    // overwrite k by force
    public function rlog($k, $m, $f = false) {
        $ex = array_key_exists("$k", (array)$this->results);
        if ($ex) {
            if ($f)
                $this->log_result($k, $m);
            else
                return 1;
        }
        else
            $this->log_result($k, $m);

        return 0;
    }

    private function log_result($k, $m) {
        $this->results["$k"] = $m;
    }

    // logger
    // logs message m of type t and flags success if neccessary
    public function mlog($t, $m) {
        switch ($t) {
            case 'error':
            case 'err' :
            case 'e' :
                $this->log_error($m);
                break;

            case 'warning' :
            case 'warn' :
            case 'w' :
                $this->log_warning($m);
                break;

            case 'note' :
            case 'n' :
                $this->log_note($m);
                break;

            default :
                break;
        }
    }

    private function log_error($m) {
        $this->errors[] = $m;
        $this->status = $this->status | self::ERROR;
        $this->success = false;
    }

    private function log_warning($m) {
        $this->warnings[] = $m;
        $s = $this->status;
        $this->status = $this->status | self::WARNING;
    }
    
    private function log_note($m) {
        $this->notes[] = $m;
        $s = $this->status;
        $this->status = $this->status | self::NOTICE;
    }

    // outputer
    public function json_output() {
        echo "{$this->get_json()}";
    }

    // getters
    public function get_errors()   { return $this->errors; }
    public function get_warnings() { return $this->warnings; }
    public function get_notes()    { return $this->notes; }
    public function get_success()  { return $this->success; }
    public function get_status()   { return $this->status; }
    public function get_results()  { return $this->results; }

    public function get_json() {
        $msg = null;
        $msg['success'] = $this->success;

        // get logged messages
        $e = $this->errors;
        $w = $this->warnings;
        $n = $this->notes;
        if ($e || $w || $n)
            $msg['log'] = null;
        if ($e)
            $msg['log']['errors'] = $e;
        if ($w)
            $msg['log']['warnings'] = $w;
        if ($n)
            $msg['log']['notes'] = $n;

        //$r = $this->get_results();
        if ($this->results)
            $msg['results'] = $this->results;
        /*if ( $this->results )
        {
            $msg['results'] = Array();
            foreach ( $this->results as $k => $v )
                $msg['results']["$k"] = $v;
        }*/

        return json_encode( $msg );
    }

    // data
    private $results  = null; 
    private $errors   = null; 
    private $warnings = null; 
    private $notes    = null; 
    private $status;//   = FINE;
    private $success  = true;

    const ERROR   = 4;
    const WARNING = 2;
    const NOTICE  = 1;
    const FINE    = 0;
}

?>
