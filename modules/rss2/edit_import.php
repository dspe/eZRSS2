<?php

/**
 * eZRSS2 edit import
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

$Module = $Params['Module'];

require_once( "kernel/common/template.php" );
$http = eZHTTPTool::instance();

//Get RSSImport id if it is accessable
$step = (int)$http->hasPostVariable( 'Step' ) ? $http->postVariable( 'step' ) : 1;
$rssImportID = isset( $Params['RSSImportID'] ) ? $Params['RSSImportID'] : false;

if ( $http->hasPostVariable( 'RSSImport_ID' ) )
{
    $rssImportID = $http->postVariable( 'RSSImport_ID' );
}

if ( $http->hasPostVariable( 'importLanguage' ) ) {
	$RSSLanguage = $http->postVariable( 'importLanguage' );
} else {
	$RSSLanguage = "eng-GB";
}

// Check if valid RSS ID //
if ( !is_numeric( $rssImportID ) )
{
    // Create default rssImport object to use
    $rssImport = eZRSS2Import::create(false, $RSSLanguage );
    $rssImport->store();
    $rssImportID = $rssImport->attribute( 'id' );
}

// Fetch RSS Import object //
$rssImport = eZRSS2Import::fetch( $rssImportID, true, eZRSS2Import::STATUS_DRAFT );
if ( !$rssImport )
{
    $rssImport = eZRSS2Import::fetch( $rssImportID, true, eZRSS2Import::STATUS_VALID );
    if ( $rssImport )
    {
        $rssImport->setAttribute( 'status', eZRSS2Import::STATUS_DRAFT );
        $rssImport->store();
    }
}
if ( !$rssImport )
{
    return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'rss' );
}
else
{
    $timeout = checkTimeout( $rssImport );
    if ( $timeout !== false )
    {
        return $timeout;
    }
}

$importDescription = $rssImport->importDescription();

// Handle RSS module action //
if ( $Module->isCurrentAction( 'AnalyzeFeed' ) ||
     $Module->isCurrentAction( 'UpdateClass' ) )
{
    $version = eZRSS2Import::getRSSVersion( $http->postVariable( 'url' ) );

    if ( !isset( $importDescription['rss_version'] ) ||
         $importDescription['rss_version'] != $version )
    {
        $importDescription['object_attributes'] = array();
        $importDescription['class_attributes'] = array();
    }
    $importDescription['rss_version'] = $version;
    $rssImport->setImportDescription( $importDescription );
    storeRSSImport( $rssImport, $http );
}
else if ( $Module->isCurrentAction( 'Store' ) )
{
    storeRSSImport( $rssImport, $http, true );
    return $Module->redirectTo( '/rss2/list' );
}
else if ( $Module->isCurrentAction( 'Cancel' ) )
{
    $rssImport->remove();
    return $Module->redirectTo( '/rss2/list' );
}
else if ( $Module->isCurrentAction( 'BrowseDestination' ) )
{
    storeRSSImport( $rssImport, $http );
    return eZContentBrowse::browse( array( 'action_name' => 'RSSObjectBrowse',
                                           'description_template' => 'design:rss/browse_destination.tpl',
                                           'from_page' => '/rss2/edit_import/'.$rssImportID.'/destination' ),
                                    $Module );
}
else if ( $Module->isCurrentAction( 'BrowseUser' ) )
{
    storeRSSImport( $rssImport, $http );
    return eZContentBrowse::browse( array( 'action_name' => 'RSSUserBrowse',
                                           'description_template' => 'design:rss/browse_user.tpl',
                                           'from_page' => '/rss2/edit_import/'.$rssImportID.'/user' ),
                                    $Module );
}

// Check if coming from browse, if so store result
if ( isset( $Params['BrowseType'] ) )
{
    switch ( $Params['BrowseType'] )
    {
        case 'destination': // Returning from destination browse
        {
            $nodeIDArray = $http->hasPostVariable( 'SelectedNodeIDArray' ) ? $http->postVariable( 'SelectedNodeIDArray' ) : null;
            if ( isset( $nodeIDArray ) && !$http->hasPostVariable( 'BrowseCancelButton' ) )
            {
                $rssImport->setAttribute( 'destination_node_id', $nodeIDArray[0] );
                $rssImport->store();
            }
        } break;

        case 'user': //Returning from user browse
        {
            $nodeIDArray = $http->postVariable( 'SelectedObjectIDArray' );
            if ( isset( $nodeIDArray ) && !$http->hasPostVariable( 'BrowseCancelButton' ) )
            {
                $rssImport->setAttribute( 'object_owner_id', $nodeIDArray[0] );
                $rssImport->store();
            }
        } break;
    }
}

$tpl = templateInit();

$tpl->setVariable( 'languageAvailable', eZContentLanguage::fetchList()); 

// Get classes and class attributes
$classArray = eZContentClass::fetchList();

$tpl->setVariable( 'rss_class_array', $classArray );
$tpl->setVariable( 'rss_import', $rssImport );
$tpl->setVariable( 'step', $step );

$Result = array();
$Result['content'] = $tpl->fetch( "design:rss/edit_import.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );



function storeRSSImport( $rssImport, $http, $publish = false )
{
    $rssImport->setAttribute( 'name', $http->postVariable( 'name' ) );
    $rssImport->setAttribute( 'url', $http->postVariable( 'url' ) );
    if ( $http->hasPostVariable( 'active' ) )
        $rssImport->setAttribute( 'active', 1 );
    else
        $rssImport->setAttribute( 'active', 0 );

    if ( $http->hasPostVariable( 'Class_ID' ) )
    {
        $rssImport->setAttribute( 'class_id', $http->postVariable( 'Class_ID' ) );
    }
    
    if( $http->hasPostVariable( 'importLanguage' ) ) {
    	$rssImport->setAttribute( 'language', $http->postVariable( 'importLanguage') );	
    }

    $importDescription = $rssImport->importDescription();
    $classAttributeList = eZContentClassAttribute::fetchListByClassID( $rssImport->attribute( 'class_id' ) );

    $importDescription['class_attributes'] = array();
    foreach( $classAttributeList as $classAttribute )
    {
        $postVariableName = 'Class_Attribute_' . $classAttribute->attribute( 'id' );
        if ( $http->hasPostVariable( $postVariableName ) )
        {
            $importDescription['class_attributes'][(string)$classAttribute->attribute( 'id' )] = $http->postVariable( $postVariableName );
        }
    }

    $importDescription['object_attributes'] = array();
    foreach( $rssImport->objectAttributeList() as $key => $attributeName )
    {
        $postVariableName = 'Object_Attribute_' . $key;
        if ( $http->hasPostVariable( $postVariableName ) )
        {
            $importDescription['object_attributes'][$key] = $http->postVariable( $postVariableName );
        }
    }

    $rssImport->setImportDescription( $importDescription );

    if ( $publish )
    {
        $db = eZDB::instance();
        $db->begin();
        $rssImport->setAttribute( 'status', eZRSS2Import::STATUS_VALID );
        $rssImport->store();
        // remove draft
        $rssImport->setAttribute( 'status', eZRSS2Import::STATUS_DRAFT );
        $rssImport->remove();
        $db->commit();
    }
    else
    {
        $rssImport->store();
    }
}

function checkTimeout( $rssImport )
{
    $user = eZUser::currentUser();
    $contentIni = eZINI::instance( 'content.ini' );
    $timeOut = $contentIni->variable( 'RSSImportSettings', 'DraftTimeout' );
    if ( $rssImport->attribute( 'modifier_id' ) != $user->attribute( 'contentobject_id' ) &&
         $rssImport->attribute( 'modified' ) + $timeOut > time() )
    {
        // locked editing
        $tpl = templateInit();

        $tpl->setVariable( 'rss_import', $rssImport );
        $tpl->setVariable( 'rss_import_id', $rssImportID );
        $tpl->setVariable( 'lock_timeout', $timeOut );

        $Result = array();
        $Result['content'] = $tpl->fetch( 'design:rss/edit_import_denied.tpl' );
        $Result['path'] = array( array( 'url' => false,
                                        'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );
        return $Result;
    }
    else if ( $timeOut > 0 && $rssImport->attribute( 'modified' ) + $timeOut < time() )
    {
        $rssImport->remove();
        $rssImport = false;
    }

    return false;
}

?>
