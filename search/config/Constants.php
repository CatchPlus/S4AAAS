<?php
abstract class Constants
{
    const DEFAULT_ROWS = 10;                                            // Default number of rows returned in the result
    const DEFAULT_OFFSET = 0;                                           // Default offset for the result
    
    const MAX_PAGINATION = 20;                                          // Max number of pages shown before seeing a [...]
    const MAX_END_PAGINATION = 4;                                       // unused
    const MARGE_PAGINATION = 10;                                        // When to increment the last page
    const TIMEOUT = 10;                                                 // Default timeout for Monk retrieval
    const NO_IMAGE = "http://localhost/monk2/images/no-image.jpg";      // location of the image shown when no image is found
    const USERAGENT = 'Monk Agent 0.9';                                 // User agent used to try to conquer failed HTTP requests

    const WIKI_URL = 'http://userwiki.target.rug.nl/monk/doku.php?id=userinterface_documentatie';
    
    static $pages = array(                                              // Selection of number of pages per page
        5 => 5,
        10 => 10,
        15 => 15,
        20 => 20,
        25 => 25,
    );
    
    static $fields = array(
        'institutions'  => 'institutions',           // allowed fields
        'collections'   => 'collections',
        'books'         => 'books',
        'annotations'   => 'annotations',
        'wordzonetypes' => 'wordzonetypes',
    );
    
    static $matches = array(                                            // allowed matches
        'prefix'  => 'prefix',
        'suffix'   => 'suffix',
        'exact'    => 'exact',
        'wildcard' => 'wildcard',
    );
    
    static $annotations = array(                                        // allowed annotations
        'wordzone' => 'Woordlabel',
        'line'     => 'Transcriptie',
        'page'     => 'Paginatitel',
    );
            
//    static $wordZoneTypes = array(                                      // allowed wordzonetypes
//        'HUMAN'  => 'HUMAN',
//        'JAVA'   => 'JAVA',
//        'RECOG'  => 'RECOG',
//        'RECOGe' => 'RECOGe',
//        'MINED'  => 'MINED',
//    );

    static $wordZoneTypes = array(                                      // allowed wordzonetypes
        'HUMAN|JAVA'  => 'Mens',
        'RECOG|RECOGe|MINED'  => 'Machine',
    );

    static $toolTipAnchors = array(
        'bookCollection' => 'boek_collectie',
        'wordzone' => 'woordlabel',
        'annotations' => 'annotaties',
        'match' => 'gelijkenis'
    );

    // TODO: Get this from Monk instead of configuration as a constants
    static $bookIDLookup = array(                                       // conversion of bookid to navisid
      "navis-NL-HaNA_2.02.04_3960" => "navis-NL-HaNA_2.02.04_3960_%04d-line-%03d",
      "navis-NL-HaNA_2.02.04_3965" => "navis-NL-HaNA_2.02.04_3965_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7813" => "navis-NL-HaNA_2.02.14_7813_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7815" => "navis-NL-HaNA_2.02.14_7815_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7816" => "navis-NL-HaNA_2.02.14_7816_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7819" => "navis-NL-HaNA_2.02.14_7819_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7820" => "navis-NL-HaNA_2.02.14_7820_%04d-line-%03d",
      "navis-H2_7823_0001-1094" => "navis-NL_HaNa_H2_7823_%04d-line-%03d",
      "navis" => "navis-H24001_7824_%04d-line-%03d",
      "navis2" => "navis-H24001_7824_%04d-line-%03d",
      "navis-nl-hana_h26506_0550_0001-0739" => "navis-nl-hana_h26506_0556_%04d-line-%03d",
      "navis-nl-hana_h26506_0557_0001-0822" => "navis-nl-hana_h26506_0557_%04d-line-%03d",
      "navis-H2_7823_0001-1094b" => "navis-NL_HaNa_H2_7823_%04d-line-%03d",
      "cliwoc-Adm_177_1177" => "cliwoc-Adm_177_1177_%04d-line-%03d",
      "cliwoc-Adm_177_1189" => "cliwoc-Adm_177_1189_%04d-line-%03d",
      "ScanCompA-NL-HaNA_7822_0001-0062" => "navis-NL-HaNA_7822_%04d-line-%03d",
      "ScanCompB-NL-HaNA_7822_0001-0062" => "navis-NL-HaNA_2.02.14_7824_%04d-line-%03d",
      "ScanCompC-NL-HaNA_7822_0001-0062" => "navis-NL-HaNA_2.02.14_7830_%04d-line-%03d",
      "ScanCompD-NL-HaNA_7822_0001-0062" => "navis-NL-HaNA_2-02-14_7828_%04d-line-%03d",
      "ScanCompE-NL-HaNA_7822_0001-0062" => "navis-NL-HaNA_2.02.14_7826_%04d-line-%03d",
      "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72" => "navis-ubrug-Ubbo_Emmius_RFH_%04d-line-%03d",
      "QIrug-Qumran_extr09" => "navis-QIrug-Qumran_extr09_%04d-line-%03d",
      "navis-medieval-text-Leuven" => "navis-medieval-text-Leuven_%04d-line-%03d",
      "GeldArch-rekeningen-1425" => "navis-GeldArch-rekeningen-1425_%04d-line-%03d",
      "SAL7316" => "navis-SAL7316_%04d-line-%03d"
    );
    
}
?>
