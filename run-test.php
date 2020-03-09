<?PHP 

require "src/SigaLogin.php";
require 'src/SigaUser.php';
function show($data) {echo json_encode($data);}

// Test siga load
//$siga = new MySIGA\SigaLogin();
//show($siga->load(false));
//show($siga->login($_POST["cpf"], $_POST["pass"]));
//show($siga->status());
//show($siga->logout());

$user = new MySiga\SigaUser();
show($user->info());

?>