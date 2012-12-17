<?php
class Config extends Constants
{
    //const BASEURL = 'http://217.21.192.132';

    const BASEURL = 'http://application01.target.rug.nl';               // URL of the Monk server
    const SEARCH_SUFFIX = '/rest/rest.php/books/';                      // Retrieval part for Book collection
    const SUGGESTION_SUFFIX = '/rest/rest.php/suggestions/';            // URI of the suggestions
    const RESULT_SUFFIX = '/rest/rest.php/index/';                      // Where to get the results
    const IMG_URL = 'http://application01.target.rug.nl';               // URL for the image retrieval
    const IMG_SUFFIX = '/monk';                                         // suffix for the image retrieval
//    const SUGGESTION_URL = 'http://na.rolffokkens.nl/rest_ts.php/suggestions/';
    const SUGGESTION_URL = 'http://application01.target.rug.nl/rest/rest.php/suggestions/';
    const LOGGING = KLogger::DEBUG;                                     // For Logging. HAS to be KLogger::OFF on development
}
?>
