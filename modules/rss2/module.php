<?php

/**
 * eZRSS2 module list
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

$Module = array( 'name' => 'eZRSS2' );

$ViewList = array();
$ViewList['list'] = array(
    'script' => 'list.php',
    'functions' => array( 'edit' ),
    'default_navigation_part' => 'ezsetupnavigationpart',
    'unordered_params' => array( 'language' => 'Language' ) );

$ViewList['edit_export'] = array(
    'script' => 'edit_export.php',
    'functions' => array( 'edit' ),
    'ui_context' => 'edit',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array( 'StoreButton' => 'Store',
                                    'Update_Item_Class' => 'UpdateItem',
                                    'AddSourceButton' => 'AddItem',
                                    'RemoveButton' => 'Cancel',
                                    'BrowseImageButton' => 'BrowseImage',
                                    'RemoveImageButton' => 'RemoveImage' ),
    'params' => array( 'RSSExportID', 'RSSExportItemID', 'BrowseType' ) );

$ViewList['edit_import'] = array(
    'script' => 'edit_import.php',
    'functions' => array( 'edit' ),
    'ui_context' => 'edit',
    'default_navigation_part' => 'ezsetupnavigationpart',
    'single_post_actions' => array( 'StoreButton' => 'Store',
                                    'RemoveButton' => 'Cancel',
                                    'AnalyzeFeedButton' => 'AnalyzeFeed',
                                    'Update_Class' => 'UpdateClass',
                                    'DestinationBrowse' => 'BrowseDestination',
                                    'UserBrowse' => 'BrowseUser' ),
    'params' => array( 'RSSImportID', 'BrowseType' ) );


$ViewList['feed'] = array(
    'script' => 'feed.php',
    'functions' => array( 'feed' ),
    'params' => array ( 'RSSFeed' ) );


$FunctionList = array( );
$FunctionList['feed'] = array();
$FunctionList['edit'] = array();

?>
