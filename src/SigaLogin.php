<?PHP

namespace MySiga {

require 'SigaError.php';
require 'SigaUtiliries.php';

class SigaLogin {

	public function load($captcha=false) {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		
		if($captcha && !isset($_SESSION['url'])) {
			$result = $this->load(false);
			if(isset($result['error']))
				return $result;
			$url = $result['url'].'?captcha=true';
		}else if($captcha && isset($_SESSION['url']))
			$url = $_SESSION['url'].'?captcha=true';
		else if(isset($_SESSION['url']))
			$url = $_SESSION['url'];
		else $url = 'https://siga.ufjf.br/redirect.php/';

		$result = curl_get($url);
		if(isset($result['error'])) return $result;
		
		$data = array(
			'url'       => $_SESSION['url'],
			'challenge' => strpart(strstr($result['body'], 'challenge'), 'value="', '"'),
			'mysiga'    => session_id(),
			'siga3'     => $_SESSION['session'],
			'time'      => Date('r'),
		);
		if($captcha) $data['captcha'] = strpart(strstr($result['body'], 'Captcha'), 'inline/', '.');
		
		if(!$data['url'] || !$data['challenge'] || !$data['mysiga'] || !$data['siga3'] || !$data['time'] || ($captcha && !$data['captcha']))
			return SigaError::report('SIGA_PAGE_UNLOAD');
		
		if($captcha) $_SESSION['captcha'] = $data['captcha'];
		$_SESSION['challenge'] = $data['challenge'];
		return $data;
	}
	
	public function user_login($user, $response, $captcha='') {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		
		if(!isset($_SESSION['session']))
			return $this->error('SIGA_PAGE_NOT_LOADED');
		
		$body = array(
			"user"         => $user,
			"password"     => "",
			"uid"          => $user,
			"pwd"          => "",
			"tries"        => "",
			"redir"        => "",
			"url"          => "",
			"challenge"    => $_SESSION["challenge"],
			"response"     => $response,
			"__ISAJAXCALL" => "yes"
		);
		
		if(!empty($captcha)) $body += array(
			"loginCaptcha" => $captcha,
			"idCaptcha"    => $_SESSION["captcha"],
		);

		$result = post('/siga/login/authenticate', $body);
		if(isset($result['error'])) return $result;
		
		$result = preg_match('~\?(.*)=~', $result['url'], $r)?$r[1]:false;
		if($result) {
			if(     $result == "sessionExpired"   ) return SigaError::report('SIGA_SESSION_EXPIRED');
			else if($result == "userNotRegistered") return SigaError::report('SIGA_UNKNOW_USER');
			else if($result == "errorPass"        ) return SigaError::report('SIGA_WRONG_PASSWORD');
			else if($result == "captcha"          ) return SigaError::report('SIGA_NEED_CAPTCHA');
			else if($result == "captchaError"     ) return SigaError::report('SIGA_WRONG_CAPTCHA');
		}

		return array(
    	    'url'    => $_SESSION['url'],
			'mysiga' => session_id(),
			'siga3'  => $_SESSION['session'],
			'time'   => Date('r'),
        	'logged' => true,
		);
	}
	
	public function login($user, $password, $captcha=false) {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		
		if($captcha && !isset($_SESSION['captcha']))
			return SigaError::report('SIGA_PAGE_NOT_LOADED');
		
		if(!$captcha) {
			$data = $this->load();
			if(isset($data['error']))
				return $data;
		}

		$response = md5($user.':'.md5($password).':'.$_SESSION['challenge']);
		return $this->user_login($user, $response, $captcha?$captcha:'');
	}
	
	public function logout() {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		if(!isset($_SESSION['session']))
			return SigaError::report('SIGA_PAGE_NOT_LOADED');
		
		get('siga/login/logout');
		session_destroy();
		return array(
			'logged'   => false,
			'sessions' => 'Finished'
		);
	}
	
	public function status() {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		if(!isset($_SESSION['session']))
			return SigaError::report('SIGA_PAGE_NOT_LOADED');
		
		$result = get('/siga/academico/aluno/formDadosAluno');
		if(isset($result['error'])) return $result;

		$result['body'] = strpart($result['body'], 'menuL');
		$user = array(
			'cpf'       => strpart($result['body'], "rio: ", "]"),
			'matricula' => strpart($result['body'], "Atual: ", "]"),
			'msginbox'  => strpart($result['body'], 'Caixa/', '"'),
		);

		$result['body'] = strpart($result['body'], 'bodyL');
		$user['email']  = strtolower(strpart(strstr($result['body'], 'email'), 'value="', '"'));
		$user['name']   = upname(strpart(strstr($result['body'], 'nome'), 'value="', '"'));

		if(!$user['cpf'] || !$user['matricula'] || !$user['msginbox'] || !$user['email'] || !$user['name'])
			return SigaError::report('SIGA_NO_LOGGED');
		
		$user['logged'] = true;
		return $user;
	}
	
	public function redirect() {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		if(!isset($_SESSION['session']))
			return SigaError::report('SIGA_PAGE_NOT_LOADED');
		
		// -----------------------------
		// ATENTION: This is not working
		// -----------------------------
		
		http_response_code(303);
		$host = preg_match('~:\/\/(.*)\/index.php~', $_SESSION['url'], $r)?$r[1]:false;
		setcookie('PHPSESSID', $_SESSION['session'], ['Domain'=>$host, 'Path'=>'/']);
		header('Location: '.$_SESSION['url'].'/siga/academico/acessoaluno/main');
	}

}

} // End of namespace
?>