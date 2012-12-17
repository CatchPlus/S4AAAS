<?php
/* Program to generate a NA/KdK file path on the basis 
   of a line ID
   
   Jean-Paul van Oosten jan 2011
   Generated at Fri Sep  2 11:48:57 2011

*/

define('GLOBAL_ROOT', "/srv/www/htdocs/monk/");
define('WORDZONE_IDENTIFIER', "-zone-");
define('LINE_IDENTIFIER'    , "-line-");

function parse_line_id($line_id)
{
   $kboek = 0;
   $kpage = 0;
   $kline = 0;
   if (sscanf($line_id, "navis-NL-HaNA_2.02.04_3960_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 1;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.04_3965_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 2;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7813_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 3;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7815_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 4;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7816_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 5;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7819_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 6;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7820_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 7;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7822_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 8;
   } else if (sscanf($line_id, "navis-NL_HaNa_H2_7823_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 9;
   } else if (sscanf($line_id, "navis-H24001_7824_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 10;
   } else if (sscanf($line_id, "navis-H24001_7824_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 11;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7825_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 12;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7826_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 13;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7827_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 14;
   } else if (sscanf($line_id, "navis-NL-HaNA_2.02.14_7828_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 15;
   } else if (sscanf($line_id, "navis-nl-hana_h26506_0556_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 16;
   } else if (sscanf($line_id, "navis-nl-hana_h26506_0557_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 17;
   } else if (sscanf($line_id, "navis-NL_HaNa_H2_7823_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 18;
   } else if (sscanf($line_id, "cliwoc-Adm_177_1177_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 19;
   } else if (sscanf($line_id, "cliwoc-Adm_177_1189_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 20;
   } else if (sscanf($line_id, "navis-ubrug-Ubbo_Emmius_RFH_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 21;
   } else if (sscanf($line_id, "navis-QIrug-Qumran_extr09_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 22;
   } else if (sscanf($line_id, "navis-medieval-text-Leuven_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 23;
   } else if (sscanf($line_id, "navis-GeldArch-rekeningen-1425_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 24;
   } else if (sscanf($line_id, "navis-SAL7316_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 25;
   } else if (sscanf($line_id, "cliwoc-Adm_177_1177b_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 26;
   } else if (sscanf($line_id, "navis-PV-Astro-1932_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 27;
   } else if (sscanf($line_id, "navis-GV-Appingedam_8149_%04d-line-%03d", $kpage, $kline) > 0) {
      $kboek = 28;
   }
   return array($kboek, $kpage, $kline);
}

function page_id($line_id)
{
   $out = "";
   list($kboek,$kpage,$kline) = parse_line_id($line_id);
   
   if ($kboek == 1)
      return sprintf("NL-HaNA_2.02.04_3960_%04d", $kpage);
   if ($kboek == 2)
      return sprintf("NL-HaNA_2.02.04_3965_%04d", $kpage);
   if ($kboek == 3)
      return sprintf("NL-HaNA_2.02.14_7813_%04d", $kpage);
   if ($kboek == 4)
      return sprintf("NL-HaNA_2.02.14_7815_%04d", $kpage);
   if ($kboek == 5)
      return sprintf("NL-HaNA_2.02.14_7816_%04d", $kpage);
   if ($kboek == 6)
      return sprintf("NL-HaNA_2.02.14_7819_%04d", $kpage);
   if ($kboek == 7)
      return sprintf("NL-HaNA_2.02.14_7820_%04d", $kpage);
   if ($kboek == 8)
      return sprintf("NL-HaNA_2.02.14_7822_%04d", $kpage);
   if ($kboek == 9)
      return sprintf("NL_HaNa_H2_7823_%04d", $kpage);
   if ($kboek == 10)
      return sprintf("H24001_7824_%04d", $kpage);
   if ($kboek == 11)
      return sprintf("H24001_7824_%04d", $kpage);
   if ($kboek == 12)
      return sprintf("NL-HaNA_2.02.14_7825_%04d", $kpage);
   if ($kboek == 13)
      return sprintf("NL-HaNA_2.02.14_7826_%04d", $kpage);
   if ($kboek == 14)
      return sprintf("NL-HaNA_2.02.14_7827_%04d", $kpage);
   if ($kboek == 15)
      return sprintf("NL-HaNA_2.02.14_7828_%04d", $kpage);
   if ($kboek == 16)
      return sprintf("nl-hana_h26506_0556_%04d", $kpage);
   if ($kboek == 17)
      return sprintf("nl-hana_h26506_0557_%04d", $kpage);
   if ($kboek == 18)
      return sprintf("NL_HaNa_H2_7823_%04d", $kpage);
   if ($kboek == 19)
      return sprintf("Adm_177_1177_%04d", $kpage);
   if ($kboek == 20)
      return sprintf("Adm_177_1189_%04d", $kpage);
   if ($kboek == 21)
      return sprintf("ubrug-Ubbo_Emmius_RFH_%04d", $kpage);
   if ($kboek == 22)
      return sprintf("QIrug-Qumran_extr09_%04d", $kpage);
   if ($kboek == 23)
      return sprintf("medieval-text-Leuven_%04d", $kpage);
   if ($kboek == 24)
      return sprintf("GeldArch-rekeningen-1425_%04d", $kpage);
   if ($kboek == 25)
      return sprintf("SAL7316_%04d", $kpage);
   if ($kboek == 26)
      return sprintf("Adm_177_1177b_%04d", $kpage);
   if ($kboek == 27)
      return sprintf("PV-Astro-1932_%04d", $kpage);
   if ($kboek == 28)
      return sprintf("GV-Appingedam_8149_%04d", $kpage);
   return "?Unknown?";
}

function db_dir_id($line_id)
{
   list($kboek,$kpage,$kline) = parse_line_id($line_id);

   if ($kboek == 1)
      return "navis-NL-HaNA_2.02.04_3960";
   if ($kboek == 2)
      return "navis-NL-HaNA_2.02.04_3965";
   if ($kboek == 3)
      return "navis-NL-HaNA_2.02.14_7813";
   if ($kboek == 4)
      return "navis-NL-HaNA_2.02.14_7815";
   if ($kboek == 5)
      return "navis-NL-HaNA_2.02.14_7816";
   if ($kboek == 6)
      return "navis-NL-HaNA_2.02.14_7819";
   if ($kboek == 7)
      return "navis-NL-HaNA_2.02.14_7820";
   if ($kboek == 8)
      return "navis-NL-HaNA_2.02.14_7822";
   if ($kboek == 9)
      return "navis-H2_7823_0001-1094";
   if ($kboek == 10)
      return "navis";
   if ($kboek == 11)
      return "navis2";
   if ($kboek == 12)
      return "navis-NL-HaNA_2.02.14_7825";
   if ($kboek == 13)
      return "navis-NL-HaNA_2.02.14_7826";
   if ($kboek == 14)
      return "navis-NL-HaNA_2.02.14_7827";
   if ($kboek == 15)
      return "navis-NL-HaNA_2.02.14_7828";
   if ($kboek == 16)
      return "navis-nl-hana_h26506_0550_0001-0739";
   if ($kboek == 17)
      return "navis-nl-hana_h26506_0557_0001-0822";
   if ($kboek == 18)
      return "navis-H2_7823_0001-1094b";
   if ($kboek == 19)
      return "cliwoc-Adm_177_1177";
   if ($kboek == 20)
      return "cliwoc-Adm_177_1189";
   if ($kboek == 21)
      return "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72";
   if ($kboek == 22)
      return "QIrug-Qumran_extr09";
   if ($kboek == 23)
      return "navis-medieval-text-Leuven";
   if ($kboek == 24)
      return "GeldArch-rekeningen-1425";
   if ($kboek == 25)
      return "SAL7316";
   if ($kboek == 26)
      return "cliwoc-Adm_177_1177b";
   if ($kboek == 27)
      return "PV-Astro-1932";
   if ($kboek == 28)
      return "GV-Appingedam_8149";
   return 0;
}

function bookid($line_id)
{
   $bk = "";
   if(strncmp($line_id,"navis-",6) == 0) {
      $bk = db_dir_id($line_id);
   } else if(strncmp($line_id,"cliwoc-",7) == 0) {
      $bk = db_dir_id($line_id);
   } else {
      $bk = $line_id;

      $n = strlen($bk);
      $i = $n-1;
      while($i > 0 && $i >= $n-5) {
         if($bk[$i] == '_') {
            break;
         }
         $i--;
      }
      $bk = substr($bk, 0, $i);
   }
   if(strncmp($bk,"navis-",6) == 0)
      return substr($bk, 6);
   return $bk;
}

function bookroot($id)
{
	$bk = "";
	if (strncmp($id, "navis-", 6) == 0) {
		$bk = db_dir_id($id);
	} else if (strncmp($id, "cliwoc-", 7) == 0) {
		$bk = db_dir_id($id);
	} else {
		$bk = $id;
		$n = strlen($bk);
		$i = $n - 1;
		while ($i > 0 && $i >= n - 5) {
			if ($bk[i] == "_") {
				break;
			}
			$i--;
		}
		$bk = substr($bk, 0, $i);
	}
	return $bk;
}

function path_of_page($type, $raw_page_id)
{
   
   if(strcmp($type,"book") == 0) {
      /* type: book for bookid with navis- prefix stripped */
      return sprintf("%s",bookid($raw_page_id));
   } else if(strcmp($type,"bookroot") == 0) {
      /* type: book for bookroot (real root)  */
      return sprintf("%s",bookroot($raw_page_id));
   } else {
      /* If not a book: type txt, pgm-grey etc */
      
      $dummy_line_id = sprintf("%s-line-000", $raw_page_id);
      $db_dir = db_dir_id($dummy_line_id);
      if (!$db_dir) /* also try with navis- prefix */
      {
          $dummy_line_id = sprintf("navis-%s-line-000", $raw_page_id);
          $db_dir = db_dir_id($dummy_line_id);
      }
      $fullpath = sprintf("%s/%s/Pages/%s"
                   ,GLOBAL_ROOT,$db_dir
                   ,$raw_page_id); 
      if(strcmp($type,"blind") != 0) {
         if(!file_exists($fullpath)) {
            return 0;
         } else {
            return $fullpath;
         }
      } else {
         return $fullpath; /* 'blind' a pseudo filetype, comparable to @blind mode 
                      return .true. regardless of the existence
                      of the file or directory */
      }
   }
}

function path_of_line($type, $raw_line_id, $mode="")
{
   
   if(strncmp($raw_line_id,"navis-",6) == 0
   || strncmp($raw_line_id,"cliwoc-",7) == 0) {
      $line_id = $raw_line_id; /* normal ID label */
   } else {
      
      /* probably file path with ID label somewhere at the end */
      
      $eop = strpos($raw_line_id,"navis-");
      if($eop === FALSE) {
         $eop = strpos($raw_line_id,"cliwoc-");
         if($eop === FALSE) {
            printf("navis-id2path() FATAL, not an ID: [%s]\n", $raw_line_id);
            exit(1);
         }
      }
      $line_id = substr($raw_line_id, $eop); /* take the first one */
      
      $eop = strchr($line_id,'.');
      if($eop !== FALSE) $line_id = substr($line_id, 0, $eop); /* remove possible file .typ */
      
      /* line_id is now cleaned */
   }

   if(strcmp($type,"txt") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/Txt/%s.txt"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
                   
   } else if(strcmp($type,"tags") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/Txt/%s.tags"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
                   
   } else if(strcmp($type,"log") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/Txt/%s.log"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
                   
   } else if(strcmp($type,"web-grey") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/web-grey/%s.jpg"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
                   
   } else if(strcmp($type,"web") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/web-grey/%s.jpg"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);

   } else if(strcmp($type,"pgm-grey") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/pgm-grey/%s.pgm"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
   } else if(strcmp($type,"dbg-color") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/dbg-color/%s.jpg"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
   } else if(strcmp($type,"xml") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/%s/Zones/zones.xml"
                   ,GLOBAL_ROOT,db_dir_id($line_id)
                   ,page_id($line_id),$line_id);
   } else if(strcmp($type,"book") == 0) {                   
      $fullpath = sprintf("%s",bookid($line_id));

   } else {
      printf("Type [%s] not known. Give web,web-grey,pgm-grey,dbg-color,txt,tags,log\n", type);
      exit(1);
   }
   if(strcmp($mode,"@blind") != 0 && strcmp($type,"book") != 0) {
      if(!file_exists($fullpath)) {
         return(0);
      } else {
         return($fullpath);
      }
   } else {
      /* @blind: do not really check on disk if image exists */
      return($fullpath);
   }
}

function line_id($an_id)
{
   $out = $an_id;
   $eop = strpos($out,"-zone");
   if($eop !== FALSE) $out = substr($out, 0, $eop);
   
   return($out);
}

function path_of_wordzone($type, $raw_wordzone_id, $mode="")
{
   if(strncmp($raw_wordzone_id,"navis-",6) == 0
   || strncmp($raw_wordzone_id,"cliwoc-",7) == 0) {
      $wordzone_id = $raw_wordzone_id; /* normal ID label */
   } else {
      
      /* probably file path with ID label somewhere at the end */
      
      $basnam = $raw_wordzone_id;
      $wordzone_id = basename($basnam);
      
      $eop = strpos($wordzone_id,"navis-");
      if($eop === FALSE) {
         $eop = strpos($wordzone_id,"cliwoc-");
         if($eop === FALSE) {
            printf("navis-id2path() FATAL, fpath does not contain a navisID at the end: [%s]\n", $raw_wordzone_id);
            exit(1);
         }
      }
      
      $eop = strpos($wordzone_id,'.');
      if($eop !== FALSE) $wordzone_id = substr($wordzone_id, 0, $eop); /* remove possible file .typ */
      
      /* wordzone_id is now cleaned */
   }

   if(strcmp($type,"txt") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/%s/Zones/%s.txt"
                   ,GLOBAL_ROOT,db_dir_id($wordzone_id)
                   ,page_id($wordzone_id)
                   ,line_id($wordzone_id)
                   ,$wordzone_id);
                   
   } else if(strcmp($type,"web-grey") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/%s/Zones/%s.jpg"
                   ,GLOBAL_ROOT,db_dir_id($wordzone_id)
                   ,page_id($wordzone_id)
                   ,line_id($wordzone_id)
                   ,$wordzone_id);
                   
   } else if(strcmp($type,"pgm-grey") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/%s/Zones/%s.pgm"
                   ,GLOBAL_ROOT,db_dir_id($wordzone_id)
                   ,page_id($wordzone_id)
                   ,line_id($wordzone_id)
                   ,$wordzone_id);
   } else if(strcmp($type,"xml") == 0) {
      $fullpath = sprintf("%s/%s/Pages/%s/Lines/%s/Zones/zones.xml"
                   ,GLOBAL_ROOT,db_dir_id($wordzone_id)
                   ,page_id($wordzone_id),line_id($wordzone_id));

   } else if(strcmp($type,"book") == 0) {                   
      $fullpath = sprintf("%s",bookid($wordzone_id));

   } else if(strcmp($type,"bookroot") == 0) {                   
      $fullpath = sprintf("%s",bookroot($wordzone_id));

   } else {
      printf("Type [%s] not implemented for wordzones. Give web-grey,pgm-grey,txt\n", type);
      exit(1);
   }
   
   if(strcmp($mode,"@blind") != 0 && strcmp($type,"book") != 0) {
      if(!file_exists($fullpath)) {
         return(0);
      } else {
         return($fullpath);
      }
   } else {
      /* @blind: do not really check on disk if image exists */
      return($fullpath);
   }
}

function path_of($type, $raw_line_id, $mode="")
{
   if(strstr($raw_line_id,WORDZONE_IDENTIFIER) !== FALSE) {
      $fullpath = path_of_wordzone($type,$raw_line_id,$mode);
   } else if(strstr($raw_line_id,LINE_IDENTIFIER) !== FALSE) {
      $fullpath = path_of_line($type,$raw_line_id,$mode);
   } else {
      $fullpath = path_of_page($type,$raw_line_id,$mode);
   }
   return($fullpath);
}

// produces shortname of the book
function shortname($line_id)
{
   list($kboek,$kpage,$kline) = parse_line_id($line_id);

   if ($kboek == 1)
      return "KdK 1893 blz. 1-1316";
   if ($kboek == 2)
      return "KdK 1897 blz. 781-1626";
   if ($kboek == 3)
      return "KdK 1898 blz. 1-842";
   if ($kboek == 4)
      return "KdK 1899 blz. 1-922";
   if ($kboek == 5)
      return "KdK 1899 blz. 862-1782";
   if ($kboek == 6)
      return "KdK 1901 blz. 1-1040";
   if ($kboek == 7)
      return "KdK 1901 blz. 1041-2024";
   if ($kboek == 8)
      return "KdK 1902 blz. 974-2065";
   if ($kboek == 9)
      return "KdK 1903 blz. 1-1094";
   if ($kboek == 10)
      return "KdK 1903 blz. 1299-1346";
   if ($kboek == 11)
      return "KdK 1903 blz. 1348-1401";
   if ($kboek == 12)
      return "KdK 1904 blz. 1-998";
   if ($kboek == 13)
      return "KdK 1904 blz. 999-2098";
   if ($kboek == 14)
      return "KdK 1905 blz. 1-1120";
   if ($kboek == 15)
      return "KdK 1905 blz. 1121-2247";
   if ($kboek == 16)
      return "KdK 1809, scannr. 130-739";
   if ($kboek == 17)
      return "KdK 1810, scannr. 199-822";
   if ($kboek == 18)
      return "KdK 1903, reg.+blz. 1-1094 XML-layout";
   if ($kboek == 19)
      return "Adm 1779 - 177 1177";
   if ($kboek == 20)
      return "Adm 1779 - 177 1189";
   if ($kboek == 21)
      return "Ubbo Emmius RFH";
   if ($kboek == 22)
      return "Qumran scrolls";
   if ($kboek == 23)
      return "Middelduits";
   if ($kboek == 24)
      return "Gelderse rekening 1425";
   if ($kboek == 25)
      return "Schepenbank Leuven 1421";
   if ($kboek == 26)
      return "Adm 1779 - (test)";
   if ($kboek == 27)
      return "P.V.Astro - 1932";
   if ($kboek == 28)
      return "GV Appingedam - 1855";
}

// produces a list of books
function bookdirlist()
{
   return array(
      "navis-NL-HaNA_2.02.04_3960",
      "navis-NL-HaNA_2.02.04_3965",
      "navis-NL-HaNA_2.02.14_7813",
      "navis-NL-HaNA_2.02.14_7815",
      "navis-NL-HaNA_2.02.14_7816",
      "navis-NL-HaNA_2.02.14_7819",
      "navis-NL-HaNA_2.02.14_7820",
      "navis-NL-HaNA_2.02.14_7822",
      "navis-H2_7823_0001-1094",
      "navis",
      "navis2",
      "navis-NL-HaNA_2.02.14_7825",
      "navis-NL-HaNA_2.02.14_7826",
      "navis-NL-HaNA_2.02.14_7827",
      "navis-NL-HaNA_2.02.14_7828",
      "navis-nl-hana_h26506_0550_0001-0739",
      "navis-nl-hana_h26506_0557_0001-0822",
      "navis-H2_7823_0001-1094b",
      "cliwoc-Adm_177_1177",
      "cliwoc-Adm_177_1189",
      "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72",
      "QIrug-Qumran_extr09",
      "navis-medieval-text-Leuven",
      "GeldArch-rekeningen-1425",
      "SAL7316",
      "cliwoc-Adm_177_1177b",
      "PV-Astro-1932",
      "GV-Appingedam_8149",
   );
}

function bookdir2linepattern($bookdir)
{
   $bd2lp = array(
      "navis-NL-HaNA_2.02.04_3960" => "navis-NL-HaNA_2.02.04_3960_%04d-line-%03d",
      "navis-NL-HaNA_2.02.04_3965" => "navis-NL-HaNA_2.02.04_3965_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7813" => "navis-NL-HaNA_2.02.14_7813_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7815" => "navis-NL-HaNA_2.02.14_7815_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7816" => "navis-NL-HaNA_2.02.14_7816_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7819" => "navis-NL-HaNA_2.02.14_7819_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7820" => "navis-NL-HaNA_2.02.14_7820_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7822" => "navis-NL-HaNA_2.02.14_7822_%04d-line-%03d",
      "navis-H2_7823_0001-1094" => "navis-NL_HaNa_H2_7823_%04d-line-%03d",
      "navis" => "navis-H24001_7824_%04d-line-%03d",
      "navis2" => "navis-H24001_7824_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7825" => "navis-NL-HaNA_2.02.14_7825_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7826" => "navis-NL-HaNA_2.02.14_7826_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7827" => "navis-NL-HaNA_2.02.14_7827_%04d-line-%03d",
      "navis-NL-HaNA_2.02.14_7828" => "navis-NL-HaNA_2.02.14_7828_%04d-line-%03d",
      "navis-nl-hana_h26506_0550_0001-0739" => "navis-nl-hana_h26506_0556_%04d-line-%03d",
      "navis-nl-hana_h26506_0557_0001-0822" => "navis-nl-hana_h26506_0557_%04d-line-%03d",
      "navis-H2_7823_0001-1094b" => "navis-NL_HaNa_H2_7823_%04d-line-%03d",
      "cliwoc-Adm_177_1177" => "cliwoc-Adm_177_1177_%04d-line-%03d",
      "cliwoc-Adm_177_1189" => "cliwoc-Adm_177_1189_%04d-line-%03d",
      "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72" => "navis-ubrug-Ubbo_Emmius_RFH_%04d-line-%03d",
      "QIrug-Qumran_extr09" => "navis-QIrug-Qumran_extr09_%04d-line-%03d",
      "navis-medieval-text-Leuven" => "navis-medieval-text-Leuven_%04d-line-%03d",
      "GeldArch-rekeningen-1425" => "navis-GeldArch-rekeningen-1425_%04d-line-%03d",
      "SAL7316" => "navis-SAL7316_%04d-line-%03d",
      "cliwoc-Adm_177_1177b" => "cliwoc-Adm_177_1177b_%04d-line-%03d",
      "PV-Astro-1932" => "navis-PV-Astro-1932_%04d-line-%03d",
      "GV-Appingedam_8149" => "navis-GV-Appingedam_8149_%04d-line-%03d",
   );
   return $bd2lp[$bookdir];
}

// produces an associative array of book to institutions
function institutions()
{
    return array(
        "navis-NL-HaNA_2.02.04_3960" => "NA",
        "navis-NL-HaNA_2.02.04_3965" => "NA",
        "navis-NL-HaNA_2.02.14_7813" => "NA",
        "navis-NL-HaNA_2.02.14_7815" => "NA",
        "navis-NL-HaNA_2.02.14_7816" => "NA",
        "navis-NL-HaNA_2.02.14_7819" => "NA",
        "navis-NL-HaNA_2.02.14_7820" => "NA",
        "navis-NL-HaNA_2.02.14_7822" => "NA",
        "navis-H2_7823_0001-1094" => "NA",
        "navis" => "NA",
        "navis2" => "NA",
        "navis-NL-HaNA_2.02.14_7825" => "NA",
        "navis-NL-HaNA_2.02.14_7826" => "NA",
        "navis-NL-HaNA_2.02.14_7827" => "NA",
        "navis-NL-HaNA_2.02.14_7828" => "NA",
        "navis-nl-hana_h26506_0550_0001-0739" => "NA",
        "navis-nl-hana_h26506_0557_0001-0822" => "NA",
        "navis-H2_7823_0001-1094b" => "NA",
        "cliwoc-Adm_177_1177" => "NA",
        "cliwoc-Adm_177_1189" => "NA",
        "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72" => "UB_RuG",
        "QIrug-Qumran_extr09" => "QI_RuG",
        "navis-medieval-text-Leuven" => "Belgie",
        "GeldArch-rekeningen-1425" => "Gelders Archief",
        "SAL7316" => "Stadsarchief Leuven",
        "cliwoc-Adm_177_1177b" => "NA",
        "PV-Astro-1932" => "Fam. Astro",
        "GV-Appingedam_8149" => "Groninger Archieven",
    );
}

// produces an associative array of book to collection
function collections()
{
    return array(
        "navis-NL-HaNA_2.02.04_3960" => "KdK",
        "navis-NL-HaNA_2.02.04_3965" => "KdK",
        "navis-NL-HaNA_2.02.14_7813" => "KdK",
        "navis-NL-HaNA_2.02.14_7815" => "KdK",
        "navis-NL-HaNA_2.02.14_7816" => "KdK",
        "navis-NL-HaNA_2.02.14_7819" => "KdK",
        "navis-NL-HaNA_2.02.14_7820" => "KdK",
        "navis-NL-HaNA_2.02.14_7822" => "KdK",
        "navis-H2_7823_0001-1094" => "KdK",
        "navis" => "KdK",
        "navis2" => "KdK",
        "navis-NL-HaNA_2.02.14_7825" => "KdK",
        "navis-NL-HaNA_2.02.14_7826" => "KdK",
        "navis-NL-HaNA_2.02.14_7827" => "KdK",
        "navis-NL-HaNA_2.02.14_7828" => "KdK",
        "navis-nl-hana_h26506_0550_0001-0739" => "KdK",
        "navis-nl-hana_h26506_0557_0001-0822" => "KdK",
        "navis-H2_7823_0001-1094b" => "KdK",
        "cliwoc-Adm_177_1177" => "Adm",
        "cliwoc-Adm_177_1189" => "Adm",
        "ubrug-Ubbo_Emmius_RFH_1616_Omslag_p72" => "UE_RFH",
        "QIrug-Qumran_extr09" => "QI_Brill",
        "navis-medieval-text-Leuven" => "Middelduits",
        "GeldArch-rekeningen-1425" => "GLA-Rekeningen",
        "SAL7316" => "Schepenbank",
        "cliwoc-Adm_177_1177b" => "Adm",
        "PV-Astro-1932" => "Astro-brieven",
        "GV-Appingedam_8149" => "GA-GV",
    );
}

