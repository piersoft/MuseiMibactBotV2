	<?php
/**
* Telegram Bot example for Italian Museums of DBUnico Mibact Lic. CC-BY
* @author Francesco Piero Paolicelli @piersoft
*/
//include("settings_t.php");
include("Telegram.php");

class mainloop{
const MAX_LENGTH = 4096;
function start($telegram,$update)
{

	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");
	//$data=new getdata();
	// Instances the class

	/* If you need to manually take some parameters
	*  $result = $telegram->getData();
	*  $text = $result["message"] ["text"];
	*  $chat_id = $result["message"] ["chat"]["id"];
	*/

	$inline_query = $update["inline_query"];
	$text = $update["message"] ["text"];
	$chat_id = $update["message"] ["chat"]["id"];
	$user_id=$update["message"]["from"]["id"];
	$location=$update["message"]["location"];
	$reply_to_msg=$update["message"]["reply_to_message"];

	$this->shell(	$inline_query,$telegram,$text,$chat_id,$user_id,$location,$reply_to_msg);
	$db = NULL;

}

//gestisce l'interfaccia utente
 function shell($inline_query,$telegram,$text,$chat_id,$user_id,$location,$reply_to_msg)
{
	date_default_timezone_set('Europe/Rome');
	$today = date("Y-m-d H:i:s");

	if ($text == "/start" || $text == "Info") {
		$reply = "Benvenuto. Per ricercare un Museo, clicca sulla graffetta (📎) e poi 'posizione' oppure digita il nome del Comune. Verrà interrogato il DataBase del Mibact utilizzabile con licenza CC-BY presente su https://dati.beniculturali.it/. Grazie ai Linked OpenData e alle Query sullo Sparql Endpoint, verranno elencati fino a max 100 musei e luoghi della cultura. In qualsiasi momento scrivendo /start ti ripeterò questo messaggio di benvenuto.\nQuesto bot, non ufficiale, è stato realizzato da @piersoft.\nCodice sorgente -> https://github.com/piersoft/MuseiMibactBotV2.\nLa propria posizione viene ricercata grazie al geocoder di openStreetMap con Lic. odbl.";
	//	$reply .="\nWelcome. To search for a Museum, click on the paper clip (📎) and then 'position' or type the name of the municipality. It will be questioned DataBase Unique Mibact used with the CC-BY license, and will be listed up to max 50 museums. At any time by writing / start you repeat this welcome message. This bot, unofficially, has been realized by @piersoft and the source code for free reuse is on https://github.com/piersoft/MuseiMibactBot. Its position is searched through the geocoder OpenStreetMap with Lic. ODbL.";
		$content = array('chat_id' => $chat_id, 'text' => $reply,'disable_web_page_preview'=>true);
		$telegram->sendMessage($content);
		$log=$today. ";new chat started;" .$chat_id. "\n";
		$this->create_keyboard($telegram,$chat_id);
		exit;

	}else	if (strpos($inline_query["location"],'.') !== false){

		//	$this->location_manager_inline($inline_query,$telegram,$user_id,$chat_id,$location);
		//	exit;

		}else if ($text == "/location" || $text == "location") {

			$option = array(array($telegram->buildKeyboardButton("Invia la tua posizione / send your location", false, true)) //this work
                        );
    // Create a permanent custom keyboard
    $keyb = $telegram->buildKeyBoard($option, $onetime=false);
    $content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Attiva la localizzazione sul tuo smartphone / Turn on your GPS");
    $telegram->sendMessage($content);
		}

		//gestione segnalazioni georiferite
		elseif($location!=null)
		{

			$this->location_manager($telegram,$user_id,$chat_id,$location);
			exit;

		}	elseif(strpos($text,'🏛') === false && strpos($text,'/') === false && strpos($text,'-') === false){

			$text=strtolower($text);
			$location="Sto cercando i Musei e Luoghi della Cultura nel Comune di: / Searching for Town's Museums of: ".$text;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		//	sleep (1);
			$text=str_replace(" ","%20",$text);

			$html=file_get_contents('https://dati.beniculturali.it/sparql?default-graph-uri=&query=select+*+%7B%0D%0Aselect+distinct+%3Fs+as+%3Fsubject%0D%0A%3Fname%0D%0A%3Fdescription+%3FIdentifier%0D%0A%3Flat+%3Flon%0D%0A%3FIndirizzo%0D%0A%3FCodice_postale+%3FComune%0D%0A+%3FImage%0D%0A+%7B%0D%0A+graph+%3Chttp%3A%2F%2Fdati.beniculturali.it%2Fmibact%2Fluoghi%3E+%7B%0D%0A%3Fs+rdf%3Atype+cis%3ACulturalInstituteOrSite+%3B%0D%0A+cis%3AinstitutionalCISName+%3Fname+FILTER+%28lang%28%3Fname%29+%3D+%27it%27%29.%0D%0A%3Fs+l0%3Adescription%3Fdescription+FILTER+%28lang%28%3Fdescription%29+%3D+%27it%27%29.%0D%0A%3Fs+l0%3Aidentifier+%3FIdentifier.%0D%0Aoptional+%7B%3Fs+geo%3Alat+%3Flat%7D.%0D%0Aoptional+%7B%3Fs+geo%3Along+%3Flon%7D.%0D%0Aoptional+%7B%3Fs+foaf%3Adepiction+%3FImage%7D.%0D%0A%0D%0A+%3Fs+cis%3AhasSite+%5Bcis%3AsiteAddress+%3Faddress+%5D+.%0D%0A+%3Faddress+clvapit%3AfullAddress+%3FIndirizzo+filter%28regex%28lcase%28str%28%3FIndirizzo%29%29%2C%22'.$text.'%22%29%29.%0D%0A+%3Faddress+clvapit%3ApostCode+%3FCodice_postale.%0D%0A+%3Faddress+clvapit%3AhasCity+%5Brdfs%3Alabel+%3FComune%5D+filter%28%28lcase%28str%28%3FComune%29%29+%3D+%22'.$text.'%22%29%29.%0D%0A%0D%0A+%0D%0A+%7D%0D%0A%7D%0D%0AORDER+BY+%3Fs%0D%0A+%7D%0D%0A+limit+1000%0D%0A+offset+0&format=application%2Fsparql-results%2Bjson&timeout=0&debug=on');

			$doc=json_decode($html);
			$count=0;
			$divs0   = $doc->{'results'};
						if (count($doc->{'results'}->{'bindings'})<1) {
						$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei censiti Mibact in questo luogo",'disable_web_page_preview'=>true);
							$telegram->sendMessage($content);
							$this->create_keyboard($telegram,$chat_id);
								exit;
			        //echo "non ci sono musei";
					}




		$divsl =[];
		$divs =[];
		$divs1 = [];
		$dival=[];
		$diva=[];
		$diva1=[];


		foreach($doc->{'results'}->{'bindings'} as $div0) {
			$divsl   = $doc->{'results'}->{'bindings'}[$count]->{'subject'}->{'value'};
			$divsl=str_replace("http://dati.beniculturali.it/mibact/luoghi/resource/CulturalInstituteOrSite/","",$divsl);

			$divs    = $doc->{'results'}->{'bindings'}[$count]->{'description'}->{'value'};
			$divs1   = $doc->{'results'}->{'bindings'}[$count]->{'name'}->{'value'};

			array_push($dival,$divsl);
			array_push($diva,$divs );
			array_push($diva1,$divs1);
					$count++;
		}



		echo "Count: ".$count."\n";


		//$count=3;
if ($count > 1001){
	$content = array('chat_id' => $chat_id, 'text' => "Troppe richieste, registringi la ricerca");
		$telegram->sendMessage($content);
		exit;
}
$option=[];
		for ($i=0;$i<$count;$i++){
		$alert.="\n\n";
	//	$alert.= $diva1[$i]."\n";
	//	$alert .="🏛 ".$dival[$i]."\n";
		$option[$i]=$dival[$i];
	$alert.= "🏛 /".$dival[$i]."-".$diva1[$i]."\n";
		$alert.="__________________";
	}

	//	echo $alert;

		$chunks = str_split($alert, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
//			$forcehide=$telegram->buildForceReply(true);
				//chiedo cosa sta accadendo nel luogo
	//		$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_markup' =>$forcehide,'disable_web_page_preview'=>true);
	$forcehide=$telegram->buildForceReply(true);
		//chiedo cosa sta accadendo nel luogo
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);

		}
		$optionf=array([]);
		for ($i=0;$i<$count;$i++){
			array_push($optionf,["🏛 ".$dival[$i]."_".$diva1[$i]]);

		}
				$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
				$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su 🏛 per dettagli / Click on 🏛 for details]");
				$telegram->sendMessage($content);

	}elseif(strpos($text,'🏛') !== false || strpos($text,'/') !== false && strpos($text,'start') == false){
		function extractString($string, $start, $end) {
				$string = " ".$string;
				$ini = strpos($string, $start);
				if ($ini == 0) return "";
				$ini += strlen($start);
				$len = strpos($string, $end, $ini) - $ini;
				return substr($string, $ini, $len);
		}

if (strpos($text,'/') !== false){
	$text=str_replace("/","",$text);
//}elseif (strpos($text,'-') !== false) {
//	$text=extractString($text,"🏛 ","-");
}else $text=extractString($text,"🏛 ","_");


		$text=str_replace("🏛 ","",$text);

		$text=str_replace("🏛","",$text);

	if (empty($text) !== false){
		$content = array('chat_id' => $chat_id, 'text' => "Nessun risultato / No result",'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
				$this->create_keyboard($telegram,$chat_id);
			exit;
	}
			$location="Sto cercando: / Searching for: ".$text;
			$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
			$telegram->sendMessage($content);
		//	sleep (1);
	//		$text=urlencode($text);
			$text=str_replace("-","%2D",$text);
	//		$text=str_replace("'","%27",$text);
			$text=str_replace(" ","%20",$text);
	//		$text=str_replace("DBUnico.","",$text);
	//		$text=str_replace("MiBAC.","",$text);
	//		$text=str_replace("\"","%22",$text);
	//		$text=str_replace("“","%E2%80%9C",$text);



	$html=file_get_contents('https://dati.beniculturali.it/sparql?default-graph-uri=&query=select+*+%7B%0D%0Aselect+distinct+%3Fs+as+%3Fsubject%0D%0A%3Fname%0D%0A%3Furl%0D%0A%3Furltmp%0D%0A%3Fdescription+%3FIdentifier%0D%0A%3Flat+%3Flon%0D%0A%3FIndirizzo%0D%0A%3FCodice_postale+%3FComune+%3FProvincia%0D%0A%3FPrenotazioni+%0D%0A%3FTelefono+%3FFax+%3FEmail+%3FWebSite+%3FImage%0D%0A+%7B%0D%0A+graph+%3Chttp%3A%2F%2Fdati.beniculturali.it%2Fmibact%2Fluoghi%3E+%7B%0D%0A%3Fs+rdf%3Atype+cis%3ACulturalInstituteOrSite+%3B%0D%0A+cis%3AinstitutionalCISName+%3Fname+FILTER+%28lang%28%3Fname%29+%3D+%27it%27%29.%0D%0Aoptional+%7B+%3Fs+l0%3Adescription%3Fdescription+%7D+FILTER+%28lang%28%3Fdescription%29+%3D+%27it%27%29%0D%0A%3Fs+l0%3Aidentifier+%3FIdentifier%0D%0ABIND%28REPLACE%28%3FIdentifier%2C+%22MiBAC.%22%2C+%22%22%2C+%22%22%29+AS+%3Furltmp%29%0D%0ABIND%28REPLACE%28%3Furltmp%2C+%22DBUnico.%22%2C+%22%22%2C+%22%22%29+AS+%3Furl%29%0D%0Afilter%28%28%3Furl%3D%22'.$text.'%22%29%29.%0D%0Aoptional+%7B+%3Fs+geo%3Alat+%3Flat%7D.%0D%0Aoptional+%7B+%3Fs+geo%3Along+%3Flon%7D.%0D%0Aoptional+%7B+%3Fs+foaf%3Adepiction+%3FImage%7D.%0D%0A%0D%0A+%3Fs+cis%3AhasSite+%5Bcis%3AsiteAddress+%3Faddress+%5D+.%0D%0A+%3Faddress+clvapit%3AfullAddress+%3FIndirizzo.%0D%0A+%3Faddress+clvapit%3ApostCode+%3FCodice_postale.%0D%0A+%3Faddress+clvapit%3AhasCity+%5Brdfs%3Alabel+%3FComune%5D.%0D%0A+%3Faddress+clvapit%3AhasProvince+%5Brdfs%3Alabel+%3FProvincia%5D.%0D%0A%0D%0Aoptional+%7B%3Fs+accessCondition%3AhasAccessCondition+%5Brdf%3Atype+accessCondition%3ABooking+%3B%0D%0A+rdfs%3Alabel+%3FPrenotazioni%5D+%7D%0D%0Aoptional+%7B%0D%0A+%3Fs+smapit%3AhasOnlineContactPoint+%3FcontactPoint+.%0D%0A+optional+%7B+%3FcontactPoint+smapit%3AhasTelephone+%5Bsmapit%3AhasTelephoneType+%3Chttps%3A%2F%2Fw3id.org%2Fitalia%2Fcontrolled-vocabulary%2Fclassifications-for-public-services%2Fchannel%2F031%3E+%3B%0D%0A+smapit%3AtelephoneNumber+%3FTelefono%5D+%7D%0D%0A+optional+%7B+%3FcontactPoint+smapit%3AhasTelephone+%5Bsmapit%3AhasTelephoneType+%3Chttps%3A%2F%2Fw3id.org%2Fitalia%2Fcontrolled-vocabulary%2Fclassifications-for-public-services%2Fchannel%2F033%3E+%3B%0D%0A+smapit%3AtelephoneNumber+%3FFax%5D+%7D%0D%0A+optional+%7B+%3FcontactPoint+smapit%3AhasEmail+%5Bsmapit%3AemailAddress+%3FEmail%5D+%7D%0D%0A+optional+%7B+%3FcontactPoint+smapit%3AhasWebSite+%5Bsmapit%3AURL+%3FWebSite%5D+%7D%0D%0A%7D+%0D%0Aoptional+%7B%0D%0A+%3Fs+potapit%3AhasTicket+%3Fticket+.%0D%0A+%3Foffer+potapit%3Aincludes+%3Fticket+%3B%0D%0Apotapit%3AhasPriceSpecification+%5Bpotapit%3AhasCurrencyValue+%3FBiglietti%5D%0D%0A%7D%0D%0A+%7D%0D%0A%7D%0D%0AORDER+BY+%3Fs%0D%0A+%7D%0D%0A+limit+1000%0D%0A+offset+0&format=application%2Fsparql-results%2Bjson&timeout=0&debug=on');

	$doc=json_decode($html);
	$divs0   = $doc->{'results'};

	if (count($doc->{'results'}->{'bindings'})<1) {
				$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei censiti Mibact in questo luogo",'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$this->create_keyboard($telegram,$chat_id);
						exit;

			}



/*
"subject",
"name",
"description",
"Identifier",
"lat",
"lon",
"Indirizzo",
"Codice_postale",
"Comune",
"Provincia",
"Prenotazioni",
"Telefono",
"Fax",
"Email",
"WebSite",
"Image"
*/

		$divs0   = $doc->{'results'};
			$diva1 =[];
		$diva2=[];
		$diva3=[];
		$diva4=[];
		$diva5=[];
		$diva6=[];
		$diva7=[];
		$diva8=[];
		$diva9=[];
		$diva10=[];
		$diva11=[];
		$diva12=[];
		$diva13=[];

		$count=0;
		foreach($doc as $div0) {
			$divsl   = $doc->{'results'}->{'bindings'}[$count]->{'subject'}->{'value'};
			$divsl=str_replace("http://dati.beniculturali.it/mibact/luoghi/resource/CulturalInstituteOrSite/","",$divsl);
			$divs2    = $doc->{'results'}->{'bindings'}[$count]->{'description'}->{'value'};
			$divs1   = $doc->{'results'}->{'bindings'}[$count]->{'name'}->{'value'};
			$divs5   = $doc->{'results'}->{'bindings'}[$count]->{'WebSite'}->{'value'};
			$divs6   = $doc->{'results'}->{'bindings'}[$count]->{'Email'}->{'value'};
			$divs7   = $doc->{'results'}->{'bindings'}[$count]->{'Telefono'}->{'value'};

			$divs9   = $doc->{'results'}->{'bindings'}[$count]->{'lat'}->{'value'};
			$divs10   = $doc->{'results'}->{'bindings'}[$count]->{'lon'}->{'value'};
			$divs11   = $doc->{'results'}->{'bindings'}[$count]->{'Indirizzo'}->{'value'};
			$divs12   = $doc->{'results'}->{'bindings'}[$count]->{'Image'}->{'value'};
			$divs12   = $doc->{'results'}->{'bindings'}[$count]->{'Image'}->{'value'};

			$divs13   = $doc->{'results'}->{'bindings'}[$count]->{'Prenotazioni'}->{'value'};

			array_push($dival,$divsl);
			array_push($diva,$divs);
			array_push($diva1,$divs1);
			array_push($diva2,$divs2);
			array_push($diva3,$divs3);
			array_push($diva4,$divs4);
			array_push($diva5,$divs5);
			array_push($diva6,$divs6);
			array_push($diva7,$divs7);
			array_push($diva8,$divs8);
			array_push($diva9,$divs9);
			array_push($diva10,$divs10);
			array_push($diva11,$divs11);
			array_push($diva12,$divs12);
			array_push($diva13,$divs13);

			$count++;
		}

	//	echo "Count: ".$count."\n";

		//$count=3;
if ($count > 50){
	$content = array('chat_id' => $chat_id, 'text' => "Troppe richieste, registringi la ricerca");
		$telegram->sendMessage($content);
		exit;
}
		for ($i=0;$i<$count-1;$i++){
		$alert.="\n\n";
		$alert.= "*".$diva1[$i]."*\n";
		$alert.= "".$diva2[$i]."\n";
		if ($diva3[$i]!=NULL) $alert.= "\n".$diva3[$i];
		if ($diva4[$i]!=NULL) $alert.= "\nApertura: ".$diva4[$i];
		if ($diva12[$i]!=NULL) {

			$longUrl = $diva12[$i];

			$alert .="\nFoto/Video: ".$longUrl;

		}
		if ($diva5[$i]!=NULL)$alert.= "\nSitoweb: ".$diva5[$i];
		if ($diva6[$i]!=NULL) $alert.= "\nEmail: ".$diva6[$i];
		if ($diva7[$i]!=NULL)$alert.= "\nTelefono: ".$diva7[$i];
		if ($diva11[$i]!=NULL)$alert.= "\nIndirizzo: ".$diva11[$i];
		if ($diva13[$i]!=NULL)$alert.= "\nPrenotazione: ".$diva13[$i];
		if ($diva8[$i]!=NULL) $alert.= "\nChiusura settimanale: ".$diva8[$i];


		if ($diva9[$i]!=NULL){
						$longUrl = "http://www.openstreetmap.org/?mlat=".$diva9[$i]."&mlon=".$diva10[$i]."#map=19/".$diva9[$i]."/".$diva10[$i];

$option = array( array( $telegram->buildInlineKeyboardButton("Mappa", $url=$longUrl)));
$keyb = $telegram->buildInlineKeyBoard($option);
$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "Vai alla");
$telegram->sendMessage($content);
					}

			$alert.="\n\n__________________";



	}

	//	echo $alert;

		$chunks = str_split($alert, self::MAX_LENGTH);
		foreach($chunks as $chunk) {
//			$forcehide=$telegram->buildForceReply(true);
				//chiedo cosa sta accadendo nel luogo
	//		$content = array('chat_id' => $chat_id, 'text' => $chunk, 'reply_markup' =>$forcehide,'disable_web_page_preview'=>true);
	$forcehide=$telegram->buildForceReply(true);
		//chiedo cosa sta accadendo nel luogo
	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>false,'parse_mode'=>'Markdown');

			$telegram->sendMessage($content);


		}
			if ($diva12[0]!=NULL) {

		$reply=$shortLink['id'];
		$content = array('chat_id' => $chat_id, 'text' => $reply);
			$telegram->sendMessage($content);
		}

			$this->create_keyboard($telegram,$chat_id);
			exit;

	}





}


// Crea la tastiera
function create_keyboard($telegram, $chat_id)
 {
	 $forcehide=$telegram->buildKeyBoardHide(true);
	 $content = array('chat_id' => $chat_id, 'text' => "- Digita un Comune oppure invia la tua posizione tramite la graffetta (📎) o cliccando /location\n- Send your position clicking 📎 or digit Town or click /location\n- /start x Info&Credits", 'reply_markup' =>$forcehide);
	 $telegram->sendMessage($content);

 }




function location_manager($telegram,$user_id,$chat_id,$location)
	{

			$lon=$location["longitude"];
			$lat=$location["latitude"];
			$response=$telegram->getData();
				$reply="http://nominatim.openstreetmap.org/reverse?email=piersoft2@gmail.com&format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1";
				$json_string = file_get_contents($reply);
				$parsed_json = json_decode($json_string);
				//var_dump($parsed_json);
				$comune="";
				$temp_c1 =$parsed_json->{'display_name'};

				if ($parsed_json->{'address'}->{'town'}) {
					$temp_c1 .="\nCittà: ".$parsed_json->{'address'}->{'town'};
					$comune .=$parsed_json->{'address'}->{'town'};
				}else 	$comune .=$parsed_json->{'address'}->{'city'};

				if ($parsed_json->{'address'}->{'village'}) $comune .=$parsed_json->{'address'}->{'village'};

				if (empty($comune) == true){

						$content = array('chat_id' => $chat_id, 'text' => "Non ci sono musei", 'reply_to_message_id' =>$bot_request_message_id,'disable_web_page_preview'=>true);
					 $telegram->sendMessage($content);
					// $this->create_keyboard_temp($telegram,$chat_id);
					exit;

				}
			//	$location="Comune di: ".$comune." tramite le coordinate che hai inviato: ".$lat.",".$lon;
				$location="Comune di / Town of: ".$comune;

				$content = array('chat_id' => $chat_id, 'text' => $location,'disable_web_page_preview'=>true);
				$telegram->sendMessage($content);

			  $alert="";
				$comune=strtolower($comune);
					$comune=str_replace(" ","%20",$comune);
				$text=$comune;

$html=file_get_contents('https://dati.beniculturali.it/sparql?default-graph-uri=&query=select+*+%7B%0D%0Aselect+distinct+%3Fs+as+%3Fsubject%0D%0A%3Fname%0D%0A%3Fdescription+%3FIdentifier%0D%0A%3Flat+%3Flon%0D%0A%3FIndirizzo%0D%0A%3FCodice_postale+%3FComune%0D%0A+%3FImage%0D%0A+%7B%0D%0A+graph+%3Chttp%3A%2F%2Fdati.beniculturali.it%2Fmibact%2Fluoghi%3E+%7B%0D%0A%3Fs+rdf%3Atype+cis%3ACulturalInstituteOrSite+%3B%0D%0A+cis%3AinstitutionalCISName+%3Fname+FILTER+%28lang%28%3Fname%29+%3D+%27it%27%29.%0D%0A%3Fs+l0%3Adescription%3Fdescription+FILTER+%28lang%28%3Fdescription%29+%3D+%27it%27%29.%0D%0A%3Fs+l0%3Aidentifier+%3FIdentifier.%0D%0Aoptional+%7B%3Fs+geo%3Alat+%3Flat%7D.%0D%0Aoptional+%7B%3Fs+geo%3Along+%3Flon%7D.%0D%0Aoptional+%7B%3Fs+foaf%3Adepiction+%3FImage%7D.%0D%0A%0D%0A+%3Fs+cis%3AhasSite+%5Bcis%3AsiteAddress+%3Faddress+%5D+.%0D%0A+%3Faddress+clvapit%3AfullAddress+%3FIndirizzo+filter%28regex%28lcase%28str%28%3FIndirizzo%29%29%2C%22'.$text.'%22%29%29.%0D%0A+%3Faddress+clvapit%3ApostCode+%3FCodice_postale.%0D%0A+%3Faddress+clvapit%3AhasCity+%5Brdfs%3Alabel+%3FComune%5D+filter%28%28lcase%28str%28%3FComune%29%29+%3D+%22'.$text.'%22%29%29.%0D%0A%0D%0A+%0D%0A+%7D%0D%0A%7D%0D%0AORDER+BY+%3Fs%0D%0A+%7D%0D%0A+limit+100%0D%0A+offset+0&format=application%2Fsparql-results%2Bjson&timeout=0&debug=on');

	$doc=json_decode($html);
	$divs0   = $doc->{'results'};

	if (count($doc->{'results'}->{'bindings'})<1) {
				$content = array('chat_id' => $chat_id, 'text' => "Non ci risultano Musei censiti Mibact in questo luogo",'disable_web_page_preview'=>true);
					$telegram->sendMessage($content);
					$this->create_keyboard($telegram,$chat_id);
						exit;
					//echo "non ci sono musei";
			}


			//var_dump($doc);
/*
"subject",
"name",
"description",
"Identifier",
"lat",
"lon",
"Indirizzo",
"Codice_postale",
"Comune",
"Provincia",
"Prenotazioni",
"Telefono",
"Fax",
"Email",
"WebSite",
"Image"
*/

		$divs0   = $doc->{'results'};
			$diva1 =[];
		$diva2=[];
		$diva3=[];
		$diva4=[];
		$diva5=[];
		$diva6=[];
		$diva7=[];
		$diva8=[];
		$diva9=[];
		$diva10=[];
		$diva11=[];
		$diva12=[];
		$diva13=[];
	$dival=[];
		$count=0;
		foreach($doc->{'results'}->{'bindings'} as $div0) {
			$divsl   = $doc->{'results'}->{'bindings'}[$count]->{'subject'}->{'value'};
			$divsl=str_replace("http://dati.beniculturali.it/mibact/luoghi/resource/CulturalInstituteOrSite/","",$divsl);
			$divs2    = $doc->{'results'}->{'bindings'}[$count]->{'description'}->{'value'};
			$divs1   = $doc->{'results'}->{'bindings'}[$count]->{'name'}->{'value'};
			$divs5   = $doc->{'results'}->{'bindings'}[$count]->{'WebSite'}->{'value'};
			$divs6   = $doc->{'results'}->{'bindings'}[$count]->{'Email'}->{'value'};
			$divs7   = $doc->{'results'}->{'bindings'}[$count]->{'Telefono'}->{'value'};

			$divs9   = $doc->{'results'}->{'bindings'}[$count]->{'lat'}->{'value'};
			$divs10   = $doc->{'results'}->{'bindings'}[$count]->{'lon'}->{'value'};
			$divs11   = $doc->{'results'}->{'bindings'}[$count]->{'Indirizzo'}->{'value'};
			$divs12   = $doc->{'results'}->{'bindings'}[$count]->{'Image'}->{'value'};


			array_push($dival,$divsl);
			array_push($diva,$divs);
			array_push($diva1,$divs1);
			array_push($diva2,$divs2);
			array_push($diva3,$divs3);
			array_push($diva4,$divs4);
			array_push($diva5,$divs5);
			array_push($diva6,$divs6);
			array_push($diva7,$divs7);
			array_push($diva8,$divs8);
			array_push($diva9,$divs9);
			array_push($diva10,$divs10);
			array_push($diva11,$divs11);
			array_push($diva12,$divs12);
			array_push($diva13,$divs13);

			$count++;
		}

	//	echo "Count: ".$count."\n";

		//$count=3;
if ($count > 50){
	$content = array('chat_id' => $chat_id, 'text' => "Troppe richieste, registringi la ricerca");
		$telegram->sendMessage($content);
		exit;
}


				$option=[];
				for ($i=0;$i<$count;$i++){
				$alert.="\n\n";
				$alert.= $diva1[$i]."\n";
				$alert.= "🏛 /".$dival[$i]."\n";
				$option[$i]=$dival[$i];
			//	$alert .="Clicca per dettagli: /".$diva1[$i]."\n";
				if ($diva9[$i]!=NULL){
					$lat10=floatval($diva9[$i]);
					$long10=floatval($diva10[$i]);
					$theta = floatval($lon)-floatval($long10);
					$dist =floatval( sin(deg2rad($lat)) * sin(deg2rad($lat10)) +  cos(deg2rad($lat)) * cos(deg2rad($lat10)) * cos(deg2rad($theta)));
					$dist = floatval(acos($dist));
					$dist = floatval(rad2deg($dist));
					$miles = floatval($dist * 60 * 1.1515 * 1.609344);


					if ($miles >=1){
						$alert .="Distanza: ".number_format($miles, 2, '.', '')." Km\n";
					} else $alert .="Distanza: ".number_format(($miles*1000), 0, '.', '')." mt\n";

				}

					$alert.="__________________";


			}

			//	echo $alert;

				$chunks = str_split($alert, self::MAX_LENGTH);
				foreach($chunks as $chunk) {
		    $forcehide=$telegram->buildForceReply(true);
		   	$content = array('chat_id' => $chat_id, 'text' => $chunk,'disable_web_page_preview'=>true);
  			$telegram->sendMessage($content);

				}


					$optionf=array([]);
					for ($i=0;$i<$count;$i++){
						array_push($optionf,["🏛 ".$dival[$i]."_".$diva1[$i]]);

					}
							$keyb = $telegram->buildKeyBoard($optionf, $onetime=false);
							$content = array('chat_id' => $chat_id, 'reply_markup' => $keyb, 'text' => "[Clicca su 🏛 per dettagli / Click on 🏛 for details]");
							$telegram->sendMessage($content);
					//		$telegram->buildKeyBoardHide(true);
					exit;

					}

				}

				?>
