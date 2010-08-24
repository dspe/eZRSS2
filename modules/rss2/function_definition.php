<?php

/**
 * eZRSS2 function definition
 * @copyright Copyright (C) 2010 - Philippe VINCENT-ROYOL. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Philippe VINCENT-ROYOL
 * @version @@@VERSION@@@
 * @package ezrss2
 */

$FunctionList = array();

$FunctionList['FetchAll'] = array( 'name' => 'fetchall',
                                 'call_method' => array( 'include_file' => 'extension/ezrss2/modules/rss2/rss2functioncollection.php',
                                                         'class' => 'rssFetchFunctionCollection',
                                                         'method' => 'rssFetch' ),
                                 'parameter_type' => 'standard',
                                 'parameters' => array ( )
						);
						
$FunctionList['FetchByLanguage'] = array( 'name' => 'fetchbylanguage',
                                 'call_method' => array( 'include_file' => 'extension/ezrss2/modules/rss2/rss2functioncollection.php',
                                                         'class' => 'rssFetchFunctionCollection',
                                                         'method' => 'fetchbylanguage' ),
                                 'parameter_type' => 'standard',
                                 'parameters' => array (
															array( 'name' => 'lang',
                                                                             'type' => 'array',
                                                                             'default' => false,
                                                                             'required' => true )
														)
						);						

?>
