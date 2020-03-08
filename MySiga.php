<?PHP

class MySiga {

	// define all info consts
	private const MYSIGA_NAME     = 'MySIGA';
	private const MYSIGA_FULLNAME = 'MySIGA API';
	private const MYSIGA_VERSION  = '0.0.0';
	private const MYSIGA_SERVER   = 'https://jwdouglas.net/api/mysiga';
	private const MYSIGA_DESC     = 'An unofficial API RESTful for SIGA3 of UFJF based on webscraping with PHP.';
	private const MYSIGA_DOC      = 'bit.ly/mysiga';

	public function load($captcha=false) {
		if(session_status() != PHP_SESSION_ACTIVE)
			session_start();
		
		$url = isset($_SESSION["url"])?$_SESSION["url"]:"https://siga.ufjf.br/redirect.php/";
		
		if(!$captcha) $result = $this->curl_get($url);
		else $result = $this->curl_get($url."?captcha=true");
		if(isset($result['error'])) return $result;
		
		$data = array(
			'url'       => $_SESSION['url'],
			'challenge' => preg_match('~challenge.*value="(.*)">.*res.*__~s', $result['body'], $r)?$r[1]:false,
			'mysiga'    => session_id(),
			'siga3'     => $_SESSION['session'],
			'time'      => Date('r'),
		);
		if($captcha) $data['captcha'] = preg_match('~name="loginCaptcha".*files/inline/(.*)\.png.*idCaptcha"~s', $result['body'], $r)?$r[1]:false;
		
		if(!$data['url'] || !$data['challenge'] || !$data['mysiga'] || !$data['siga3'] || !$data['time'] || ($captcha && !$data['captcha']))
			return $this->error('SIGA_PAGE_UNLOAD');
		
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
			"challenge"    => $_SESSION["challenge"],
		);

		$result = $this->post('/siga/login/authenticate/?', $body);
		if(isset($result['error'])) return $result;
		
		$result = preg_match('~\?(.*)=~', $result['url'], $r)?$r[1]:false;
		if($result) {
			if(     $result == "sessionExpired"   ) return $this->error('SIGA_SESSION_EXPIRED');
			else if($result == "userNotRegistered") return $this->error('SIGA_UNKNOW_USER');
			else if($result == "errorPass"        ) return $this->error('SIGA_WRONG_PASSWORD');
			else if($result == "captcha"          ) return $this->error('SIGA_NEED_CAPTCHA');
			else if($result == "captchaError"     ) return $this->error('SIGA_WRONG_CAPTCHA');
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
			return $this->error('SIGA_PAGE_NOT_LOADED');
		
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
		$this->get('siga/login/logout');
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
			return $this->error('SIGA_PAGE_NOT_LOADED');
		

		$result = $this->get("/siga/academico/aluno/formDadosAluno");
		if(isset($result["error"])) return $result;

		$result["body"] = strstr($result["body"], 'id="menuLayout"');
		$user = array(
			"cpf"       => $this->strpart($result["body"], "rio: ", "]"),
			"matricula" => $this->strpart($result["body"], "Perfil Atual: ", "]"),
			"msginbox"  => $this->strpart($result["body"], 'siga/common/caixamensagem/formCaixa/', '"'),
			"email"     => strtolower($this->strpart(strstr($result["body"], 'name="pessoa::email"'), 'value="', '"')),
			//"name"      => upname(strpart(strstr($result["body"], 'name="pessoa::nome"'), 'value="', '"')),
		);

		if(!$user["cpf"] || !$user["matricula"] || !$user["msginbox"] || !$user["email"])// || !$user["name"])
			return $this->error('SIGA_NO_LOGGED');
		
		$user['logged'] = true;
		return $user;
	}
	
	private function get($uri) {
		return $this->curl_get($_SESSION['url'].$uri);
	}
	
	private function post($uri, $body = array(), $cookies = array()) {
		
		$cookies["PHPSESSID"] = $_SESSION["session"];

		$cookie_line = "Cookie:";
		foreach($cookies as $cookie_key => $cookie_value)
			$cookie_line .= " ".$cookie_key."=".$cookie_value.";";
		$cookie_line = ltrim($cookie_line, ";");

		$request = curl_init($_SESSION['url'].$uri);
		curl_setopt($request, CURLOPT_COOKIESESSION,  false);
		curl_setopt($request, CURLOPT_POST,           true);
		curl_setopt($request, CURLOPT_POSTFIELDS,     http_build_query($body));
		curl_setopt($request, CURLOPT_HTTPHEADER,     array($cookie_line));
		curl_setopt($request, CURLINFO_HEADER_OUT,    true);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_HEADER,         true);
		curl_setopt($request, CURLOPT_AUTOREFERER,    true);
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
		$useragent = $this::MYSIGA_NAME.'/'.$this::MYSIGA_VERSION.' (+'.$this::MYSIGA_DOC.') '.$_SERVER['SERVER_SOFTWARE'];
		curl_setopt($request, CURLOPT_USERAGENT, $useragent);
		$content = curl_exec($request);

		$data['header'] = preg_match('~(.*)\r\n\r\n<\!~s', $content, $r)?$r[1]:false;
		$data['body'] = substr($content, strlen($data['header'])+4);
		$data["url"] = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
		$data["code"] = curl_getinfo($request, CURLINFO_HTTP_CODE);
	
		if($data['code'] == 401)
			return $this->error('SIGA_NO_LOGGED');

		if($data["code"] != 200 && $data["code"] != 302 && $data["code"] != 303)
			return $this->error('SIGA_PAGE_UNAVAILABLE');

		if(!$data['header'] || !$data['body'] || !$data['url'])
			return $this->error('SIGA_PAGE_UNLOAD');
		
		curl_close($request);
		return $data;
	}
	
	private function curl_get($url, $cookies = array()) {
		
		if(isset($_SESSION['session']))
			$cookies['PHPSESSID'] = $_SESSION['session'];

		$cookie_line = 'Cookie:';
		foreach($cookies as $cookie_key => $cookie_value)
			$cookie_line .= ' '.$cookie_key.'='.$cookie_value.';';
		$cookie_line = ltrim($cookie_line, ';');

		$request = curl_init($url);
		curl_setopt($request, CURLOPT_COOKIESESSION,  true);
		curl_setopt($request, CURLOPT_POST,           false);
		curl_setopt($request, CURLOPT_HTTPHEADER,     array($cookie_line));
		curl_setopt($request, CURLINFO_HEADER_OUT,    true);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($request, CURLOPT_HEADER,         true);
		curl_setopt($request, CURLOPT_AUTOREFERER,    true);
		curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
		$useragent = $this::MYSIGA_NAME.'/'.$this::MYSIGA_VERSION.' (+'.$this::MYSIGA_DOC.') '.$_SERVER['SERVER_SOFTWARE'];
		curl_setopt($request, CURLOPT_USERAGENT, $useragent);
		$content = curl_exec($request);
		
		$data['header'] = preg_match('~(.*)\r\n\r\n<\!~s', $content, $r)?$r[1]:false;
		$data['body'] = substr($content, strlen($data['header'])+4);
		$data['url'] = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
		$data['code'] = curl_getinfo($request, CURLINFO_HTTP_CODE);
	
		if($data['code'] == 401)
			return $this->error('SIGA_NO_LOGGED');

		if($data["code"] != 200 && $data["code"] != 302 && $data["code"] != 303)
			return $this->error('SIGA_PAGE_UNAVAILABLE');

		if(!$data['header'] || !$data['body'] || !$data['url'])
			return $this->error('SIGA_PAGE_UNLOAD');
		
		$data['cookies'] = array();
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data['header'], $matches);
		foreach($matches[1] as $item) {
			parse_str($item, $cookie);
			$data['cookies'] = array_merge($data['cookies'], $cookie);
		}
		
		if(isset($data['cookies']['PHPSESSID']))
			$_SESSION['session'] = $data['cookies']['PHPSESSID'];

		$url = preg_match('#http.*index.php#', $data['url'], $r)?$r[0].'/':false;
		if(!isset($_SESSION['url'])) $_SESSION['url'] = $url;
		if(!isset($_COOKIES['url'])) setcookie('url', $url);
		
		curl_close($request);
		return $data;
	}

	private function error($flag) {
		require 'MyError.php';
		$myerror = new MyError();
		return $myerror->report($flag);
	}
	
	// depreciated
	private function strpart($string, $start=false, $end=false, $keep_start=false) {
		return strstr(substr(strstr($string, ($start !== false)?$start:""), $keep_start?0:strlen($start)), ($end !== false)?$end:"", true);
	}
}
?>