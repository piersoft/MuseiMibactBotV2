<?php
include('settings_t.php');

/*
$lat=40.6701;
$lon=16.5999;

	//$lon=$row[0]['lng'];
	//$lat=$row[0]['lat'];
	$alert="";
	$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
	$json_string = file_get_contents($reply);
	$parsed_json = json_decode($json_string);
	//var_dump($parsed_json);
	$comune="";
	$temp_c1 =$parsed_json->{'display_name'};

	if ($parsed_json->{'address'}->{'town'}) {
		$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'city'};

	}
		$comune .=$parsed_json->{'address'}->{'town'};
	//	if ($parsed_json->{'address'}->{'town'}) {

		echo "comune: ".$comune;

		$html = file_get_contents('http://www.dataset.puglia.it/api/action/datastore_search?resource_id=97d48e1a-58f6-47de-92f8-32bfb016f7f7&q=Mostra');

		$parsed_json = json_decode($html);
		$count = 0;
		foreach($parsed_json as $data=>$csv1){
			 $count = $count+1;
		}
echo $count;
var_dump($parsed_json);
*/

$text="Lecce";
$html = file_get_contents('http://dbunico20.beniculturali.it/DBUnicoManagerWeb/dbunicomanager/searchPlace?tipologiaLuogo=1&quantita=50&comune='.$text);

$html=str_replace("<![CDATA[","",$html);
$html=str_replace("]]>","",$html);
$html=str_replace("</br>","",$html);
$html=str_replace("\n","",$html);
$html=str_replace("&nbsp;","",$html);
$html=str_replace(";"," ",$html);
$html=str_replace(","," ",$html);
if (strpos($html,'<mibac>') == false) {
echo "no result";
}

$doc = new DOMDocument;
$doc->loadHTML($html);

$xpa    = new DOMXPath($doc);
//var_dump($doc);
$divsl   = $xpa->query('//codice[@sorgente="DBUnico 2.0"]');
$divs0   = $xpa->query('//mibac');
$divs   = $xpa->query('//mibac//luogodellacultura/proprieta');
$divs1   = $xpa->query('//mibac//luogodellacultura/denominazione/nomestandard');
$divs2   = $xpa->query('//mibac//luogodellacultura/identificatore/codice[@sorgente="DBUnico 2.0"]');
$dival=[];
$diva=[];
$diva1=[];
$diva2=[];
$count=0;
foreach($divs0 as $div0) {
$count++;
}
echo "Count: ".$count."\n";
foreach($divsl as $divl) {

		array_push($dival,$divl->nodeValue);
}

foreach($divs as $div) {
		array_push($diva,$div->nodeValue);

}

foreach($divs1 as $div1) {

			array_push($diva1,$div1->nodeValue);
}
foreach($divs2 as $div2) {

			array_push($diva2,$div2->nodeValue);
}
//var_dump($divs2);
//$count=3;
if ($count > 50){
echo "troppe richieste";
}
$option=[];
for ($i=0;$i<$count;$i++){
$alert.="\n\n</br>";
$alert.= $diva1[$i]."\n";
$alert.= $diva2[$i]."\n";
$diva1[$i]=str_replace(" ","_",$diva1[$i]);
$diva1[$i]=str_replace("'","__",$diva1[$i]);
$diva1[$i]=str_replace("-","___",$diva1[$i]);
$diva1[$i]=str_replace("(","$",$diva1[$i]);
$diva1[$i]=str_replace(")","$$",$diva1[$i]);
$diva1[$i]=str_replace("\"","22",$diva1[$i]);
$diva1[$i]=str_replace("“","E2809C",	$diva1[$i]);
$option[$i]=$diva1[$i];
//	$alert.= "Clicca per dettagli: /".$diva1[$i]."\n";
$alert.="</br>__________________";
}

echo $alert;
?>
