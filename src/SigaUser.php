<?PHP 

namespace MySiga {

require_once('SigaError.php');
require_once('SigaUtiliries.php');

class SigaUser {

    function info() {
        
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

}

} // End of namespace

?>