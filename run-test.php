<?PHP 

require 'src/MySiga.php';
function show($data) {echo json_encode($data);}

//if(session_status() != PHP_SESSION_ACTIVE)
//    session_start();
//session_destroy();

// Test siga load
$siga = new MySIGA\MySiga();
//show($siga->load());
//show($siga->login($_POST['cpf'], $_POST['pass']));
show($siga->status());
//show($siga->logout());

?>