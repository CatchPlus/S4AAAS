<?php
class Suggestions {
    
    private $get = array();             // the _GET data
    
    private $query = '';                // the query for retrieval
    
//    private $suggestions= array();      // holding the suggestions
    private $suggestions;      // holding the suggestions


    function __construct($getData)
    {
        $this->get = $getData;
        /* TODO: validation of query? */
        if(isset($this->get['query']))
            $this->query = $this->get['query'];
        $this->setSuggestions();

    }

    /* collect the suggestions with the file_get_contents function */
    private function setSuggestions()
    {
        try {
            $xml = SendRequest::getResult(Config::SUGGESTION_URL . $this->query);
        } catch (Exception $e) {
            throw new Exception('Can not retrieve data from Monk. Monk is taking a nap?');
        }
        $this->suggestions = "['" . str_replace('|',"','", $xml->searchresult->suggestions) . "'],";
    }

    /* collect the collection with curl. Ought to be faster, but bottleneck will be monk */
    /* update: unused, use abstract class SendRequest now */
    private function setSuggestionsWithCurl()
    {
        $ch = curl_init(Config::BASEURL . Config::SUGGESTION_SUFFIX . $this->query);
        //curl_setopt($ch, CURLOPT_URL, Config::BASEURL . Config::SUGGESTION_SUFFIX . $this->query);

        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Config::TIMEOUT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, Config::USERAGENT);                  // Have to set user agent to avoid getting a 'failed to open stream: HTTP request failed!'
        if(($result = curl_exec($ch)) !== '')
        {
            curl_close($ch);

            $xml = simplexml_load_string($result);
            

            // parse xml
            foreach($xml->children() as $child) {
                if($child->getName() == 'searchresult') {
                    $this->suggestions[] = (string) "'{$child->suggestion}'";
                }
            }
        } else {
            throw new Exception('Can not retrieve data from Monk. Monk is taking a nap?');
        }
        
    }
    
    /* convert result to json, well, simulate it */
    public function getSuggestions2()
    {
        $return = "{
\tquery:'{$this->query}',
\tsuggestions: " . '[' . implode(',', $this->suggestions) . '],' . "
\tdata: []
}";
        return $return;
    }

    public function getSuggestions()
    {
        $return = "{
\t\"query\":'{$this->query}',
\t\"suggestions\": " . $this->suggestions . "
\t\"data\": []
}";
        return $return;
    }
}