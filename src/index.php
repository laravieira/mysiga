<?PHP

require 'vendor/autoload.php';

use Mezon\Router\Router;
use MySiga\MySiga;
use MySiga\MySigaAcademic;

date_default_timezone_set('America/Sao_Paulo');
$router = new Router();



// MySiga
$router->addRoute('/ping',         'MySiga\MySiga::ping');
$router->addRoute('/load',         function() {return (new MySiga())->begin();});
$router->addRoute('/load/captcha', function() {return (new MySiga())->begin(true);});


// MySigaUser
$router->addRoute('/user',                     'MySiga\MySigaUser::user');
$router->addRoute('/user/logout',              'MySiga\MySigaUser::logout');
$router->addRoute('/user/message',             'MySiga\MySigaUser::messages');
$router->addRoute('/user/message/coordenator', 'MySiga\MySigaUser::coordenatorMessage');
$router->addRoute('/user/lock',                'MySiga\MySigaUser::lock');
$router->addRoute('/user/skincolor',           'MySiga\MySigaUser::skinColor');
$router->addRoute('/user/data',                'MySiga\MySigaUser::data');
$router->addRoute('/user/cep/[i:cep]',         'MySiga\MySigaInput::cep');
$router->addRoute('/user/login',               'MySiga\MySigaInput::login',     'POST');
$router->addRoute('/user/login/old',           'MySiga\MySigaInput::oldLogin',  'POST');
$router->addRoute('/user/update/skincolor',    'MySiga\MySigaInput::skinColor', 'POST');
$router->addRoute('/user/update/password',     'MySiga\MySigaInput::password',  'POST');
$router->addRoute('/user/update/address',      'MySiga\MySigaInput::address',   'POST');
$router->addRoute('/user/update/contact',      'MySiga\MySigaInput::contact',   'POST');


// MySigaAcademic
$router->addRoute('/academic/grade',                 'MySiga\MySigaAcademic::grade');
$router->addRoute('/academic/history',               'MySiga\MySigaAcademic::history');
$router->addRoute('/academic/schedule/[a:code]',     'MySiga\MySigaInput::schedule');
$router->addRoute('/academic/registration',          function() {return MySigaAcademic::registration();});
$router->addRoute('/academic/registration/pre',      'MySiga\MySigaAcademic::preRegistration');
$router->addRoute('/academic/registration/[a:view]', 'MySiga\MySigaInput::registration');
$router->addRoute('/academic/ira',                   'MySiga\MySigaAcademic::ira');
$router->addRoute('/academic/ira/chart',             'MySiga\MySigaAcademic::iraCharts');
$router->addRoute('/academic/ira/chart/[a:a]/[a:d]', 'MySiga\MySigaInput::iraChart');


// MySigaDepartment
$router->addRoute('/department',                          'MySiga\MySigaDepartment::list');
$router->addRoute('/department/[i:id]',                   'MySiga\MySigaInput::department');
$router->addRoute('/department/[i:id]/[i:y]/[a:s]',       'MySiga\MySigaInput::department');
$router->addRoute('/department/room/[i:room]',            'MySiga\MySigaInput::room');
$router->addRoute('/department/[i:id]/rooms',             'MySiga\MySigaInput::rooms');
$router->addRoute('/department/[i:id]/[i:y]/[a:s]/rooms', 'MySiga\MySigaInput::rooms');
$router->addRoute('/department/semester',                 'MySiga\MySigaDepartment::semesters');
$router->addRoute('/department/semester/[i:id]',          'MySiga\MySigaInput::semesterById');
$router->addRoute('/department/semester/[i:y]/[a:s]',     'MySiga\MySigaInput::semesterByYear');


// Not Found for all requests types
$router->addRoute('*', 'MySigaInput::all', ['GET', 'POST', 'PUT', 'DELETE', 'OPTION', 'PATCH']);

MySiga::init($router);