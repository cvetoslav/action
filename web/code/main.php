<?php
require_once('entities/user.php');
require_once('config.php');
require_once('common.php');

session_start();

$user = !isset($_SESSION['userId']) ? new User() : User::get($_SESSION['userId']);
if ($user == null) {
    $user = new User();
}

// Update the last page and the one before it
if (isset($_SESSION['lastPage'])) {
    if ($_SESSION['lastPage'] != $_SERVER['REQUEST_URI']) {
        $_SESSION['prevPage'] = $_SESSION['lastPage'];
        $_SESSION['lastPage'] = $_SERVER['REQUEST_URI'];
    }
} else {
    $_SESSION['lastPage'] = $_SERVER['REQUEST_URI'];
}

$showMessage = '';
if (isset($_SESSION['messageType']) && isset($_SESSION['messageText'])) {
    $showMessage .= '<script>showMessage("' . $_SESSION['messageType'] . '", "' . $_SESSION['messageText'] . '");</script>';
    unset($_SESSION['messageType']);
    unset($_SESSION['messageText']);
}

switch ($_GET['page']) {
    case 'home':
        require_once('home.php');
        $page = new HomePage($user);
        break;
    case 'problems':
        require_once('problems.php');
        $page = new ProblemsPage($user);
        break;
    case 'queue':
        require_once('queue.php');
        $page = new QueuePage($user);
        break;
    case 'training':
        require_once('training.php');
        $page = new TrainingPage($user);
        break;
    case 'ranking':
        require_once('ranking.php');
        $page = new RankingPage($user);
        break;
    case 'profile':
        require_once('profile.php');
        $page = new ProfilePage($user);
        $page->init();
        break;
    case 'login':
        require_once('login.php');
        $page = new LoginPage($user);
        break;
    case 'logout':
        $user->logOut();
        break;
    case 'register':
        require_once('register.php');
        $page = new RegisterPage($user);
        break;
    case 'about':
        require_once('about.php');
        $page = new AboutPage($user);
        break;
    case 'help':
        require_once('help.php');
        $page = new HelpPage($user);
        break;
    case 'stats':
        require_once('stats.php');
        $page = new StatsPage($user);
        $page->init();
        break;
    case 'error':
        require_once('error.php');
        $page = new ErrorPage($user);
        break;
    case 'forbidden':
        require_once('forbidden.php');
        $page = new ForbiddenPage($user);
        break;
    default:
        require_once('error.php');
        $page = new ErrorPage($user);
}
$content = $page->getContent();

require('page.html');
?>
