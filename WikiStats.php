<?php
/**
 * This is an example extension. It doesn't actually do anything useful, but
 * can be copied to provide the basis for your own extension.
 */

/** 
 * Prevent a user from accessing this file directly and provide a helpful 
 * message explaining how to install this extension.
 */
if ( !defined( 'MEDIAWIKI' ) ) { 
    if ( !defined( 'MEDIAWIKI' ) ) {
        echo <<<EOT
To install the Example extension, put the following line in your 
LocalSettings.php file: 
require_once( "\$IP/extensions/Example/Example.php" );
EOT;
        exit( 1 );
    }
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits[ 'specialpage' ][] = array(
    'path' => __FILE__,
    'name' => 'WikiStats',
    'author' => array( '[https://sites.google.com/site/emijrp/ emijrp]', 'mangelrp' ), 
    'url' => 'https://www.mediawiki.org/wiki/Extension:WikiStats', 
    'descriptionmsg' => 'wikistats-desc',
    'version' => '0.0.1',
);

// Find the full directory path of this extension
$current_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;

// Autoload this extension's classes
$wgAutoloadClasses[ 'SpecialWikiStats' ] = $current_dir . 'WikiStats.body.php';

// Add the i18n message file
$wgExtensionMessagesFiles[ 'WikiStats' ] = $current_dir . 'WikiStats.i18n.php';

// Tell MediaWiki about the special page
$wgSpecialPages[ 'WikiStats' ] = 'SpecialWikiStats';

//http://www.mediawiki.org/wiki/Manual:$wgResourceModules#Examples
#to add this    <!--[if lte IE 8]><script language="javascript" type="text/javascript" src="../excanvas.min.js"></script><![endif]-->
$wgResourceModules['ext.WikiStats'] = array(
        'scripts' => array('modules/jquery.js', 'modules/jquery.flot.js'),
        'styles' => 'modules/layout.css',
        'dependencies' => array( ), 
        'localBasePath' => dirname( __FILE__ ),
        //'remoteExtPath' => 'WikiStats',
);

