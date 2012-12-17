<?php
class Config extends Constants
{
    //const BASEURL = 'http://217.21.192.132';

    const BASEURL = 'http://s4aaas.target-imedia.nl';                   // URL of the Monk server
    const SEARCH_SUFFIX = '/rest/books/';                               // Retrieval part for Book collection
    const SUGGESTION_SUFFIX = '/rest/suggestion_unauth/';               // URI of the suggestions
    const RESULT_SUFFIX = '/rest/search/';                              // Where to get the results
    const IMG_URL = 'http://s4aaas.target-imedia.nl';                   // URL for the image retrieval
    const IMG_SUFFIX = '/monk';                                         // suffix for the image retrieval
    const SUGGESTION_URL = 'http://s4aaas.target-imedia.nl/rest/suggestion_unauth/';
    const LOGGING = KLogger::OFF;                                       // For Logging. HAS to be KLogger::OFF on development
    
}
?>
