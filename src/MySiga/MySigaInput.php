<?PHP

namespace MySiga;

use DateTime;

class MySigaInput {
    /**
     * @throws MySigaException
     */
    static function all() {
        throw new MySigaException('Not Found', 404);
    }

    /**
     * @throws MySigaException
     */
    static function semesterById(array $get) {
        if(!isset($get['id']) || intval($get['id']) < 0 || intval($get['id']) > 1000)
            throw new MySigaException('No valid semester id.');
        return MySigaDepartment::semesterById(intval($get['id']));
    }

    /**
     * @throws MySigaException
     */
    static function semesterByYear(array $get) {
        $year = intval((new DateTime('now'))->format('Y'));
        if(!isset($get['y']) || strlen($get['y']) > 4  || intval($get['y']) > ($year+1))
            throw new MySigaException('No valid year.');
        if(!isset($get['s']) || strlen($get['s']) != 1 || intval($get['s']) > 4)
            throw new MySigaException('No valid semester.');
        return MySigaDepartment::semesterByYear(intval($get['y']), intval($get['s']));
    }

    /**
     * @throws MySigaException
     */
    static function login(): array
    {
        if(!isset($_POST['cpf']) || strlen($_POST['cpf']) != 11)
            throw new MySigaException('No valid CPF.');
        if(!isset($_POST['password']) || strlen($_POST['password']) < 8)
            throw new MySigaException('No valid password.');
        if(isset($_POST['captcha']) && strlen($_POST['captcha']) < 5)
            throw new MySigaException('No valid captcha.');
        $captcha = $_POST['captcha'] ?? null;
        return MySigaUser::login($_POST['cpf'], $_POST['password'], $captcha);
    }

    /**
     * @throws MySigaException
     */
    static function oldLogin(): array
    {
        if(!isset($_POST['cpf']) || strlen($_POST['cpf']) != 11)
            throw new MySigaException('No valid CPF.');
        if(!isset($_POST['response']) || strlen($_POST['response']) < 8)
            throw new MySigaException('No valid password.');
        if(isset($_POST['captcha']) && strlen($_POST['captcha']) < 5)
            throw new MySigaException('No valid captcha.');
        $captcha = $_POST['captcha'] ?? null;
        return MySigaUser::oldLogin($_POST['cpf'], $_POST['response'], $captcha);
    }

    /**
     * @throws MySigaException
     */
    static function schedule(array $vars): array
    {
        if(isset($vars['code']) && strlen($vars['code'])>5 && strlen($vars['code'])<15)
            return MySigaAcademic::schedule($vars['code']);
        else
            throw new MySigaException('No valid class code.');
    }

    /**
     * @param string $uri
     * @param array $get
     * @return array
     * @throws MySigaException
     */
    static function department(string $uri, array $get): array
    {
        if(!isset($get['id']) || intval($get['id']) < 1)
            throw new MySigaException('No valid department code.');
        $year = intval((new DateTime('now'))->format('Y'));
        if(isset($get['y']) && (strlen($get['y']) > 4  || intval($get['y']) > ($year+1)))
            throw new MySigaException('No valid year.');
        if(isset($get['s']) && (strlen($get['s']) != 1 || intval($get['s']) > 4))
            throw new MySigaException('No valid semester.');
        $year = isset($get['y'])?intval($get['y']):null;
        $semester = $get['s'] ?? null;
        return MySigaDepartment::department(intval($get['id']), $year, $semester);
    }

    /**
     * @throws MySigaException
     */
    static function rooms(array $get) {
        $year = intval((new DateTime('now'))->format('Y'));
        if(!isset($get['id']) || intval($get['id']) < 1)
            throw new MySigaException('No valid department code.');
        if(isset($get['y']) && (strlen($get['y']) > 4  || intval($get['y']) > ($year+1)))
            throw new MySigaException('No valid year.');
        if(isset($get['s']) && (strlen($get['s']) != 1 || intval($get['s']) > 4))
            throw new MySigaException('No valid semester.');
        
        $year = isset($get['y'])?intval($get['y']):null;
        $semester = $get['s'] ?? null;
        return MySigaDepartment::rooms(intval($get['id']), $year, $semester);
    }

    /**
     * @throws MySigaException
     */
    static function room(array $get): array
    {
        if(!isset($get['room']) || intval($get['room']) < 1)
            throw new MySigaException('No valid room code.');
        return MySigaDepartment::room(intval($get['room']));            
    }

    /**
     * @throws MySigaException
     */
    static function registration(array $get): ?array
    {
        if(isset($get['view'])) {
            if($get['view'] == 'download')
                return MySigaAcademic::registration(false, true);
            elseif($get['view'] == 'browser')
                return MySigaAcademic::registration(false);
            else
                return MySigaAcademic::registration();
        }
        return null;
    }

    /**
     * @throws MySigaException
     */
    static function skinColor(): array
    {
        if(!isset($_POST['color']) || intval($_POST['color']) < 1)
            throw new MySigaException('No valid skin color option code.');
        return MySigaUser::setSkinColor(intval($_POST['color']));
    }

    /**
     * @throws MySigaException
     */
    static function iraChart(array $get): array
    {
        $avarege = false;
        $detailed = false;
        if(isset($get['a']) && $get['a'] == 'true')
            $avarege = true;
        if(isset($get['d']) && $get['d'] == 'true')
            $detailed = true;
        return MySigaAcademic::iraChart($avarege, $detailed);
    }

    /**
     * @throws MySigaException
     */
    static function cep(array $get): array
    {
        if(!isset($get['cep']) || strlen($get['cep']) != 8)
            throw new MySigaException('No valid cep code.');
        return MySigaUser::cep($get['cep']);
    }

    /**
     * @throws MySigaException
     */
    static function address() {
        $cep        = empty($_POST['cep'])        ?null:$_POST['cep'];
        $address    = empty($_POST['address'])    ?null:$_POST['address'];
        $number     = (empty($_POST['number']) || intval($_POST['number']) < 1)?null:intval($_POST['number']);
        $complement = empty($_POST['complement']) ?null:$_POST['complement'];
        $district   = empty($_POST['district'])   ?null:$_POST['district'];
        $city       = empty($_POST['city'])       ?null:$_POST['city'];
        $state      = empty($_POST['state'])      ?null:$_POST['state'];

        return MySigaUser::updateAddress($cep, $address, $number, $complement, $district, $city, $state);
    }

    /**
     * @throws MySigaException
     */
    static function contact(): array
    {
        $tel   = !isset($_POST['telephone']) ?null:$_POST['telephone'];
        $cel   = !isset($_POST['celphone'])  ?null:$_POST['celphone'];
        $email = !isset($_POST['email'])     ?null:$_POST['email'];
        
        return MySigaUser::updateContact($tel, $cel, $email);
    }

    /**
     * @throws MySigaException
     */
    static function password(): array
    {
        if(!isset($_POST['oldpassword']) || strlen($_POST['oldpassword']) < 8)
            throw new MySigaException('Old password is invalid.');
        if(!isset($_POST['password']))
            throw new MySigaException('New password is invalid.');
        
        $uc = preg_match('@[A-Z]@', $_POST['password']);
        $lc = preg_match('@[a-z]@', $_POST['password']);
        $nb = preg_match('@[0-9]@', $_POST['password']);
        
        if(!$uc || !$lc || !$nb || strlen($_POST['password']) < 8)
            throw new MySigaException('New password is too easy.');
        
        return MySigaUser::updatePassword($_POST['oldpassword'], $_POST['password']);
    }
}
