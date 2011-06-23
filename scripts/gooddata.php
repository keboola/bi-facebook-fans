<?php
define('ROOT_PATH', dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
define('APPLICATION_PATH', ROOT_PATH . '/application');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    'production',
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();

// Setup console input
$opts = new Zend_Console_Getopt(array(
	'table|t=s' => 'table option, with required string parameter'
));
$opts->setHelp(array(
	't' => 'Name of the table to export.'
));
try {
	$opts->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	echo $e->getUsageMessage();
	exit;
}

switch($opts->getOption('table')) {
	case 'days':
		$_t = new Model_Days();
		$output = '"id","date","dau","mau","views","viewsunique","viewslogin","viewslogout","viewstotal","likestotal",'
				  .'"likesadded","likesremoved","contentlikesadded","","comments","feedviews","feedviewsunique",'
				  .'"wallposts","wallpostsunique","photos","photoviews","photoviewsunique","videos","videoplays",'
				  .'"videoplaysunique"'."\n";
		foreach($_t->fetchAll() as $r) {
			$output .= '"'.$r->id.'",'
			           . '"'.$r->date.'",'
			           . '"'.$r->dau.'",'
					   . '"'.$r->mau.'",'
			           . '"'.$r->views.'",'
                       . '"'.$r->viewsUnique.'",'
                       . '"'.$r->viewsLogin.'",'
                       . '"'.$r->viewsLogout.'",'
                       . '"'.$r->totalViews().'",'
                       . '"'.$r->likesTotal.'",'
					   . '"'.$r->likesAdded.'",'
					   . '"'.$r->likesRemoved.'",'
					   . '"'.$r->contentLikesAdded.'",'
					   . '"'.$r->contentLikesRemoved.'",'
                       . '"'.$r->comments.'",'
                       . '"'.$r->feedViews.'",'
                       . '"'.$r->feedViewsUnique.'",'
                       . '"'.$r->wallPosts.'",'
                       . '"'.$r->wallPostsUnique.'",'
                       . '"'.$r->photos.'",'
                       . '"'.$r->photoViews.'",'
                       . '"'.$r->photoViewsUnique.'",'
                       . '"'.$r->videos.'",'
                       . '"'.$r->videoPlays.'",'
                       . '"'.$r->videoPlaysUnique.'"'
			           . "\n";
		}
		echo $output;
		break;
	case 'rDaysReferrals':
		$_t = new Model_DaysReferrals();
		$output = '"id","idday","idreferral","views"'."\n";
		foreach($_t->fetchAll() as $r) {
            $output .= '"'.$r->id.'",'
			           . '"'.$r->idDay.'",'
			           . '"'.$r->idReferral.'",'
			           . '"'.$r->views.'"'
			           . "\n";
		}
		echo $output;
		break;
	case 'referrals':
		$_t = new Model_Referrals();
		$output = '"id","name","type"'."\n";
		foreach($_t->fetchAll() as $r) {
			$output .= '"'.$r->id.'",'
			           . '"'.$r->name.'",'
			           . '"'.$r->type.'"'
			           . "\n";
		}
		echo $output;
		break;
	case 'userCountries':
		$_t = new Model_DaysUserCountries();
		$output = '"id","idday","country","views"'."\n";
		foreach($_t->fetchAll() as $r) {
			$output .= '"'.$r->id.'",'
			           . '"'.$r->idDay.'",'
					   . '"'.$r->country.'",'
			           . '"'.$r->views.'"'
			           . "\n";
		}
		echo $output;
		break;
	default:
		echo $opts->getUsageMessage();
}
