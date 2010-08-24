<?php

/**
 * eZRSS2 list
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

$Module = $Params['Module'];

require_once( "kernel/common/template.php" );
$http = eZHTTPTool::instance();

if ( $http->hasPostVariable( 'NewExportButton' ) )
{
    return $Module->run( 'edit_export', array() );
}
else if ( $http->hasPostVariable( 'RemoveExportButton' ) && $http->hasPostVariable( 'DeleteIDArray' ) )
{
    $deleteArray = $http->postVariable( 'DeleteIDArray' );
    foreach ( $deleteArray as $deleteID )
    {
        $rssExport = eZRSS2Export::fetch( $deleteID, true, eZRSS2Export::STATUS_DRAFT );
        if ( $rssExport )
        {
            $rssExport->remove();
        }
        $rssExport = eZRSS2Export::fetch( $deleteID, true, eZRSS2Export::STATUS_VALID );
        if ( $rssExport )
        {
            $rssExport->remove();
        }
    }
}
else if ( $http->hasPostVariable( 'NewImportButton' ) )
{
    return $Module->run( 'edit_import', array() );
}
else if ( $http->hasPostVariable( 'RemoveImportButton' ) && $http->hasPostVariable( 'DeleteIDArrayImport' ) )
{
    $deleteArray = $http->postVariable( 'DeleteIDArrayImport' );
    foreach ( $deleteArray as $deleteID )
    {
        $rssImport = eZRSS2Import::fetch( $deleteID, true, eZRSS2Import::STATUS_DRAFT );
        if ( $rssImport )
        {
            $rssImport->remove();
        }
        $rssImport = eZRSS2Import::fetch( $deleteID, true, eZRSS2Import::STATUS_VALID );
        if ( $rssImport )
        {
            $rssImport->remove();
        }
    }
}


// Get all RSS Exports
$exportArray = eZRSS2Export::fetchList();
$exportList = array();
foreach( $exportArray as $export )
{
    $exportList[$export->attribute( 'id' )] = $export;
}

// Get all RSS imports
$importArray = eZRSS2Import::fetchList();
$importList = array();
foreach( $importArray as $import )
{
    $importList[$import->attribute( 'id' )] = $import;
}

$tpl = templateInit();

$tpl->setVariable( 'rssexport_list', $exportList );
$tpl->setVariable( 'rssimport_list', $importList );

$Result = array();
$Result['content'] = $tpl->fetch( "design:rss/list.tpl" );
$Result['path'] = array( array( 'url' => 'rss/list',
                                'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );


?>
