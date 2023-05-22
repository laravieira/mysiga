<?PHP

namespace MySiga;

use DateTime;
use DateTimeInterface;
use function Scraping\strmpart;

class MySigaLogin {
    /**
     * @throws MySigaException
     */
    static function login(string $user, string $password, int $captcha=null): array
    {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!isset($_SESSION['challenge']))
            (new MySiga())->begin();
        $response = md5($user.':'.md5($password).':'.$_SESSION['challenge']);

        try {
            return MySigaLogin::rawLogin($user, $response, $captcha);
        }catch(MySigaException $exception) {
            if($exception->getMessage() != 'Captcha required.')
                throw $exception;

            // Automatically solve this stupid captcha
            $data = (new MySiga())->begin(true);
            $captcha = $data['captcha']['numbers'][0] + $data['captcha']['numbers'][1];
            return self::login($user, $password, $captcha);
        }
    }

    /**
     * @throws MySigaException
     */
    static function rawLogin(string $user, string $response, int $captcha=null): array
    {
        $scp = MySiga::load();
        if(!isset($_SESSION['challenge']))
            throw new MySigaException('You need to load a siga session before trying login.', 424);
        if(isset($captcha) && !isset($_SESSION['captcha']))
            throw new MySigaException('You need to load a siga captcha before trying login with it.', 424);

        $post = array(
            'user'         => $user,
            'password'     => '',
            'uid'          => $user,
            'pwd'          => '',
            'tries'        => '',
            'redir'        => '',
            'url'          => '',
            'challenge'    => $_SESSION['challenge'],
            'response'     => $response,
            '__ISAJAXCALL' => 'yes'
        );

        if(isset($captcha)) $post += array(
            'loginCaptcha'   => $captcha,
            'idCaptcha'      => $_SESSION['captcha']['id'],
            'captchaNumero1' => $_SESSION['captcha']['numbers'][0],
            'captchaNumero2' => $_SESSION['captcha']['numbers'][1]
        );

        $data = $scp->post('/siga/login/authenticate/?', $post);
        $error = strstr($data['url'], '?');

        if(strpos($error, 'sessionExpired'))
            throw new MySigaException('Siga server session has been expired, do an reload and relogin.');
        else if(strpos($error, 'userNotRegistered'))
            throw new MySigaException('This CPF isn\'t registered in Siga servers.', 401);
        else if(strpos($error, 'errorPass'))
            throw new MySigaException('Wrong Password.', 401);
        else if(strpos($error, 'captcha'))
            throw new MySigaException('Captcha required.', 401);
        else if(strpos($error, 'captchaError'))
            throw new MySigaException('Captcha wrong.', 401);

        if(isset($_COOKIE['challenge']))
            setcookie('challenge',   '', time()-1, '/');
        if(isset($_COOKIE['captcha']))
            setcookie('captcha', '', time()-1, '/');

        return array(
            'server' => $scp->server(),
            'siga'   => $scp->session(),
            'client' => session_id(),
            'logged' => true,
        );
    }

    /**
     * @throws MySigaException
     */
    static function logout(): array
    {
        if(!MySiga::isOnSession()) return array(
            'server' => MYSIGA_REDIRECT,
            'siga'   => null,
            'client' => empty(session_id())?null:session_id(),
            'logged' => false,
        );

        $scp = MySiga::load();
        $scp->get('/siga/login/logout');
        session_destroy();
        return array(
            'server' => $scp->server(),
            'siga'   => $scp->session(),
            'client' => empty(session_id())?null:session_id(),
            'logged' => false,
        );
    }

    /**
     * @throws MySigaException
     */
    static function updatePassword(string $oldPassword, string $password): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/common/usuario/formAlterarSenha');

        $login     = strmpart($data['content'], 'me="log', 'value="', '"');
        $challenge = strmpart($data['content'], 'challenge', 'value="', '"');
        $realm     = strmpart($data['content'], 'realm', 'value="', '"');
        $md5pass   = md5($password);

        $post = array(
            'login'             => $login,
            'oldPassword'       => '',
            'newPassword'       => $md5pass,
            'newPasswordCheck'  => $md5pass,
            'challenge'         => $challenge,
            'newPasswordLength' => strlen($password),
            'response'          => md5($login.':'.md5($oldPassword).':'.$challenge),
            'idUsuario'         => '',
            'hashVoip'          => md5($login.':'.$realm.':'.$password),
            'hashEduroam'       => bin2hex($md5pass),
            'realm'             => $realm,
        );

        $data = $scp->post('/siga/common/usuario/alterarSenha', $post);

        if(strpos($data['content'], 'Senha atual não confere.'))
            throw new MySigaException('Old password is wrong.');
        if(strpos($data['content'], 'Nova senha deve ser diferente da atual!'))
            throw new MySigaException('Old and New passwords must be different.');
        if(!strpos($data['content'], 'Senha alterada com sucesso.'))
            throw new MySigaException('Can\'t change password.');
        
        return array(
            'login'   => $login,
            'session' => $scp->session(),
            'date'    => (new DateTime('now'))->format(DateTimeInterface::RSS),
            'passwordUpdated' => true,
        );
    }
}
