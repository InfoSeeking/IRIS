<?php
include("autoload.php");
use UnitedPrototype\GoogleAnalytics;

// Initilize GA Tracker
$tracker = new GoogleAnalytics\Tracker('UA-42804042-1', 'rutgers.edu');

// Assemble Visitor information
// (could also get unserialized from database)
$visitor = new GoogleAnalytics\Visitor();
$visitor->setIpAddress($_SERVER['REMOTE_ADDR']);
$visitor->setUserAgent($_SERVER['HTTP_USER_AGENT']);
//$visitor->setScreenResolution('1024x768');

// Assemble Session information
// (could also get unserialized from PHP session)
$session = new GoogleAnalytics\Session();

// Assemble Page information
$page = new GoogleAnalytics\Page('/index.php');
$page->setTitle('API Endpoint');

//track request type
$reqType = new GoogleAnalytics\CustomVariable(1, "requestType", "extract?", GoogleAnalytics\CustomVariable::SCOPE_PAGE);

// Track page view
$tracker->trackPageview($page, $session, $visitor);

echo "Done";