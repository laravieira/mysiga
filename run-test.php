<?PHP 

require "src/SigaLogin.php";
require 'src/SigaUser.php';
function show($data) {echo json_encode($data);}

// Test siga load
$siga = new MySIGA\SigaLogin();
//show($siga->load(false));
show($siga->login($_GET["cpf"], $_GET["pass"]));
//show($siga->status());
//show($siga->logout());
show($siga->transfer());

$user = new MySiga\SigaUser();
//show($user->data());
//show($user->history());
//show($user->pre_registration());

?>