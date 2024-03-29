<?PHP

namespace MySiga;

use DateTime;
use DateTimeInterface;
use Scraping\Scraping;
use function Scraping\strmpart;
use function Scraping\strpart;

class MySiga extends Scraping {

    static function init($router) {
        try {
            self::json($router->callRoute($_SERVER['REQUEST_URI']));
        }catch(MySigaException $e) {
            self::json(array(
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'uri'     => $e->URI(),
                'server'  => $e->sigaServer(),
                'siga'    => $e->sigaSession(),
                'client'  => $e->clientSession(),
                'docs'    => MYSIGA_DOCS,
                'date'    => (new DateTime('now'))->format(DateTimeInterface::RSS),
            ), $e->getCode()<100?500:$e->getCode());
        }
    }

    public static function cache(string $name, mixed $cache=null, string $expire=null): mixed {
        parent::cacheFolder(MYSIGA_CACHE);
        return parent::cache($name, $cache, $expire);
    }

    public static function isOnSession(): bool {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        return (isset($_SESSION['mysiga']) && $_SESSION['mysiga'] instanceof MySiga);
    }

    /**
     * @throws MySigaException
     */
    public static function load(Scraping $scraping=null) {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(isset($scraping))
            $_SESSION['mysiga'] = $scraping;
        if(isset($_SESSION['mysiga']) && $_SESSION['mysiga'] instanceof MySiga)
            return $_SESSION['mysiga'];
        session_destroy();
        throw new MySigaException('Session not created. Load requested.', 424);
    }

    public function __construct() {
        parent::__construct(MYSIGA_REDIRECT, MYSIGA_USERAGENT);
    }

    /**
     * @throws MySigaException
     */
    public function begin(bool $useCaptcha=false): array
    {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        $data = parent::get('', true);
        self::useSession(true);

        if(self::session(strpart($data['content'], 'PHPSESSID=', ';')) == null
        || self::server($data['header']['Location']) == null)
            throw new MySigaException('Unable to load siga.', 502);
        
        $data = $useCaptcha?self::get('/?captcha=true')['content']:$data['content'];

        $challenge = strpart(strstr($data, 'challenge'), 'value="', '"');

        // $captchaId = $useCaptcha?strmpart($data, 'name="loginCaptcha"', 'files/inline/'):false;
        // $captchaId = $useCaptcha?strstr($captcha, '.png" alt="Captcha"', true):false;
        $captcha = $useCaptcha?array(
            'id' => strmpart($data, 'idCaptcha', 'value="', '"'),
            'numbers' => array(
                intval(strmpart($data, 'captchaNumero1', 'value="', '"')),
                intval(strmpart($data, 'captchaNumero2', 'value="', '"'))
            ),
            'tip' => 'Maybe just sum these two numbers?'
            // 'id' => $captchaId,
            // 'url' => $this->server().'/core/download/files/inline/'.$captchaId.'.png'
        ):null;

        if($useCaptcha) {
            setcookie('captcha', json_encode($captcha), 0, '/');
            $_SESSION['captcha'] = $captcha;
        }
        setcookie('challenge', $challenge, 0, '/');
        $_SESSION['challenge'] = $challenge;
        
        self::load($this);

        return array(
            'challenge' => $challenge,
            'captcha' => $useCaptcha?$captcha:null,
            'server' => $this->server(),
            'siga' => $this->session(),
            'client' => session_id(),
        );
    }

    /**
     * @throws MySigaException
     */
    private function checkReturn(array $data, bool $headers): array
    {
        if(($headers && !$data['request']) || ($headers && !$data['header'])
        || !$data['content'] || !$data['url'] || !$data['code'])
            throw new MySigaException('Unable to load siga.', 502);
        else if($data['code'] == 401)
            throw new MySigaException('Login required, please do it first.', 401);
        else if($data['code'] != 200 && $data['code'] != 302 && $data['code'] != 303)
            throw new MySigaException('This request is unavailable on Siga servers.', 404);
        else return $data;
    }

    /**
     * @throws MySigaException
     */
    public function get(string $uri, bool $headers=false): array {
        return self::checkReturn(parent::get($uri, $headers), $headers);
    }

    /**
     * @throws MySigaException
     */
    public function post(string $uri, array $data, array $header=null, bool $headers=false): array {
        return self::checkReturn(parent::post($uri, $data, $header, $headers), $headers);
    }

}