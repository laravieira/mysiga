<?PHP

namespace MySiga;

use DateTime;
use DateTimeInterface;
use function Scraping\strpart;
use function Scraping\strmpart;
use function Scraping\strmstr;
use function Scraping\upname;
use function Scraping\accents;

class MySigaUser {



// |--------------------------------------------------------------------|
// |                                                                    |
// |                             User Login                             |
// |                                                                    |
// |--------------------------------------------------------------------|


    /**
     * @throws MySigaException
     */
    static function login(string $user, string $password, string $captcha=null): array
    {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(empty($captcha))
            (new MySiga())->begin();
        $response = md5($user.':'.md5($password).':'.$_SESSION['challenge']);
        return MySigaUser::rawLogin($user, $response, $captcha);
    }

    /**
     * @throws MySigaException
     */
    static function rawLogin(string $user, string $response, string $captcha=null): array
    {
        if(!isset($_SESSION['challenge']))
            throw new MySigaException('You need to load a siga session before trying login.', 7);
        if(isset($captcha) && !isset($_SESSION['captcha']))
            throw new MySigaException('You need to load a siga captcha before trying login with it.', 7);

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
            'loginCaptcha' => $captcha,
            'idCaptcha'    => $_SESSION['captcha'],
        );

        $scp = MySiga::load();
        $data = $scp->post('/siga/login/authenticate/?', $post);
        $error = strstr($data['url'], '?');

        if(strpos($error, 'sessionExpired'))
            throw new MySigaException('Siga server session has been expired, do an reload and relogin.');
        else if(strpos($error, 'userNotRegistered'))
            throw new MySigaException('This CPF isn\'t registered in Siga servers.');
        else if(strpos($error, 'errorPass'))
            throw new MySigaException('Wrong Password.');
        else if(strpos($error, 'captcha'))
            throw new MySigaException('Captcha required, load a captcha, solve and login again.');
        else if(strpos($error, 'captchaError'))
            throw new MySigaException('Captcha wrong.');

        if(isset($_COOKIE['challenge']))
            setcookie('challenge',   null, time()-1, '/');
        if(isset($_COOKIE['captcha']))
            setcookie('captcha', null, time()-1, '/');

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
    static function user(): array
    {
        $scp  = MySiga::load();
        $data = $scp->get('/siga/academico/aluno/formDadosAluno');
        $info = strstr($data['content'], 'id="menuLayout"');
        $user = array(
            'cpf'       => strpart($info, 'rio: ', ']'),
            'matricula' => strpart($info, 'Perfil Atual: ', ']'),
            'msginbox'  => strpart($info, 'siga/common/caixamensagem/formCaixa/', '"'),
            'email'     => strtolower(strpart(strstr($info, 'name="pessoa::email"'), 'value="', '"')),
            'name'      => upname(strpart(strstr($info, 'name="pessoa::nome"'), 'value="', '"')),
        );

        if(!$user['cpf'] || !$user['matricula'] || !$user['msginbox'] || !$user['email'] || !$user['name'])
            throw new MySigaException('Unable to load session user information.');
        else return $user;
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
    static function messages(): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/common/caixamensagem/formCaixa');
        
        if(strpos($data['content'], 'Não há mensagens'))
            return array();
        else
            throw new MySigaException('There are messages, but they readed isn\'t implemented yet.', 500);
    }

    /**
     * @throws MySigaException
     */
    static function coordenatorMessage(): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/academico/acessoaluno/formMsgCoordenacao');
        
        $text = explode('-', strpart(strstr($data['content'], 'id="curso"'), 'value="', '"'));
        $cood = strpart(strstr($data['content'], 'coordenador'), 'value="', '"');
        $msg  = strpart(strstr($data['content'], 'msgCoordenacao'), '>', '</span');

        return array(
            'course' => array(
                'code' => trim($text[0]),
                'name' => upname(html_entity_decode(trim($text[1]), encoding:'UTF-8')),
            ),
            'coordenator' => upname($cood),
            'msg' => strip_tags($msg),
        );
    }

    /**
     * @throws MySigaException
     */
    static function skinColor(): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/academico/aluno/formAtualizaCorRaca');
        
        $msg  = strmstr($data['content'], 'Informação importante:', '<span');
        $options = explode('<option', strpart(strstr($data['content'], 'Selecione sua'), '<option', '</select'));
        $skinColors = array();
        foreach($options as $option)
            $skinColors[strpart($option, 'value="', '"')] = match(strpart($option, '>', '<')) {
                'Amarela'  => 'Yellow',
                'Branca'   => 'White',
                'Indígena' => 'Indigenous',
                'Parda'    => 'Brown',
                'Preta'    => 'Black',
                'Não desejo declarar cor/raça' => 'No declare',
                default => strpart($option, '>', '<'),
            };

        return array(
            'skincolors' => $skinColors,
            'warning' => strip_tags(strpart($msg, '>', '</span')),
        );
    }

    /**
     * @throws MySigaException
     */
    static function setSkinColor(int $option): array
    {
        $user = MySigaUser::data();
        $post = array(
            'enumEtnia' => $option,
            'idPessoa'  => $user['id'],
        );

        $scp = MySiga::load();
        $data = $scp->post('/siga/academico/aluno/saveCorRaca', $post);
        if(!strpos($data['content'], 'atualizada com sucesso!'))
            throw new MySigaException('This information can\'t be registered on Siga', 500);
        
        return array(
            'date' => (new DateTime('now'))->format(DateTimeInterface::RSS),
            'skinColorUpdated' => true,
        );
    }

    /**
     * @throws MySigaException
     */
    static function lock(): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/academico/acessoaluno/formTrancamentoOpcoes');
        
        $text = explode('/', strpart(strstr($data['content'], 'id="campo4"'), '>', '<'));

        $semester = 0;
        if(strpos($data['content'], 'Solicitar Trancamento Período')) {
            $ctrl = 'mb'.strpart(strstr($data['content'], 'Solicitar Trancamento Período', true), 'id="mb', '"');
            $semester = strpart(strstr($data['content'], '["'.$ctrl), 'Periodo/', '\"');
        }

        return array(
            'matricula' => strpart(strstr($data['content'], 'id="campo1"'), '>', '<'),
            'name'      => upname(strpart(strstr($data['content'], 'id="campo2"'), '>', '<')),
            'unlocked'  => (bool)strpos(strpart(strstr($data['content'], 'id="campo3"'), '>', '<', true), 'Ativo'),
            'year'      => intval($text[0]),
            'semester'  => intval($text[1])?:$text[1],
            'lockid' => array(
                'semester' => $semester?:null,
                // Other options
            ),
        );
    }

    /**
     * @throws MySigaException
     */
    static function data(): array
    {
        $scp = MySiga::load();
        $data = $scp->get('/siga/academico/aluno/formDadosAluno');
        
        return array(
            'id'         => intval(strmpart($data['content'], 'idPessoa', 'value="', '"')),
            'profile'    => intval(strpart($data['content'], 'formCaixa/', '"')),
            'matricula'  => strpart($data['content'], 'Atual: ', ']'),
            'modified'   => date_create_from_format('d/m/Y', strmpart($data['content'], 'dataAlteracao', '>', '<'))->format('Y/m/d'),
            'name'       => upname(strmpart($data['content'], 'nome"', 'value="', '"')),
            'father'     => upname(strmpart($data['content'], 'nomePai', 'value="', '"')),
            'mother'     => upname(strmpart($data['content'], 'nomeMae', 'value="', '"')),
            'birth'      => date_create_from_format('d/m/Y', strmpart($data['content'], 'dataNasci', 'value="', '"'))->format('Y/m/d'),
            'hometown'   => array(
                'town'  => upname(trim(strpart(strmstr($data['content'], 'dataNasci', 'Local'), 'value="', '/'))),
                'state' => trim(strmpart(strmstr($data['content'], 'dataNasci', 'Local'), 'value="', '/', '"')),
            ),
            'telephone'  => strmpart($data['content'], 'telefone', 'value="', '"'),
            'cellphone'  => strmpart($data['content'], 'celular', 'value="', '"'),
            'email'      => strtolower(strmpart($data['content'], 'email', 'value="', '"')),
            'address'    => array(
                'cep'        => strmpart($data['content'], 'CEP', 'value="', '"'),
                'id'         => intval(strmpart($data['content'], 'idMunicipio', 'value="', '"')),
                'street'     => upname(strmpart($data['content'], '::end', 'value="', '"')),
                'complement' => upname(strmpart($data['content'], 'complemento', 'value="', '"')),
                'district'   => upname(strmpart($data['content'], 'bairro', 'value="', '"')),
                'city'       => upname(strmpart($data['content'], 'municipio', 'value="', '"')),
                'state'      => strmpart($data['content'], 'UF', 'value="', '"'),
            ),
            'cpf'        => strmpart($data['content'], 'CPF', 'value="', '"'),
            'rg'         => array(
                'code'       => strmpart($data['content'], 'numeroRG', 'value="', '"'),
                'publisher'  => strmpart($data['content'], 'orgaoRG', 'value="', '"'),
            ),
        );
    }

    /**
     * @throws MySigaException
     */
    static function cep(string $cep): array
    {
        $code = $cep;
        $code = substr($code, 0, 5).'-'.substr($code, 5);
        $post = array(
            'mcomponenteendereco::CEP' => $code,
        );
        $scp = MySiga::load();
        $data = $scp->post('/siga/mcomponenteendereco/ajaxPesquisaCEP/?idComponente=mcomponenteendereco', $post);
        $data = html_entity_decode($data['content']);

        $cepid      = intval(strmpart($data, 'idMunicipio', 'value="', '"'));
        $address    = upname(strmpart($data, '::end', 'value="', '"'));
        $complement = upname(strmpart($data, 'complemento', 'value="', '"'));
        $district   = upname(strmpart($data, 'bairro', 'value="', '"'));
        $city       = upname(strmpart($data, 'municipio', 'value="', '"'));
        $state      = strmpart($data, 'UF', 'value="', '"');

        if(empty($cepid) || empty($state) || empty($city))
            throw new MySigaException('Unrecognized cep code.');
        
        return array(
            'cep'        => $code,
            'cepid'      => $cepid,
            'address'    => (empty($address) || accents($address) == $city)?null:$address,
            'complement' => empty($complement)?null:$complement,
            'district'   => empty($district)?null:$district,
            'city'       => $city,
            'state'      => $state,
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

    /**
     * @throws MySigaException
     */
    static function updateAddress(string $cep=null, string $address=null, int $number=null, string $complement=null, string $district=null, string $city=null, $state=null) {
        if(empty($cep) && empty($address) && empty($number) && empty($complement) && empty($district) && empty($city) && empty($state))
            throw new MySigaException('No data to update');
        
        $data = self::data();
        $adr = self::cep(empty($cep)?$data['address']['cep']:$cep);
        
        if(empty($adr['address']) && !empty($address))
            $adr['address'] = $address;
        if(empty($adr['address']) && empty($address))
            $adr['address'] = $data['address']['street'];
        
        if(empty($adr['complement']) && (!empty($number) || !empty($complement)))
            $adr['complement'] = $number.', '.$complement;
        if(empty($adr['complement']) && empty($number) && empty($complement))
            $adr['complement'] = $data['address']['complement'];

        if(empty($adr['district']) && !empty($district))
            $adr['district'] = $district;
        if(empty($adr['district']) && empty($district))
            $adr['district'] = $data['address']['district'];

        if(empty($adr['city']) && !empty($city))
            $adr['city'] = $city;
        if(empty($adr['city']) && empty($city))
            $adr['city'] = $data['address']['city'];

        if(empty($adr['state']) && !empty($state))
            $adr['state'] = $state;
        if(empty($adr['state']) && empty($state))
            $adr['state'] = $data['address']['state'];

        $post = array(
            'pessoa::idPessoa'                 => $data['id'],
            'mcomponenteendereco::CEP'         => $adr['cep'],
            'mcomponenteendereco::endereco'    => $adr['address'],
            'mcomponenteendereco::number'      => empty($number)?$adr['complement']:$number,
            'mcomponenteendereco::complemento' => $adr['complement'],
            'mcomponenteendereco::bairro'      => $adr['district'],
            'mcomponenteendereco::municipio'   => $adr['city'],
            'mcomponenteendereco::UF'          => $adr['state'],
            'mcomponenteendereco::idMunicipio' => $adr['cepid'],
        );
        
        $scp = MySiga::load();
        $data = $scp->post('/siga/academico/aluno/saveDadosAluno', $post);

        if(!strpos($data['content'], 'Dados pessoais salvos com sucesso.'))
            throw new MySigaException('Can\'t update data.');
        return self::data()['address'];
    }

    /**
     * @throws MySigaException
     */
    static function updateContact(string $tel=null, string $cel=null, string $email=null): array
    {
        if(!isset($tel) && !isset($cel) && !isset($email))
            throw new MySigaException('No data to update');

        $data = self::data();
        if(!empty($tel) && !preg_match('/\(?\d{2}\)?\s?\d{4}-?\d{4}/', $tel))
            throw new MySigaException('Invalid telephone number.');
        if(!empty($cel) && !preg_match('/\(?\d{2}\)?\s?\d{5}-?\d{4}/', $cel))
            throw new MySigaException('Invalid celphone number.');
        if(!empty($email) && !preg_match('/[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+.[a-zA-Z]{2,4}/', $email))
            throw new MySigaException('Invalid email.');
        
        $ptel = empty($tel)?'':'('.substr($tel, 0, 2).') '.substr($tel, 2, 4).'-'.substr($tel, 6);
        $pcel = empty($cel)?'':'('.substr($cel, 0, 2).') '.substr($cel, 2, 5).'-'.substr($cel, 7);
        
        $post = array(
            'pessoa::idPessoa' => $data['id'],
            'pessoa::telefone' => !isset($tel)?$data['telephone']:$ptel,
            'pessoa::celular'  => !isset($cel)?$data['celphone']:$pcel,
            'pessoa::email'    => empty($email)?$data['email']:$email,
            'mcomponenteendereco::CEP'         => $data['address']['cep'],
            'mcomponenteendereco::endereco'    => $data['address']['street'],
            'mcomponenteendereco::complemento' => $data['address']['complement'],
            'mcomponenteendereco::bairro'      => $data['address']['district'],
            'mcomponenteendereco::municipio'   => $data['address']['city'],
            'mcomponenteendereco::UF'          => $data['address']['state'],
            'mcomponenteendereco::idMunicipio' => $data['address']['cepid'],
        );
        
        $scp = MySiga::load();
        $data = $scp->post('/siga/academico/aluno/saveDadosAluno', $post);

        if(!strpos($data['content'], 'Dados pessoais salvos com sucesso.'))
            throw new MySigaException('Can\'t update data.');
        
        $data = self::data();
        return array(
            'telephone' => $data['telephone'],
            'celphone'  => $data['celphone'],
            'email'     => $data['email'],
        );
    }

}
