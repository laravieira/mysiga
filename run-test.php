<?PHP 

require "src/MySiga.php";
function show($data) {echo json_encode($data);}

// Test siga load
$siga = new MySIGA\MySiga();
show($siga->load(false));
//show($siga->login($_POST["cpf"], $_POST["pass"]));
//show($siga->status());
//show($siga->logout());

?>