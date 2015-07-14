<?php
/**
 * What this script does
 * =====================
 *
 * This is an early incarnation of a script to return a json array
 * containing the most recently modified files in a directory tree.
 *
 * Live example:
 *
 * http://results.swanagesailingclub.org.uk/list/
 *
 * which is scanning all files under here:
 *
 * http://results.swanagesailingclub.org.uk/2015/
 *
 * Currently, it only returns htm files which is set in the array on line 97
 *
 * array('htm')
 *
 * A future iteration of this script will make it better configurable.
 *
 * Why do it I need it?
 * ====================
 *
 * You might want to use such a file list in another application. I am consuming it
 * via a Drupal 7 module on this website:
 *
 * http://www.swanagesailingclub.org.uk
 *
 * Look at the latest results panel on the homepage, and also the race results page here:
 *
 * http://www.swanagesailingclub.org.uk/sailing/results-archive
 *
 * Those are blocks that their data from this script.
 *
 * Installation
 * =============
 * 
 * To follow
 * 
 * How do I use it?
 * ================
 * 
 * Example calls:
 * http://results.swanagesailingclub.org.uk/list/?count=5
 * http://results.swanagesailingclub.org.uk/list
 */

require '../vendor/autoload.php';

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

/************************
 *
 * Settings
 *
 * Edit below here
 ***********************/
/**
 * The default number of results to return
 */
define('DEFAULT_COUNT', 20);

/**
 * The maximum to return. Safety measure in case someone tries to retrieve too
 * many entries
 */
define('MAX_DISPLAY_COUNT', 50);

/**
 * The base url to which the file path will be added
 */
define('BASE_URL', 'http://results.swanagesailingclub.org.uk/2015/');

/**
 * The default log level. To see what the script is doing, change this to Logger::DEBUG
 * @see https://github.com/Seldaek/monolog
 */
define('LOG_LEVEL', Logger::ERROR);

/**
 * The file directory you wish to get a files list for. It should be relative to the script.
 */
define('FILES_DIRECTORY_RELATIVE_TO_SCRIPT','../2015');

/***************************
 *  Stop editing
 ***************************/

try {

    $count = isset($_GET['count']) && (int) $_GET['count'] < MAX_DISPLAY_COUNT ? (int) $_GET['count'] : DEFAULT_COUNT;

    $res = array(
        'error' => 0,
        'data' => getFiles($count, FILES_DIRECTORY_RELATIVE_TO_SCRIPT, LOG_LEVEL)
    );

} catch (Exception $e) {

    $res = array(
        'error' => 1,
        'data' => 'Error! '.$e->getMessage()
    );
}

header('Content-Type: application/json');
echo json_encode($res);


/**
 * The function to actually get the files
 *
 * @param int $count
 * @param string $sourceDir
 * @param int $loglevel
 * @return array
 */
function getFiles($count = DEFAULT_COUNT, $sourceDir = '.', $loglevel){

    $logger = new Logger('name');
    $logger->pushHandler(new StreamHandler('php://stdout', $loglevel));

    // The recursor which will iterate over directories and files
    $rec = new Chalky\Processor\DirectoryRecursor(
        $sourceDir,
        $logger,
        array('htm'), // consider only these file extensions
        array('list') // ignore this directory
    );

    // The processer which will do something on each file. In our case, append the file
    // to a list which will get sorted and sliced to give us the most recent files list.
    $recent = new Chalky\Handler\MostRecentFiles(
        $sourceDir,
        $logger,
        array(
            'base_url' => BASE_URL,
            'number' => $count,
        )
    );

    $rec->addFileProcessor($recent);
    $rec->process();
    return $recent->getLatestFiles();

}