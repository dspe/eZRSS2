<?php

/**
 * eZRSS2 fetch class
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

class rssFetchFunctionCollection
{
	function rssfetchfunctioncollection()
	{
	}
	
	function rssFetch()
	{
		$rssList = eZRSS2Export::fetchList();
		return array( 'result' => $rssList );
	}
	
	function fetchbylanguage( $languages ) {
		
		if( is_array( $languages ) )
		{
			$rssList = eZPersistentObject::fetchObjectList( 
														eZRSS2Export::definition(),
														null,
														array( 'language' => array( $languages ) ), 
														null,
														null
														);
		} else {
			$rssList = false;
		}
		return array( 'result' => $rssList );
	}
}

?>