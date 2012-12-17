<?php

function metadata($array_name, $item, $no_match_is_return_item = false)
{
  global $$array_name;
  if (array_key_exists($item, $$array_name))
    return ${$array_name}[$item];
  if ($no_match_is_return_item)
    return $item;
}

$book_id2na_url = array(
    'NL-HaNA_2.02.04_3959' => 'http://proxy.handle.net/10648/f273465b-fa89-4cc3-9852-e659811950ed',
    'NL-HaNA_2.02.04_3960' => 'http://proxy.handle.net/10648/72bb750e-03b7-41e2-a54f-1606849fb8b7',
    'NL-HaNA_2.02.04_3961' => 'http://proxy.handle.net/10648/d103001e-153d-4bce-86cc-1e2568d70a4c',
    'NL-HaNA_2.02.04_3962' => 'http://proxy.handle.net/10648/1d8d3b35-6ebf-44e9-a423-0ae35b764de3',
    'NL-HaNA_2.02.04_3963' => 'http://proxy.handle.net/10648/a3a09540-d27b-481b-9a3a-8b68a8430a89',
    'NL-HaNA_2.02.04_3964' => 'http://proxy.handle.net/10648/c1810696-a89e-4e3f-9d22-1b9e8d48272c',
    'NL-HaNA_2.02.04_3965' => 'http://proxy.handle.net/10648/5e3cf178-06a6-4d06-8715-dfd829561baa',
    'NL-HaNA_2.02.14_7813' => 'http://proxy.handle.net/10648/dc457b26-9dd5-4196-9044-2786aec4999d',
    'NL-HaNA_2.02.14_7814' => 'http://proxy.handle.net/10648/73fbfad1-93bb-4ea8-bb02-ab7f68bc7157',
    'NL-HaNA_2.02.14_7815' => 'http://proxy.handle.net/10648/e7c0ddae-91a8-4b45-964e-850a5b1942f5',
    'NL-HaNA_2.02.14_7816' => 'http://proxy.handle.net/10648/732c18ab-8892-43f6-afbb-f6885c377265',
    'NL-HaNA_2.02.14_7817' => 'http://proxy.handle.net/10648/1fc24b5a-0e87-4deb-9228-904732980ed7',
    'NL-HaNA_2.02.14_7818' => 'http://proxy.handle.net/10648/2fdadaeb-85a7-458d-9774-16b7267d249d',
    'NL-HaNA_2.02.14_7819' => 'http://proxy.handle.net/10648/9c736e07-2d86-480d-937b-53b59bdb5ddf',
    'NL-HaNA_2.02.14_7820' => 'http://proxy.handle.net/10648/b7299bbc-9d8a-4f76-b6c1-1533f315f610',
    'NL-HaNA_2.02.14_7821' => 'http://proxy.handle.net/10648/cca8013c-0cec-438d-9e32-be699a6a2bf5',
    'NL-HaNA_2.02.14_7822' => 'http://proxy.handle.net/10648/36a2e729-fe04-47b6-bc52-ea265bb84904',
    'NL-HaNA_2.02.14_7823' => 'http://proxy.handle.net/10648/39f666aa-9236-438a-9634-1f7267a07dfd',
    'NL-HaNA_2.02.14_7824' => 'http://proxy.handle.net/10648/4fd3ff62-7c81-4149-b264-0ee6c6217f32',
    'NL-HaNA_2.02.14_7825' => 'http://proxy.handle.net/10648/8906269c-64e5-445e-bd6b-8ce2b922e35d',
    'NL-HaNA_2.02.14_7826' => 'http://proxy.handle.net/10648/00fd61f9-6975-4fa8-9aef-50e5c75ea8f6',
    'NL-HaNA_2.02.14_7827' => 'http://proxy.handle.net/10648/3c83a8d5-8a2d-401a-9252-0f4162ae72ff',
    'NL-HaNA_2.02.14_7828' => 'http://proxy.handle.net/10648/851a44ec-1c14-4e43-a313-e0897165d971',
    'NL-HaNA_2.02.14_7829' => 'http://proxy.handle.net/10648/c9f9680f-23ec-4581-ad59-e4ca8ac3ed2d',
    'NL-HaNA_2.02.14_7830' => 'http://proxy.handle.net/10648/2e0ce32f-0288-4e47-bd10-57392639273e',
    'NL-HaNA_2.02.14_7831' => 'http://proxy.handle.net/10648/e542b25d-f609-4fd1-ac20-df616fce0f45',
    'NL-HaNA_2.02.14_7832' => 'http://proxy.handle.net/10648/5af69c4b-e8b4-4355-9f6d-465b32109bbb',
    'NL-HaNA_2.02.14_7833' => 'http://proxy.handle.net/10648/d18c4638-d57c-4306-8f81-1f275c016ffe',
    'NL-HaNA_2.02.14_7834' => 'http://proxy.handle.net/10648/9cd100d7-d6bf-4b15-a4e8-b0578a5619ca',
    'NL-HaNA_2.02.14_7835' => 'http://proxy.handle.net/10648/643d93e0-85bb-41b4-ae46-3ec1c43aa2b5',
    'NL-HaNA_2.02.14_7836' => 'http://proxy.handle.net/10648/390ee1f3-d8ed-4f60-acae-2e721d517f4f',
    'NL-HaNA_2.02.14_7837' => 'http://proxy.handle.net/10648/e5ecc0cf-114f-4ed1-a41d-d47398ff29ca',
    'NL-HaNA_2.02.14_7838' => 'http://proxy.handle.net/10648/70900f51-6492-4fee-a0c3-500397d99ac2',
    'NL-HaNA_2.02.14_7839' => 'http://proxy.handle.net/10648/7a863fcd-f089-4f10-a50c-47589b94d2e0',
    'NL-HaNA_2.02.14_7840' => 'http://proxy.handle.net/10648/b58779d5-ff63-41d2-9bc2-22eda5a7151a',
    'NL-HaNA_2.02.14_7841' => 'http://proxy.handle.net/10648/e58c85f8-87d2-4bb5-88c5-2df68450b9f9',
    'NL-HaNA_2.02.14_7842' => 'http://proxy.handle.net/10648/c8ed377f-09f3-4f84-ae7e-e204d27a6bdd',
    'NL-HaNA_2.02.14_7843' => 'http://proxy.handle.net/10648/1d9bdb32-c22c-4f0c-b56c-b5753396bc00',
    'NL-HaNA_2.02.14_7844' => 'http://proxy.handle.net/10648/26969c47-3d89-4731-aec3-714318ee9ec2',
);

$institution_id2hn = array(
    'NA' => 'Nationaal Archief',
    'UB_RuG' => 'Bibliotheek Rijksuniversiteit Groningen',
    'QI_RuG' => 'Bibliotheek Rijksuniversiteit Groningen',
    'Belgie' => 'Stadsarchief Leuven',
    'Stadsarchief Leuven' => 'Stadsarchief Leuven',
    'Gelders Archief' => 'Gelders Archief',
    'Fam. Astro' => 'Familie Astro',
    'Groninger Archieven' => 'Groninger Archieven'
);

$collection_id2hn = array(
    'KdK' => 'Kabinet der Koningin',
    'Adm' => 'Admiraliteiten',
    'UE_RFH' => 'Ubbo Emmius',
    'QI_Brill' => 'Qumran scrolls',
    'Middelduits' => 'Handschriften',
    'Schepenbank' => 'Schepenbank',
    'GLA-Rekeningen' => 'Graven en hertogen van Gelre',
    'Astro-brieven' => 'Privé-archief',
    'GA-GV' => 'Gedeputeerde Staten van Groningen'
);

$book_id2hn = array(
    'NL-HaNA_2.02.04_3960' => 'Index op Koninklijke besluiten - 1893, blz. 1-1316',
    'NL-HaNA_2.02.04_3965' => 'Index op Koninklijke besluiten - 1897, blz. 781-1626',
    'NL-HaNA_2.02.14_7813' => 'Index op Koninklijke besluiten - 1898, blz. 1-842',
    'NL-HaNA_2.02.14_7815' => 'Index op Koninklijke besluiten - 1899, blz. 1-922',
    'NL-HaNA_2.02.14_7816' => 'Index op Koninklijke besluiten - 1899, blz. 862-1782',
    'NL-HaNA_2.02.14_7819' => 'Index op Koninklijke besluiten - 1901, blz. 1-1040',
    'NL-HaNA_2.02.14_7820' => 'Index op Koninklijke besluiten - 1901, blz. 1041-2024',
    'NL-HaNA_2.02.14_7822' => 'Index op Koninklijke besluiten - 1902, blz. 974-2065',
    'NL_HaNa_H2_7823' => 'Index op Koninklijke besluiten - 1903, blz. 1-1094',
    'H24001_7824' => 'Index op Koninklijke besluiten - 1903, blz. 1299-1401',
    'NL-HaNA_2.02.14_7825' => 'Index op Koninklijke besluiten - 1904, blz. 1-998',
    'NL-HaNA_2.02.14_7826' => 'Index op Koninklijke besluiten - 1904, blz. 999-2098',
    'nl-hana_h26506_0556' => 'Index op Koninklijke besluiten - 1809, scannr. 130-739',
    'nl-hana_h26506_0557' => 'Index op Koninklijke besluiten - 1810, scannr. 199-822',
    'NL_HaNa_H2_7823' => 'Index op Koninklijke besluiten - 1903, blz. 1-1094',
    'Adm_177_1177' => 'Scheepsjournalen schepen Rotterdam en Schiedam - 1777-1779',
    'Adm_177_1189' => 'Scheepsjournalen schip D\'Arend - 1779-1783',
    'Adm_177_1177b' => 'Scheepsjournalen schepen Rotterdam en Schiedam - 1777-1779 (test)',
    'ubrug-Ubbo_Emmius_RFH' => 'Rerum Frisicarum historiae libri - 1616',
    'QIrug-Qumran_extr09' => 'Qumran scrolls',
    'medieval-text-Leuven' => 'Middelnederlandse tekst',
    'SAL7316' => 'Schepenbankregister - 1421-1422',
    'GeldArch-rekeningen-1425' => 'Rentmeestersrekening - 1424-1425',
    'PV-Astro-1932' => 'Brieven - 1932',
    'GV-Appingedam_8149' => 'Gemeenteverslag Appingedam - 1855',
);
?>
