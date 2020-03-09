<?PHP 

namespace MySiga {

class SigaError {

    // -----------------------------------------------------
    // -                  Base error info                  -
    // -----------------------------------------------------
    
    private const help_on  = 'https://jwdouglas.net/docs/mysiga/';
    private const reply_to = 'contato@jwdouglas.net';
    static private $errors = array(

    // -----------------------------------------------------
    // -                All Error responses                -
    // -----------------------------------------------------
    
        'UNKNOW_INTERNAL_ERROR'     => array(500, 'An undentified error has occurent on own server, please try again later. If you don\'t want to see happing again, please contact the email.'),
        'NO_RESPONSE'               => array(500, 'The final server response there are anything.'),
        'SIGA_PAGE_UNAVAILABLE'     => array(404, 'The siga page is not available for now, try again later.'),
        'SIGA_PAGE_UNLOAD'          => array(500, 'An error was occurent on load Siga page, please try again later.'),
        'SIGA_PAGE_NOT_LOADED'      => array(406, 'You need to load siga page before try to login.'),
        'SIGA_SESSION_EXPIRED'      => array(500, 'The session on siga has expired, please resend the load request to set another session.'),
        'SIGA_UNKNOW_USER'          => array(406, 'This siga user doesn\'t exist on siga, please check the user credentials.'),
        'SIGA_WRONG_PASSWORD'       => array(401, 'This password is wrong for this user, please check the password.'),
        'SIGA_NEED_CAPTCHA'         => array(409, 'This user need to do a captcha, please load the captcha or wait captcha validate disable for this user.'),
        'SIGA_WRONG_CAPTCHA'        => array(401, 'This captcha is wrong, please try again, or wait for cpatcha validate disable.'),
        'SIGA_NO_LOGGED'            => array(401, 'This user is not logged, please do login again.'),
        'UNDEFINED_RELEVANT_FIELDS' => array(406, 'You miss to send some important fields. | login/'),
        'UNDEFINED_REQUEST'         => array(400, 'Your request haven\'t any knowled ask.'),
    
    );
    // -----------------------------------------------------
    // -                  Error function                   -
    // -----------------------------------------------------

    static public function report($error_flag, $link=false) {
        $error = self::$errors[$error_flag];
        http_response_code($error[0]);
        return array('error' => array(
            'code'     => $error[0],
            'flag'     => $error_flag,
            'message'  => $error[1],
            'help-on'  => self::help_on.($link?$link:'error_flags/'),
            'reply-to' => self::reply_to,
        ));
    }
}

} // End of namespace
?>