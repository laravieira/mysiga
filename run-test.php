<?PHP 

require "src/SigaLogin.php";
require 'src/SigaUser.php';
function show($data) {echo json_encode($data);}

// Test siga load
$siga = new MySIGA\SigaLogin();
<<<<<<< HEAD
$user = new MySiga\SigaUser();

if(isset($_GET['load']))
    show($siga->load($_GET['load']));

if(isset($_GET['login_client']))
    show($siga->user_login($_POST["cpf"], $_POST["response"], $_GET['user_login']));

if(isset($_GET['login']))
    show($siga->login($_POST["cpf"], $_POST["pass"]));

if(isset($_GET['status']))
    show($siga->status());

if(isset($_GET['logout']))
    show($siga->logout());

if(isset($_GET['redirect']))
    show($siga->redirect());

if(isset($_GET['data']))
    show($user->data());

if(isset($_GET['grade']))
    show($user->grade());

if(isset($_GET['history']))
    show($user->history());

if(isset($_GET['pre_registration']))
    show($user->pre_registration());

if(isset($_GET['registration']))
    show($user->registration($_GET['registration']));
=======
//show($siga->load(false));
show($siga->login($_GET["cpf"], $_GET["pass"]));
//show($siga->status());
//show($siga->logout());
show($siga->transfer());

$user = new MySiga\SigaUser();
//show($user->data());
//show($user->history());
//show($user->pre_registration());
>>>>>>> 3bb00205cd4ebee17be22fa54c234038b8bcf540

?>