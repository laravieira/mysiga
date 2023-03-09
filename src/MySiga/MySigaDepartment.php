<?PHP

namespace MySiga;

use DateTime;
use DateTimeInterface;
use Exception;
use function Scraping\strpart;
use function Scraping\strmstr;
use function Scraping\upname;

class MySigaDepartment {
    /**
     * @throws MySigaException
     * @throws Exception
     */
    static function list() {

        $cache = MySiga::cache('departments');
        if(isset($cache)) return $cache;

        $scp = MySiga::load();
        $departments = array();
        $pages = 0;
        $page = 1;
        do {
            $post = array(
                'gridFind_PAGING' => 'yes',
                'gridFind_PAGE'   => $page==1?1:($page-1),
                'gridFind_GOPAGE' => $page,
            );
            $data = $scp->post('/siga/academico/departamento/lookup?__lookupName=lookupDeptoLookup', $post);
            
            $set  = explode('{', strpart(trim(strstr($data['content'], '.addGoPage(')), '{', ']'));
            foreach($set as $p) {
                $npage = intval(strpart(strstr($p, 'page'), '"', '"'));
                $pages = max($pages, $npage);
            }

            $set = explode('{', strpart(trim(strstr($data['content'], '.setData(')), '{', ']'));
            foreach($set as $dep) {
                $id   = intval(strpart(strstr($dep, 'idDepto'), '"', '"'));
                $departments[$id] = array(
                    'id'      => $id,
                    'name'    => strpart(strstr($dep, 'nome'), '"', '"'),
                    'acronym' => strpart(strstr($dep, 'depto'), '"', '"'),
                );
            }
            
        }while($page++ < $pages);

        return MySiga::cache('departments', $departments);
    }

    /**
     * @throws MySigaException
     */
    static function department(int $id, int $year=null, string $semester=null): array
    {
        $period = '';
        if(!isset($year))
            $year = intval((new DateTime('now'))->format('Y'));
        if(!isset($semester)) {
            $semesters = MySigaDepartment::semesters()->semesters;
            foreach($semesters as $sem) {
                if($sem->year == $year) {
                    $begin = $sem->begin?(new DateTime())->createFromFormat(DateTimeInterface::RSS, $sem->begin):(new DateTime());
                    $end   = $sem->end?(new DateTime())->createFromFormat(DateTimeInterface::RSS, $sem->end):(new DateTime());
                    $now   = new DateTime('now');
                    if($now >= $begin && $now < $end) {
                        $period = $sem;
                        break;
                    }
                }
            }
        }else
            $period = MySigaDepartment::semesterByYear($year, $semester);

        $post = array(
            'filter::idDepto' => $id,
            'filter::idPeriodoLetivo' => $period->id,
        );
        $scp   = MySiga::load();
        $data  = $scp->post('/siga/academico/acessoaluno/formConsultaPlanoDepartamentalP2', $post);
        $set   = explode('<tr', strpart(strmstr($data['content'], 'bodyLayout', '<tbody>'), '<tr', '</tbody>'));
        $links = strpart(trim(strstr($data['content'], '.registerEvents(')), '(', '});');
        
        $classes = array();
        foreach($set as $class) {
            $text = explode('-', strpart(strstr($class, '<span'), '>', '<'));
            $name = upname(trim($text[1]));
            $code = trim($text[0]);
            if(empty($code))
                continue;

            $rooms = array();
            $text = explode('<a', strpart($class, '<a', '</td'));
            foreach($text as $room) {
                $letter = strpart($room, '>', '<');
                $rcode = strpart($room, '_', '"');
                $rcode = strpart(strstr($links, $rcode), 'P3/', '\\');
                $rooms[$letter] = intval($rcode);
            }

            $classes[$code] = array(
                'name' => $name,
                'rooms' => $rooms,
            );
        }

        return array(
            'id' => $id,
            'semester' => $period,
            'classes' => $classes,
        );
    }

    /**
     * @throws MySigaException
     */
    static function room(int $code): array
    {
        $scp = MySiga::load();
        $schedule = array();
        $reserves = array();
        $teachers = array();
        
        $sPages = 0;
        $rPages = 0;
        $tPages = 0;

        $sPage = 1;
        $rPage = 1;
        $tPage = 1;

        do {
            $post = array(
                'gridFindHorario_PAGING' => 'yes',
                'gridFindHorario_PAGE' => $sPage==1?1:($sPage-1),
                'gridFindHorario_GOPAGE' => $sPage,
                'gridFindHorario__VIEWSTATE' => '',

                'gridFindReserva_PAGING' => 'yes',
                'gridFindReserva_PAGE' => $rPage==1?1:($rPage-1),
                'gridFindReserva_GOPAGE' => $rPage,
                'gridFindReserva__VIEWSTATE' => '',

                'gridFindDocente_PAGING' => 'yes',
                'gridFindDocente_PAGE' => $tPage==1?1:($tPage-1),
                'gridFindDocente_GOPAGE' => $tPage,
                'gridFindDocente__VIEWSTATE' => '',
            );
            $data = $scp->post('/siga/academico/acessoaluno/formConsultaPlanoDepartamentalP3/'.$code, $post);

            $text = explode('-', strpart(strstr($data['content'], 'Disciplina'), 'value="', '"'));
            $period = explode('/', strpart($data['content'], 'Letivo:', '<'));
            $department = strpart(strstr($data['content'], 'setor'), 'value="', '"');
            $room = array(
                'id'         => $code,
                'code'       => trim($text[0]),
                'letter'     => strpart(strstr($data['content'], 'Turma</label'), 'value="', '"'),
                'name'       => upname(html_entity_decode(trim($text[1]), encoding:'UTF-8')),
                'department' => html_entity_decode($department, encoding:'UTF-8'),
                'year'       => intval($period[0]),
                'halyear'    => intval($period[1])?:trim($period[1]),
                'space'      => strpart(strstr($data['content'], 'vagasOfer'), 'value="', '"'),
                'occuped'    => strpart(strstr($data['content'], 'vagasOcup'), 'value="', '"'),
            );

            // decread search string 
            $data['content'] = strstr($data['content'], 'Manager.Grid');
            
            // get pages for schedule table
            $set  = explode('{', strpart(trim(strstr($data['content'], 'Horario.addGoPage(')), '{', ']'));
            foreach($set as $p) {
                $npage = intval(strpart(strstr($p, 'page'), '"', '"'));
                $sPages = max($sPages, $npage);
            }
            
            // get pages for reserves table
            $set  = explode('{', strpart(trim(strstr($data['content'], 'Reserva.addGoPage(')), '{', ']'));
            foreach($set as $p) {
                $npage = intval(strpart(strstr($p, 'page'), '"', '"'));
                $rPages = max($rPages, $npage);
            }
            
            // get pages for teachers table
            $set  = explode('{', strpart(trim(strstr($data['content'], 'Docente.addGoPage(')), '{', ']'));
            foreach($set as $p) {
                $npage = intval(strpart(strstr($p, 'page'), '"', '"'));
                $tPages = max($tPages, $npage);
            }

            // Collect data for schedule table
            $set = strstr(strstr($data['content'], 'Horario.setData('), ']', true).']';
            $set = strpos($set, '{')?explode('{', strpart($set, '{', ']')):array();
            foreach($set as $value) {
                $day = strpart(strstr($value, 'nome'), '"', '"');
                $day = match($day) {
                    'Segunda-feira' => 'Monday',    'Terça-feira'   => 'Tuesday',
                    'Quarta-feira'  => 'Wednesday', 'Quinta-feira'  => 'Thursday',
                    'Sexta-feira'   => 'Friday',    'Sábado'        => 'Saturday',
                    'Domingo'       => 'Sunday',    default         => $day
                };
                $schedule[$day] = array(
                    'day'      => $day,
                    'begin'    => strpart(strstr($value, 'Inicio'), '"', '"'),
                    'end'      => strpart(strstr($value, 'Fim'), '"', '"'),
                    'location' => upname(strpart(strstr($value, 'dep'), '"', '"')),
                );
            }

            // Collect data for reserves table
            $set = strstr(strstr($data['content'], 'Reserva.setData('), ']', true).']';
            $set = strpos($set, '{')?explode('{', strpart($set, '{', ']')):array();
            foreach($set as $value) {
                $id = strpart(strstr($value, 'cod'), '"', '"');
                $reserves[$id] = array(
                    'id'    => $id,
                    'name'  => upname(strpart(strstr($value, 'nome'), '"', '"')),
                    'space' => intval(strpart(strstr($value, 'vagas'), '"', '"')),
                );
            }
            
            // Collect data for teachers table
            $set = strstr(strstr($data['content'], 'Docente.setData('), ']', true).']';
            $set = strpos($set, '{')?explode('{', strpart($set, '{', ']')):array();
            foreach($set as $value)
                $teachers += array(upname(strpart($value, '"', '"')));
            
        }while($sPage++ < $sPages || $rPage++ < $rPages || $tPage++ < $tPages);

        $room['schedule'] = $schedule;
        $room['reserves'] = $reserves;
        $room['teachers'] = $teachers;

        return $room;
    }

    /**
     * @throws MySigaException
     * @throws Exception
     */
    static function rooms(int $id, int $year=null, string $semester=null) {
        $cache = MySiga::cache('rooms/'.$id);
        if(isset($cache))
            return $cache;

        $data = MySigaDepartment::department($id, $year, $semester);
        $department = $data;
        foreach($department['classes'] as $cid => $class) {
            foreach($class['rooms'] as $rid => $room) {
                $data['classes'][$cid]['rooms'][$rid] = MySigaDepartment::room($room);
            }
        }
        return MySiga::cache('rooms/'.$id, $data);
    }

    /**
     * @throws MySigaException
     * @throws Exception
     */
    public static function semesters() {
        $cache = MySiga::cache('semesters');
        if(isset($cache)) return $cache;

        $scp = MySiga::load();
        $semesters = array();
        $pages = 0;
        $page = 1;
        do {
            $post = array(
                'lookupTable_PAGING' => 'yes',
                'lookupTable_PAGE'   => $page==1?1:($page-1),
                'lookupTable_GOPAGE' => $page,
            );
            $data = $scp->post('/siga/academico/periodoletivo/lookup?__lookupName=lookupPeriodoLetivoLookup', $post);
            
            $set  = explode('{', strpart(trim(strstr($data['content'], '.addGoPage(')), '{', ']'));
            foreach($set as $p) {
                $npage = intval(strpart(strstr($p, 'page'), '"', '"'));
                $pages = max($pages, $npage);
            }

            $set = strstr(strstr($data['content'], '.setData('), ']', true).']';
            $set = strpos($set, '{')?explode('{', strpart($set, '{', ']')):array();
            foreach($set as $sem) {
                $id    = intval(strpart(strstr($sem, 'id'), '"', '"'));
                $sems  = strpart(strstr($sem, 'semestre'), '"', '"');
                $begin = strpart(strstr($sem, 'Inicio'), '"', '"');
                $end   = strpart(strstr($sem, 'Fim'), '"', '"');
                $semesters[$id] = array(
                    'id'       => $id,
                    'year'     => intval(strpart(strstr($sem, 'ano'), '"', '"')),
                    'semester' => intval($sems)?:$sems,
                    'begin'    => $begin?(new DateTime())->createFromFormat('d/m/Y', $begin)->format(DateTimeInterface::RSS):null,
                    'end'      => $begin?(new DateTime())->createFromFormat('d/m/Y', $end)->format(DateTimeInterface::RSS):null,
                );
            }
        }while($page++ < $pages);

        return MySiga::cache('semesters', $semesters, 'P1M');
    }

    /**
     * @throws MySigaException
     */
    public static function semesterById(string $id) {
        $semesters = MySigaDepartment::semesters()->semesters;
        if(isset($semesters->$id))
            return $semesters->$id;
        else
            throw new MySigaException('There is no cached registry of this semester id.');
    }

    /**
     * @throws MySigaException
     */
    public static function semesterByYear(int $year, string $semester) {
        $semesters = MySigaDepartment::semesters()->semesters;
        foreach($semesters as $value) {
            if($value->year == $year && $value->semester == $semester)
                return $value;
        }
        throw new MySigaException('There is no cached registry of this semester.');
    }

}
