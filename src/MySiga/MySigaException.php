<?PHP

namespace MySiga;

use Exception;

class MySigaException extends Exception {
    public function sigaSession() {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        if(!empty($_SESSION['session']))
            return $_SESSION['session'];
        return null;
    }
    
    public function clientSession(): bool|string
    {
        if(session_status() != PHP_SESSION_ACTIVE)
            session_start();
        return session_id();
    }

    public function sigaServer() {
        if(isset($_SESSION['server']))
            return $_SESSION['server'];
        return null;
    }

    public function URI() {
        if(isset($_SERVER['REQUEST_URI']))
            return $_SERVER['REQUEST_URI'];
        return null;
    }
}
