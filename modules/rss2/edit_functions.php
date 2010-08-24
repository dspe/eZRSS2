<?php

/**
 * eZRSS2 edit export functions
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

class eZRSS2EditFunction
{
    /*!
     Store RSSExport

     \static
     \param Module
     \param HTTP
     \param publish ( true/false )
    */
    static function storeRSSExport( $Module, $http, $publish = false )
    {
        $valid = true;
        $validationErrors = array();

        /* Kill the RSS cache in all siteaccesses */
        $config = eZINI::instance( 'site.ini' );
        $cacheDir = eZSys::cacheDirectory();

        $availableSiteAccessList = $config->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
        foreach ( $availableSiteAccessList as $siteAccess )
        {
            $cacheFilePath = $cacheDir . '/rss/' . md5( $siteAccess . $http->postVariable( 'Access_URL' ) ) . '.xml';
            $cacheFile = eZClusterFileHandler::instance( $cacheFilePath );
            if ( $cacheFile->exists() )
            {
                $cacheFile->delete();
            }
        }

        $db = eZDB::instance();
        $db->begin();
        /* Create the new RSS feed */
        for ( $itemCount = 0; $itemCount < $http->postVariable( 'Item_Count' ); $itemCount++ )
        {
            $rssExportItem = eZRSS2ExportItem::fetch( $http->postVariable( 'Item_ID_'.$itemCount ), true, eZRSSExport::STATUS_DRAFT );
            if( $rssExportItem == null )
            {
                continue;
            }

            // RSS is supposed to feed certain objects from the subnodes
            if ( $http->hasPostVariable( 'Item_Subnodes_'.$itemCount ) )
            {
                $rssExportItem->setAttribute( 'subnodes', 1 );
            }
            else // Do not include subnodes
            {
                $rssExportItem->setAttribute( 'subnodes', 0 );
            }

            $rssExportItem->setAttribute( 'class_id', $http->postVariable( 'Item_Class_'.$itemCount ) );
            $class = eZContentClass::fetch(  $http->postVariable( 'Item_Class_'.$itemCount ) );

            $titleClassAttributeIdentifier = $http->postVariable( 'Item_Class_Attribute_Title_'.$itemCount );
            $descriptionClassAttributeIdentifier = $http->postVariable( 'Item_Class_Attribute_Description_'.$itemCount );
            $categoryClassAttributeIdentifier = $http->postVariable( 'Item_Class_Attribute_Category_'.$itemCount );

            if ( !$class )
            {
                $validated = false;
                $validationErrors[] = ezi18n( 'kernel/rss/edit_export',
                                              'Selected class does not exist' );
            }
            else
            {
                $dataMap = $class->attribute( 'data_map' );
                if ( !isset( $dataMap[$titleClassAttributeIdentifier] ) )
                {
                    $valid = false;
                    $validationErrors[] = ezi18n( 'kernel/rss/edit_export',
                                                  'Invalid selection for title class %1 does not have attribute "%2"', null,
                                                  array( $class->attribute( 'name'), $titleClassAttributeIdentifier ) );
                }
                if ( !isset( $dataMap[$descriptionClassAttributeIdentifier] ) )
                {
                    $valid = false;
                    $validationErrors[] = ezi18n( 'kernel/rss/edit_export',
                                                  'Invalid selection for description class %1 does not have attribute "%2"', null,
                                                  array( $class->attribute( 'name'), $descriptionClassAttributeIdentifier ) );
                }
                if ( $categoryClassAttributeIdentifier != '' && !isset( $dataMap[$categoryClassAttributeIdentifier] ) )
                {
                    $valid = false;
                    $validationErrors[] = ezi18n( 'kernel/rss/edit_export',
                                                  'Invalid selection for category class %1 does not have attribute "%2"', null,
                                                  array( $class->attribute( 'name'), $categoryClassAttributeIdentifier ) );
                }
            }

            $rssExportItem->setAttribute( 'title', $http->postVariable( 'Item_Class_Attribute_Title_'.$itemCount ) );
            $rssExportItem->setAttribute( 'description', $http->postVariable( 'Item_Class_Attribute_Description_'.$itemCount ) );
            $rssExportItem->setAttribute( 'category', $http->postVariable( 'Item_Class_Attribute_Category_'.$itemCount ) );
            if( $publish && $valid )
            {
                $rssExportItem->setAttribute( 'status', 1 );
                $rssExportItem->store();
                // delete drafts
                $rssExportItem->setAttribute( 'status', 0 );
                $rssExportItem->remove();
            }
            else
            {
                $rssExportItem->store();
            }
        }
        $rssExport = eZRSS2Export::fetch( $http->postVariable( 'RSSExport_ID' ), true, eZRSS2Export::STATUS_DRAFT );
        $rssExport->setAttribute( 'title', $http->postVariable( 'title' ) );
        $rssExport->setAttribute( 'url', $http->postVariable( 'url' ) );
        // $rssExport->setAttribute( 'site_access', $http->postVariable( 'SiteAccess' ) );
        $rssExport->setAttribute( 'description', $http->postVariable( 'Description' ) );
        $rssExport->setAttribute( 'rss_version', $http->postVariable( 'RSSVersion' ) );
        $rssExport->setAttribute( 'number_of_objects', $http->postVariable( 'NumberOfObjects' ) );
        $rssExport->setAttribute( 'image_id', $http->postVariable( 'RSSImageID' ) );
        if ( $http->hasPostVariable( 'active' ) )
        {
            $rssExport->setAttribute( 'active', 1 );
        }
        else
        {
            $rssExport->setAttribute( 'active', 0 );
        }
        $rssExport->setAttribute( 'access_url', str_replace( array( '/', '?', '&', '>', '<' ), '',  $http->postVariable( 'Access_URL' ) ) );
        if ( $http->hasPostVariable( 'MainNodeOnly' ) )
        {
            $rssExport->setAttribute( 'main_node_only', 1 );
        }
        else
        {
            $rssExport->setAttribute( 'main_node_only', 0 );
        }
		
        
        $rssExport->setAttribute( 'language', $http->postVariable( 'Language' ) );
        
        
        $published = false;
        if ( $publish && $valid )
        {
            $rssExport->store( true );
            // remove draft
            $rssExport->remove();
            $published = true;
        }
        else
        {
            $rssExport->store();
        }
        $db->commit();
        return array( 'valid' => $valid,
                      'published' => $published,
                      'validation_errors' => $validationErrors );
    }
}
?>
