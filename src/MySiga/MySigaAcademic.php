<?PHP 

namespace MySiga;

use DateTime;
use function Scraping\strpart;
use function Scraping\strmpart;
use function Scraping\strmstr;
use function Scraping\upname;

class MySigaAcademic {
    /**
     * @throws MySigaException
     */
    static function grade(): array
    {
        $scp  = MySiga::load();
        $data = $scp->get('/siga/academico/acessoaluno/formNota');
        
        $value   = explode('{', strpart($data['content'], 'gridMatriculas.setData', ']);'));
        $classes = array();
        $first   = true;
        foreach($value as $class) {
            if($first) {
                $first = false;
                continue;
            }
    
            // Get class metadata
            $code    = strpart($class, 'codDisciplina:"', '"');
            $letter  = strpart($class, 'turma:"', '"');
            $id      = strmpart($data['content'], $code.'('.$letter.')', 'id="bgMatricula', '"');
            $gmethod = strpart(strmstr($data['content'], $id, 'Tipo de cálculo utilizado:'), '<b>', '<');

            // Get all teathers names
            $value2   = explode('class="mLabel"', strstr(strmstr($data['content'], $id, 'Docentes:', 'id="control'), '</div>', true));
            $teathers = array();
            $first2   = true;
            foreach($value2 as $teather) {
                if($first2) {
                    $first2 = false;
                    continue;
                }
                $teathers[] = upname(strpart($teather, '>', '<'));
            }

            // Get all tests information
            $value2 = explode('{', strstr(strstr($data['content'], 'gridNotas'.$id.'.setData'), ';', true));
            $tests  = array();
            $first2 = true;
            foreach($value2 as $test) {
                if($first2) {
                    $first2 = false;
                    continue;
                }
                $maxgrade = strstr(strmstr($data['content'], $id, $id, '<td'), 'mProgressBar', true);
                $tests[] = array(
                    'grade'       => strmpart($test, 'nota',          '"', '"'),
                    'maxgrade'    => strstr(explode('>',substr(strmstr($maxgrade, ' >', ' >'), 2))[13], '<', true),
                    'description' => strmpart($test, 'descricao',     '"', '"'),
                    'date'        => strmpart($test, 'dataAplicacao', '"', '"'),
                    'weinght'     => strmpart($test, 'peso',          '"', '"'),
                );
            }
    
            $classes[$id] = array(
                'id'          => $id,
                'name'        => upname(strpart($class, 'nomeDisciplina:"', '"')),
                'code'        => $code,
                'letter'      => $letter,
                'teathers'    => $teathers,
                'status'      => strpart($class, 'situacao:"', '"'),
                'fullgrade'   => strpart($class, 'nota:"', '"'),
                'grademethod' => $gmethod,
                'tests'       => $tests,
                'year'        => strpart($class, 'anoSemestre:"', '/'),
                'halfyear'    => strmpart($class, 'anoSemestre:"', '/', '"'),
            );
        }
        return $classes;
    }

    /**
     * @throws MySigaException
     */
    static function history(): array
    {
        $scp  = MySiga::load();
        $data = $scp->get('/siga/academico/acessoaluno/formEmitirHistorico');
        
        $data = explode('.setData', $data['content']);
        if(count($data) < 2)
            return array();

        $classes = explode('{', strstr(substr($data[2], 5), ']);', true).']');
        
        $history = array();
        foreach($classes as $class) {
            $id   = strmpart($class, 'idHistorico', '"', '"');
            $name = upname(strpart($class, 'nomeDisciplina:"', '"'));
            $history[$id] = array(
                'id'       => $id,
                'name'     => $name,
                'code'     => strpart($class, 'codDisciplina:"', '"'),
                'letter'   => strpart($class, 'turma:"',  '"'),
                'status'   => strpart($class, 'situacao:"', '"'),
                'grade'    => strpart($class, 'nota:"',  '"'),
                'year'     => strpart($class, 'ano:"',  '"'),
                'halfyear' => strpart($class, 'semestre:"', '"'),
            );
        }
        return $history;
    }

    /**
     * @throws MySigaException
     */
    static function preRegistration(): bool|array
    {
        $scp  = MySiga::load();
        $data = $scp->get('/siga/academico/prematricula/formMatricula');
        
        $data = strstr($data['content'], '" class="mScripts">');
        
        $data = strpart($data, 'var fase', 'require', true);
        $data = explode("\n", $data);
        
        $json = array();
        foreach($data as $line) {
            if(str_contains($line, 'var ')) {
                $value = trim(substr($line, strpos($line, '=')+2), '"\';');
                if(intval($value))
                    $value = intval($value);
                $i = strstr(substr($line, strpos($line, 'var ')+4), ' ', true);
                $json[$i] = strlen($value)>1?json_decode($value):$value;
            }
        }

        if(count($data) < 3) return false;
        $toStyleUp = array('turmasOferecidas', 'selecionadas', 'matriculas', 'resultados');
        foreach($toStyleUp as $section) {
            foreach($json[$section] as $class) {
                $class->disciplina = trim($class->disciplina);
                $class->nome = upname($class->nome);
            }
        }
        foreach($json['docentes'] as $class) {
            $class->disciplina = trim($class->disciplina);
            $class->docente = upname($class->docente);
        }
        return $json;
    }

    /**
     * @throws MySigaException
     */
    static function registration(bool $link=true, bool $download=false): ?array
    {
        // MySiga::get('academico/acessoaluno/formComprovanteMatricula', false);
        $data = array(
            '__LAYOUT'         => 'default',
            '__ELEMENT'        => 'centerPane',
            '__TEMPLATE'       => 'base',
            '__ISFILEUPLOAD'   => 'no',
            '__ISAJAXEVENT'    => 'no',
            '__EVENTTARGET'    => 'btnPost',
            'idPrograma'       => 199543,
            'ajaxResponseType' => 'json',
        );
        $header = array('X-Requested-With' => 'XMLHttpRequest');
    
        $scp  = MySiga::load();
        $data = $scp->post('/siga/academico/aluno/repComprovanteMatricula', $data, $header);

        $json = json_decode($data['content']);
        $name = 'Comprovante de Matrícula - '.(new DateTime('now'))->format('d-m-Y').'.pdf';
        
        if($link) return array(
            'name'   => $name,
            'type'   => 'application/pdf',
            'outway' => array(
                'cookie' => 'PHPSESSID='.$scp->session(),
                'url'    => strpos($json->data, 'Nenhum registro encontrado.')?null:$json->data,
            ),
        );

        if(!strpos($json->data, 'Nenhum registro encontrado.'))
            $data = $scp->get(strstr($json->data, '/core'));
        else
            throw new MySigaException('You are not registered in any class.', 404);
        
        if($download) {
            header('Content-Type: application/force-download');
            header('Content-Type: application/octet-stream');
            header('Content-Type: application/download');
            header('Content-Disposition: attachment; filename="'.$name.'"');

        }else {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="'.$name.'"');
        }
        header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0, max-age=0');
        header('Pragma: public');
        header('Expires: 0');
        echo $data['content'];
        return null;
    }

    /**
     * @throws MySigaException
     */
    static function schedule(string $code='', string $name=''): array
    {

        // Find class by code (use the first class of search result)
        $post = array('filterLookup::disciplina' => $code,'filterLookup::nome' => $name);
        $scp  = MySiga::load();
        $data = $scp->post('/siga/academico/disciplina/lookup?__lookupName=lookupDisciplinaLookup', $post);
        $class = strpart(trim(strstr($data['content'], '.setData(')), '{', ']');

        if(!isset($class))
            throw new MySigaException('There is no class with this code or name.', 404);

        $post = array(
            'disciplina'   => strmpart($class, 'disciplina', '"', '"'),
            'nome'         => strmpart($class, 'nome', '"', '"'),
            'idDisciplina' => strmpart($class, 'idDisciplina', '"', '"'),
            'credito'      => intval(strmpart($class, 'credito', '"', '"')),
        );

        $scp  = MySiga::load();
        $data = $scp->post('/siga/academico/acessoaluno/formConsultaHorario', $post, null, true);
        $set  = strpart(trim(strstr($data['content'], '.setData(')), '{', ']);');
        $data = explode('{', $set);
        
        $rooms = array();
        foreach($data as $schedule) {
            $room = strmpart($schedule, 'turma', '"', '"');
            if(!isset($rooms[$room]))
                $rooms[$room] = array();

            $weekday = intval(strmpart($schedule, 'dia', '"', '"'));
            $day = match($weekday) {
                2 => 'Monday', 3 => 'Tuesday',  4 => 'Wednesday', 5 => 'Thursday',
                6 => 'Friday', 7 => 'Saturday', 8 => 'Sunday',    default => null
            };

            $rooms[$room][$day] = array(
                'weekday' => $weekday,
                'start'   => strmpart($schedule, 'Inicio', '"', '"'),
                'end'     => strmpart($schedule, 'Fim', '"', '"'),
            );
        }

        return array(
            'code'   => $post['disciplina'],
            'name'   => upname($post['nome']),
            'id'     => $post['idDisciplina'],
            'credit' => $post['credito'],
            'rooms'  => $rooms,
        );
    }

    /**
     * @throws MySigaException
     */
    static function ira(): array
    {
        $scp  = MySiga::load();
        $data = $scp->post('/siga/academico/aluno/formCalcularIRA', array('calcularIRA' => 1));
        
        $set = strstr(strstr($data['content'], '.setData('), ']', true).']';
        $set = strpos($set, '{')?explode('{', strpart($set, '{', ']')):array();
        
        $score = 0;
        $charge = 0;

        $classes = array();
        foreach($set as $class) {
            $semester = explode('/', strmpart($class, 'column0', '"', '"'));
            $code = strmpart($class, 'column1', '"', '"');
            $note = strmpart($class, 'column6', '"', '"');

            $classes[$code] = array(
                'code'   => $code,
                'name'   => upname(strip_tags(strmpart($class, 'column2', '"', '"'))),
                'status' => match(strmpart($class, 'column7', '"', '"')) {
                    'Aprovado' => 'Aproved',
                    'Rep Nota' => 'Reproved',
                    default => 'Rejected'
                },
                'year'     => intval($semester[0]),
                'halfyear' => intval($semester[1]),
                'grade'    => floatval(strmpart($class, 'column3', '"', '"')),
                //'charged'  => intval(strmpart($class, 'column4', '"', '"')),
                //'score'    => floatval(strmpart($class, 'column5', '"', '"')),
                'charge'   => intval(strmpart($class, 'column8', '"', '"')),
                'note'     => (empty($note) || $note == '-')?null:$note
            );
            $score += $classes[$code]['value']*$classes[$code]['charge'];
            $charge += $classes[$code]['charge'];
        }

        return array(
            'ira' => ($charge)?floatval($score)/floatval($charge):0,
            'classes' => $classes,
        );
    }

    /**
     * @throws MySigaException
     */
    static function iraChart(bool $avarege=false, bool $minmax=false): array
    {
        $scp = MySiga::load();

        $post = array(
            'rgVisual'      => $minmax?2:1,
            'exibeIRACurso' => $avarege?1:0,
            'calcularIRA'   => 1
        );

        $data = $scp->post('/siga/academico/aluno/ajaxGraficoIRA', $post);
        return array(
            'avarege' => $avarege,
            'minmax' => $minmax,
            'link' => strmpart($data['content'], 'bodyLayout', 'src="', '"'),
            'cookie' => MYSIGA_SESSNAME.'='.$scp->session(),
        );
    }

    /**
     * @throws MySigaException
     */
    static function iraCharts(): array
    {
        $scp = MySiga::load();
        return array(
            'charts' => array(
                'basic'          => self::iraChart()['link'],
                'basic-avarege'  => self::iraChart(true)['link'],
                'minmax'         => self::iraChart(false, true)['link'],
                'minmax-avarege' => self::iraChart(true, true)['link'],
            ),
            'cookie' => MYSIGA_SESSNAME.'='.$scp->session(),
        );
    }

}
