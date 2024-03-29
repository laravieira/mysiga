<?PHP

require 'vendor/autoload.php';
require 'constants.php';

use Mezon\Router\Router;
use MySiga\MySiga;

date_default_timezone_set('America/Sao_Paulo');
set_time_limit(0);
$router = new Router();

function addRoute(string $path, string $controller, string|array $method = 'GET'): void {
    $GLOBALS['router']->addRoute($path, "MySiga\Controller\\$controller::execute", $method);
}

// MySiga
addRoute('/',             'Root');
addRoute('/ping',         'Ping');
addRoute('/load',         'Load');
addRoute('/load/captcha', 'LoadCaptcha');

// MySigaLogin
addRoute('/login',        'Login',       'POST');
addRoute('/login/change', 'LoginChange', 'POST');
addRoute('/login/logout', 'LoginLogout');
addRoute('/login/raw',    'LoginRaw',    'POST');

// MySigaUser
addRoute('/user',                      'User');
addRoute('/user/cep/[i:cep]',          'UserCEP');
addRoute('/user/message/coordination', 'UserCoordinationMessage');
addRoute('/user/detail',               'UserDetails');
addRoute('/user/lock',                 'UserLock');
addRoute('/user/messages',             'UserMessages');
addRoute('/user/skincolor',            'UserSkinColor');
addRoute('/user/update/education',     'UserUpdateEducation', 'POST');
addRoute('/user/update/pis-pasep',     'UserUpdatePISPASEP',  'POST');
addRoute('/user/update/address',       'UserUpdateAddress',   'POST');
addRoute('/user/update/contact',       'UserUpdateContact',   'POST');
addRoute('/user/update/skincolor',     'UserUpdateSkinColor', 'POST');

// MySigaAcademic
addRoute('/academic/grade',                 'AcademicGrade');
addRoute('/academic/history',               'AcademicHistory');
addRoute('/academic/ira',                   'AcademicIRA');
addRoute('/academic/ira/chart',             'AcademicIRACharts');
addRoute('/academic/ira/chart/[a:a]/[a:d]', 'AcademicIRACharts');
addRoute('/academic/pre-registration',      'AcademicPreRegistration');
addRoute('/academic/registration',          'AcademicRegistration');
addRoute('/academic/registration/[a:view]', 'AcademicRegistration');
addRoute('/academic/schedule/[a:code]',     'AcademicSchedule');

// MySigaDepartment
addRoute('/department/[i:id]',                   'Department');
addRoute('/department/[i:id]/[i:y]/[a:s]',       'Department');
addRoute('/department',                          'DepartmentListAll');
addRoute('/department/room/[i:room]',            'DepartmentRoom');
addRoute('/department/[i:id]/rooms',             'DepartmentRooms');
addRoute('/department/[i:id]/[i:y]/[a:s]/rooms', 'DepartmentRooms');
addRoute('/department/semester/[i:id]',          'DepartmentSemesterById');
addRoute('/department/semester/[i:y]/[a:s]',     'DepartmentSemesterByYear');
addRoute('/department/semester',                 'DepartmentSemesters');


// Not Found for all requests types
addRoute('*', 'NotFound', ['GET', 'POST', 'PUT', 'DELETE', 'OPTION', 'PATCH']);

MySiga::init($router);