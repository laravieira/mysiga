<?PHP 

class MyError {

    // -----------------------------------------------------
    // -                  Base error info                  -
    // -----------------------------------------------------
    
    private const help_on  = 'https://jwdouglas.net/docs/mysiga/';
    private const reply_to = 'contato@jwdouglas.net';
    private $errors = array();

    // -----------------------------------------------------
    // -                All Error responses                -
    // -----------------------------------------------------
    
    function __construct() {
        $this->setFlag('UNKNOW_INTERNAL_ERROR',     500, 'An undentified error has occurent on own server, please try again later. If you don\'t want to see happing again, please contact the email.');
        $this->setFlag('NO_RESPONSE',               500, 'The final server response there are anything.');
        $this->setFlag('SIGA_PAGE_UNAVAILABLE',     404, 'The siga page is not available for now, try again later.');
        $this->setFlag('SIGA_PAGE_UNLOAD',          500, 'An error was occurent on load Siga page, please try again later.');
        $this->setFlag('SIGA_PAGE_NOT_LOADED',      406, 'You need to load siga page before try to login.');
        $this->setFlag('SIGA_SESSION_EXPIRED',      500, 'The session on siga has expired, please resend the load request to set another session.');
        $this->setFlag('SIGA_UNKNOW_USER',          406, 'This siga user doesn\'t exist on siga, please check the user credentials.');
        $this->setFlag('SIGA_WRONG_PASSWORD',       401, 'This password is wrong for this user, please check the password.');
        $this->setFlag('SIGA_NEED_CAPTCHA',         409, 'This user need to do a captcha, please load the captcha or wait captcha validate disable for this user.');
        $this->setFlag('SIGA_WRONG_CAPTCHA',        401, 'This captcha is wrong, please try again, or wait for cpatcha validate disable.');
        $this->setFlag('SIGA_NO_LOGGED',            401, 'This user is not logged, please do login again.');
        $this->setFlag('UNDEFINED_RELEVANT_FIELDS', 406, 'You miss to send some important fields. | login/');
        $this->setFlag('UNDEFINED_REQUEST',         400, 'Your request haven\'t any knowled ask.');
    }
    
    
    // -----------------------------------------------------
    // -                  Error function                   -
    // -----------------------------------------------------
    
    public function report($error_flag, $link=false) {
        $error = $this->errors[$error_flag];
        http_response_code($error[0]);
        return array('error' => array(
            'code'     => $error[0],
            'flag'     => $error_flag,
            'message'  => $error[1],
            'help-on'  => MyError::help_on.($link?$link:'error_flags/'),
            'reply-to' => MyError::reply_to,
        ));
    }
    
    private function setFlag($name, $code, $message) {
        $this->errors[$name] = array($code, $message);
    }
}

?>