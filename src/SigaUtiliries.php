<?PHP

namespace MySiga {

require_once('SigaError.php');

// define all info consts
const MYSIGA_NAME     = 'MySIGA';
const MYSIGA_FULLNAME = 'MySIGA API';
const MYSIGA_VERSION  = '0.0.0';
const MYSIGA_SERVER   = 'https://jwdouglas.net/api/mysiga';
const MYSIGA_DESC     = 'An unofficial API RESTful for SIGA3 of UFJF based on webscraping with PHP.';
const MYSIGA_DOC      = 'bit.ly/mysiga';

function get($uri, $header_separetor=false) {
    return curl_get($_SESSION['url'].$uri, [], $header_separetor);
}

function post($uri, $body = array(), $headers = array(), $cookies = array()) {
    
    $cookies["PHPSESSID"] = $_SESSION["session"];

    $cookie_line = "";
    foreach($cookies as $cookie_key => $cookie_value)
        $cookie_line .= " ".$cookie_key."=".$cookie_value.";";
    $cookie_line = ltrim($cookie_line, ";");

    $headers['Cookie'] = $cookie_line;
    
    $header_line = array();
    foreach($headers as $key => $value)
        $header_line[] = $key.': '.$value;

    $request = curl_init($_SESSION['url'].$uri);
    curl_setopt($request, CURLOPT_COOKIESESSION,  false);
    curl_setopt($request, CURLOPT_POST,           true);
    curl_setopt($request, CURLOPT_POSTFIELDS,     http_build_query($body));
    curl_setopt($request, CURLOPT_HTTPHEADER,     $header_line);
    curl_setopt($request, CURLINFO_HEADER_OUT,    true);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($request, CURLOPT_HEADER,         true);
    curl_setopt($request, CURLOPT_AUTOREFERER,    true);
    curl_setopt($request, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($request, CURLOPT_SSL_VERIFYPEER, true);
    $useragent = MYSIGA_NAME.'/'.MYSIGA_VERSION.' (+'.MYSIGA_DOC.') '.$_SERVER['SERVER_SOFTWARE'];
    curl_setopt($request, CURLOPT_USERAGENT, $useragent);
    $content = curl_exec($request);

    $data['header'] = strstr($content, "\r\n\r\n", true);
    $data['body'] = substr($content, strlen($data['header'])+2);
    $data["url"] = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
    $data["code"] = curl_getinfo($request, CURLINFO_HTTP_CODE);

    if($data['code'] == 401)
        return SigaError::report('SIGA_NO_LOGGED');

    if($data["code"] != 200 && $data["code"] != 302 && $data["code"] != 303)
        return SigaError::report('SIGA_PAGE_UNAVAILABLE');

    if(!$data['header'] || !$data['body'] || !$data['url'])
        return SigaError::report('SIGA_PAGE_UNLOAD');
    
    curl_close($request);
    return $data;
}

function curl_get($url, $cookies = array(), $header_separetor = false) {
    
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
    $useragent = MYSIGA_NAME.'/'.MYSIGA_VERSION.' (+'.MYSIGA_DOC.') '.$_SERVER['SERVER_SOFTWARE'];
    curl_setopt($request, CURLOPT_USERAGENT, $useragent);
    $content = curl_exec($request);
    
    $data['header'] = $header_separetor?strstr($content, "\r\n\r\n", true):strstr($content, '<', true);
    $data['body']   = substr($content, strlen($data['header'])+($header_separetor?4:0));
    $data['url']    = curl_getinfo($request, CURLINFO_EFFECTIVE_URL);
    $data['code']   = curl_getinfo($request, CURLINFO_HTTP_CODE);

    if($data['code'] == 401)
        return SigaError::report('SIGA_NO_LOGGED');

    if($data["code"] != 200 && $data["code"] != 302 && $data["code"] != 303)
        return SigaError::report('SIGA_PAGE_UNAVAILABLE');

    if(!$data['header'] || !$data['body'] || !$data['url'])
        return SigaError::report('SIGA_PAGE_UNLOAD');
    
    $data['cookies'] = array();
    preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $data['header'], $matches);
    foreach($matches[1] as $item) {
        parse_str($item, $cookie);
        $data['cookies'] = array_merge($data['cookies'], $cookie);
    }
    
    if(isset($data['cookies']['PHPSESSID']))
        $_SESSION['session'] = $data['cookies']['PHPSESSID'];

    $url = strpart($data['url'], null, 'index.php', false, true);
    if($url) $url.'/';
    if(!isset($_SESSION['url'])) $_SESSION['url'] = $url;
    if(!isset($_COOKIES['url'])) setcookie('url', $url);
    
    curl_close($request);
    return $data;
}

function upname($name) {
    $words = explode(" ", $name);
    $name = "";
    foreach($words as $word) {
        if($word == "I" || $word == "II" || $word == "III" || $word == "IV" || $word == "V" || $word == "VI")
            $name .= " ".$word;
        else if(strlen($word) < 4) {
            $word = mb_strtolower($word);
            if($word == 'cel') $word = 'Cel';
            if($word == 'rua') $word = 'Rua';
            if($word == 'ap')  $word = 'Ap.';
            if($word == 'ap.') $word = 'Ap.';
            if($word == 'ed')  $word = 'Ed.';
            if($word == 'ed.') $word = 'Ed.';
            if($word == 'av')  $word = 'Av.';
            if($word == 'av.') $word = 'Av.';
            $name .= ' '.$word;
        }else
            $name .= " ".mb_convert_case($word, MB_CASE_TITLE, "UTF-8");
    }
    return substr($name, 1);
}

function json_extract($string, $return_json=true) {
    $string = preg_replace('~\n~', '', $string);
    $string = str_replace(array('{', '",', ':'), array('{"', '","', '":'), $string);
    return $return_json?json_decode($string):$string;
}

function strpart($string, $start=false, $end=false, $keep_start=false, $keep_end=false) {
    $string = ($start != false)?strstr($string, $start):$string;
    $string = ($start != false && !$keep_start)?substr($string, strlen($start)):$string;
    if($end != false) {$string = strstr($string, $end, true);
    $string = ($string != false && $keep_end)?$string.$end:$string;}
    return $string;
}

} // End of namespace
?>