<?PHP 

namespace MySiga {

require_once('SigaError.php');
require_once('SigaUtiliries.php');

class SigaUser {

    function data() {
        
		if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!isset($_SESSION['session']))
            return SigaError::report('SIGA_PAGE_NOT_LOADED');
        
        $result = get('/siga/academico/aluno/formDadosAluno');
        if(isset($result['error'])) return $result;

        $result['body'] = strpart($result['body'], 'menuL');
        $user = array();

        $registry = strpart($result['body'], 'Atual: ', ']');
        $msginbox = strpart($result['body'], 'Caixa/', '"');

        $result['body'] = strpart($result['body'], 'bodyL');

        $last_mod = strpart(strstr($result['body'], 'racao'), '>', '<').' 00:00:00 -0300';
        $birthday = strpart(strstr($result['body'], 'mento'), 'value="', '"').' 00:00:00 -0300';
        $birthloc = explode(' / ', strpart(strstr($result['body'], 'al de nas'), 'value="', '"'));

        $user['name']       = upname(strpart(strstr($result['body'], 'nome'), 'value="', '"'));
        $user['contact']    = array(
            'email'    => strtolower(strpart(strstr($result['body'], 'email'), 'value="', '"')),
            'landline' => preg_replace('~[\(|\)|\s|\-|\+]~', '',strpart(strstr($result['body'], 'fone'), 'value="', '"')),
            'phone'    => preg_replace('~[\(|\)|\s|\-|\+]~', '',strpart(strstr($result['body'], 'ular'), 'value="', '"')),
        );

        $user['documentation'] = array(
            'cpf'        => strpart(strstr($result['body'], 'CPF'), 'value="', '"'),
            'rg'         => array(
                'number'    => preg_replace('~[a-z|A-Z|\s|\-|\.]~', '',strpart(strstr($result['body'], 'numeroRG'), 'value="', '"')),
                'expediter' => strpart(strstr($result['body'], 'orgaoRG'), 'value="', '"'),
            ),
            'university' => array(
                'registry' => $registry,
                'msginbox' => $msginbox,
            ),
            'father'     => upname(strpart(strstr($result['body'], 'Pai'), 'value="', '"')),
            'mother'     => upname(strpart(strstr($result['body'], 'Mae'), 'value="', '"')),
            'birthday'   => date_format(date_create_from_format('d/m/Y H:i:s O', $birthday), 'r'),
            'birthplace' => upname($birthloc[0]).', '.$birthloc[1],
        );

        $result['body'] = strpart($result['body'], 'CEP');
        $complement = explode('/', strpart(strstr($result['body'], 'complemento'), 'value="', '"').'/');
        $user['address'] = array(
            'stret'        => upname(strpart(strstr($result['body'], 'endereco'), 'value="', '"')),
            'number'       => trim($complement[0]),
            'complement'   => trim(upname($complement[1])),
            'neighborhood' => upname(strpart(strstr($result['body'], 'bairro'), 'value="', '"')),
            'cite'         => upname(strpart(strstr($result['body'], 'municipio'), 'value="', '"')),
            'state'        => strpart(strstr($result['body'], 'UF'), 'value="', '"'),
            'CEP'          => strpart(strstr($result['body'], 'CEP'), 'value="', '"'),
        );

        $user['last_alteration'] = date_format(date_create_from_format('d/m/Y H:i:s O', $last_mod), 'r');

        if(!$registry || !$msginbox)
            return SigaError::report('SIGA_NO_LOGGED');
        return $user;
    }

    public function grade() {
		if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!isset($_SESSION['session']))
            return SigaError::report('SIGA_PAGE_NOT_LOADED');
        
        $result = get('/siga/academico/acessoaluno/formNota');
        if(isset($result['error'])) return $result;
    
        $data = strpart($result['body'], 'culas.setData( ', ");");
        $data = json_extract($data);
        $classes = array();
        
        foreach($data as $class) {

            $result['body'] = strstr($result['body'], $class->codDisciplina."(".$class->turma.")");
            $id             = strpart($result['body'], 'id="bgMatricula', '"');
            $gmethod        = strpart(strstr($result['body'], 'utilizado:'), '<b>', "<");

            $tdata    = explode('<span', strpart(strstr($result['body'], 'Docentes:'), 'mBox', '</div>'));
            $teathers = array();
            foreach($tdata as $key => $teather) {
                if($key == 0) continue;
                $teathers[] = upname(strpart($teather, '>', '<'));
            }

            // ---------------------------
            // ATENTION:  Need observation
            // ---------------------------
            $tdata = json_extract(strpart($result['body'], $id.'.setData( ', ");"));

            $tests  = array();
            foreach($tdata as $test) {
                $maxgrade = strpart(strstr(strstr($test, $id), $id), '<td', 'mProgressBar');
                $tests[] = array(
                    "grade"       => $test->nota,
                    "maxgrade"    => strstr(explode('>',substr(strstr(strstr($maxgrade, ' >'), ' >'), 2))[13], '<', true),
                    "description" => $test->descricao,
                    "date"        => $test->dataAplicacao,
                    "weinght"     => $test->peso,
                );
            }

            $classes[$id] = array(
                "name"        => upname($class->nomeDisciplina),
                "code"        => $class->codDisciplina,
                "letter"      => $class->turma,
                "teathers"    => $teathers,
                "status"      => $class->situacao,
                "fullgrade"   => $class->nota,
                "grademethod" => $gmethod,
                "tests"       => $tests,
                "year"        => strstr($class->anoSemestre, '/', true),
                "halfyear"    => strpart($class->anoSemestre, '/'),
            );
        }
        return $classes;
    }

    public function history() {
		if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!isset($_SESSION['session']))
            return SigaError::report('SIGA_PAGE_NOT_LOADED');
        
        $result = get('/siga/academico/acessoaluno/formEmitirHistorico');
        if(isset($result['error'])) return $result;

        $result['body'] = strpart($result['body'], 'icula.setData( ', 'require');
        $classes = json_extract(strpart($result['body'], null, ');'));

        $history = array();
        $history['active'] = array();
        foreach($classes as $class) $history['active'][] = array(
            'name'     => upname($class->nomeDisciplina),
            'code'     => $class->codDisciplina,
            'letter'   => $class->turma,
            'status'   => $class->situacao,
            'year'     => $class->ano,
            'halfyear' => $class->semestre,
        );

        $classes = json_extract(strpart($result['body'], 'orico.setData( ', ');'));

        $history['finished'] = array();
        foreach($classes as $class) $history['finished'][$class->idHistorico] = array(
            'name'     => upname($class->nomeDisciplina),
            'code'     => $class->codDisciplina,
            'letter'   => $class->turma,
            'status'   => $class->situacao,
            'grade'    => $class->nota,
            'year'     => $class->ano,
            'halfyear' => $class->semestre,
        );
        return $history;
    }

    public function pre_registration() {
		if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!isset($_SESSION['session']))
            return SigaError::report('SIGA_PAGE_NOT_LOADED');
    
        $result = get('/siga/academico/prematricula/formMatricula');
        if(isset($result['error'])) return $result;

        $result['body'] = strpart($result['body'], 'var fase = ', 'require');

        // -----------------------------------
        // ATENTION: Incorect names and styles
        // -----------------------------------

        $data = array(
            'fase'        => substr($result['body'], 0, 1),
            'coordenator' => strpart(strstr($result['body'], 'acess'), "'", "'") == 'N'?false:true,
            'finisher'    => strpart(strstr($result['body'], 'forma'), "'", "'")?false:true,
        );

        $result['body'] = '[{"'.strpart($result['body'], 'var ', 'var acess').'}]';
        $result['body'] = str_replace(['var ', ' = '], ['}, {"', '": '], $result['body']);
        $data['data']   = json_decode($result['body']);
        
        return $data;
    }

}

} // End of namespace

?>