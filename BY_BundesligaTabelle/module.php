<?
/* 2do ********************************************************
> 
***************************************************************/

class BundesligaTabelle extends IPSModule
{

    public function Create()
    {
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyString("LigaSelection", "Liga1");
        $this->RegisterPropertyBoolean("FarbenAktiv", true);
        $this->RegisterPropertyInteger("Intervall", 900);
        $this->RegisterTimer("UpdateTabelle", 0, 'BLT_Update($_IPS[\'TARGET\']);');
    }

    public function Destroy()
    {
        $this->UnregisterTimer("UpdateTabelle");
        
        //Never delete this line!!
        parent::Destroy();
    }

    public function ApplyChanges()
    {
        //Never delete this line!
        parent::ApplyChanges();
        
        //Variable erstellen
        $this->RegisterVariableString("HTMLBoxTabelle", "Tabelle", "~HTMLBox");
        
        $this->Update();
        $this->SetTimerInterval("UpdateTabelle", $this->ReadPropertyInteger("Intervall"));
    }

    public function Update()
    {
        $LigaAuswahl = $this->ReadPropertyString("LigaSelection");
				$Sonderzeichen = array("Ã¶" => "ö", "Ã¼" => "ü", "ÃŸ" => "ß", "Ã¤" => "ä", "Ã„" => "Ä", "Ãœ" => "Ü", "Ã–" => "Ö", "Ã©" => "Ë", "Ã©" => "é");
				
				switch ($LigaAuswahl) {
				   case "Liga1":
				     	$BLtabSportde_URL = "http://www.dfb.de/bundesliga/spieltagtabelle";
				   break;
			
				   case "Liga2":
				     	$BLtabSportde_URL = "http://www.dfb.de/2-bundesliga/spieltagtabelle";
				   break;
			
				   case "Liga3":
				     	$BLtabSportde_URL = "http://www.dfb.de/3-liga/spieltagtabelle";
				   break;
				}

				
				$page = @file_get_contents($BLtabSportde_URL);
				if ($page === false)
				{
						throw new Exception("Es konnten keine Daten von der Webseite geladen werden!", E_USER_WARNING);
			  }
			  
			  
				/*
				$curl = curl_init($BLtabSportde_URL);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
				$page = curl_exec($curl);
				
			
			
				if(curl_errno($curl)) // Error-Check
				{
				    echo 'Error: ' . curl_error($curl);
				    exit;
				}
				curl_close($curl);
				*/
				
			
				$DOM = new DOMDocument;
			
			
				libxml_use_internal_errors(true);
			
				if (!$DOM->loadHTML($page))
				    {
				        $errors="";
				        foreach (libxml_get_errors() as $error)  {
				            $errors.=$error->message."<br/>";
				        }
				        libxml_clear_errors();
				        print "libxml errors:<br>$errors";
				        return;
				    }
				$xpath = new DOMXPath($DOM);
			
			
				// Platz-Nummer
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[1]');
				$i = 0;
				foreach ($content as $entry01) {
			   	$BundesligaTabelleAR["Platz"][] = $entry01->nodeValue;
				   $i++;
				}
			
				// Verein-Logo URL
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[2]/img/@src');
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["VereinLogo"][] = $entry01->nodeValue;
				}
			
				// Verein-Name
			   $content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[3]');
			   foreach ($content as $entry01) {
					$BundesligaTabelleAR["VereinName"][] = strtr(utf8_decode($entry01->nodeValue), $Sonderzeichen);
				}
			
				// Anzahl Spiele
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[4]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Spiele"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Anzahl Siege
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[5]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Siege"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Anzahl Unentschieden
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[6]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Unentschieden"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Anzahl Niederlagen
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[7]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Niederlagen"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Torverhältnis
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[8]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Torverhaeltnis"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Tor-Differenz
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[9]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["TorDifferenz"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
			
				// Punkte
				$content = $xpath->query('.//*[@class="data-table table-bordered"]/tbody/tr/td[10]');
				$i = 0;
				foreach ($content as $entry01) {
					$BundesligaTabelleAR["Punkte"][] = utf8_decode($entry01->nodeValue);
					$i++;
				}
        

        /*********** Ab hier beginnt die "Verarbeitung" der Daten aus dem Array **************/
        
				$TabelleMitFarben = $this->ReadPropertyBoolean("FarbenAktiv");   // true = Tabelle in HTMLBox mit Farben, false = Tabelle in HTMLBox ohne Farben
				$LigaAuswahl = $this->ReadPropertyString("LigaSelection");
			
				// HTML CSS Style definieren (Tabelle, Schrift, Farben, ...)
				if ($TabelleMitFarben == false) {
				$HTML_CSS_Style = '';
				}
				else {
				$HTML_CSS_Style = '<style type="text/css">
				.bt {border-collapse;border-spacing:0;}
				.bt td {font-family:Arial, sans-serif;font-size:14px;padding:1px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
				.bt th {font-family:Arial, sans-serif;font-size:14px;font-weigth:normal;padding:1px 10px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;}
				.bt .tb-title{font-size:12px;background-color:#000000;color:#FFFFFF;text-align:center}
				.bt .tb-cl{font-size:12px;background-color:#32CB00;color:#FFFFFF;text-align:center}
				.bt .tb-clqual{font-size:12px;background-color:#009901;color:#FFFFFF;text-align:center}
				.bt .tb-eurol{font-size:12px;background-color:#3166FF;color:#FFFFFF;text-align:center}
				.bt .tb-normal{font-size:12px;background-color:#FFFFFF;color:#000000;text-align:center}
				.bt .tb-abstgrel{font-size:12px;background-color:#FD6864;color:#FFFFFF;text-align:center}
				.bt .tb-abstg{font-size:12px;background-color:#FE0000;color:#FFFFFF;text-align:center}
				</style>';
				}
				
				
				// HTML Ausgabe generieren
				$TitelAR = array("Platz","Verein","Spiele","S","U","N","TV","TDiff","Punkte");  // Hier könnt ihr die Überschriften der Spalten ändern
				$HTML = '<html>'.$HTML_CSS_Style;
				$HTML .= '<table class="bt">';
				$HTML .= '<tr><th class="tb-title">'.$TitelAR[0].'</th><th class="tb-title" colspan="2">'.$TitelAR[1].'</th><th class="tb-title">'.$TitelAR[2].'</th><th class="tb-title">'.$TitelAR[3].'</th><th class="tb-title">'.$TitelAR[4].'</th><th class="tb-title">'.$TitelAR[5].'</th><th class="tb-title">'.$TitelAR[6].'</th><th class="tb-title">'.$TitelAR[7].'</th><th class="tb-title">'.$TitelAR[8].'</th></tr>';
				
				// Tabelle der 1. Liga generieren
				if ($LigaAuswahl == "Liga1") {
					for ($h=0; $h<count($BundesligaTabelleAR["Platz"]); $h++) {
						if (($h == 0) OR ($h == 1) OR ($h == 2)) {
						   $HTML .= '<tr><th class="tb-cl">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-cl"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-cl">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif ($h == 3) {
						   $HTML .= '<tr><th class="tb-clqual">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-clqual"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-clqual">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h == 4) OR ($h == 5)) {
						   $HTML .= '<tr><th class="tb-eurol">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-eurol"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-eurol">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-eurol">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h >= 6) AND ($h <= 14)) {
						   $HTML .= '<tr><th class="tb-normal">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-normal"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-normal">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif ($h == 15) {
						   $HTML .= '<tr><th class="tb-abstgrel">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-abstgrel"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-abstgrel">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h == 16) OR ($h == 17)) {
						   $HTML .= '<tr><th class="tb-abstg">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-abstg"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-abstg">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
					}
				}
				elseif ($LigaAuswahl == "Liga2") {  // Tabelle der 2. Liga generieren
					for ($h=0; $h<count($BundesligaTabelleAR["Platz"]); $h++) {
						if (($h == 0) OR ($h == 1)) {
						   $HTML .= '<tr><th class="tb-cl">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-cl"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-cl">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif ($h == 2) {
						   $HTML .= '<tr><th class="tb-clqual">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-clqual"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-clqual">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h >= 3) AND ($h <= 14)) {
						   $HTML .= '<tr><th class="tb-normal">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-normal"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-normal">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif ($h == 15) {
						   $HTML .= '<tr><th class="tb-abstgrel">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-abstgrel"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-abstgrel">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-abstgrel">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h == 16) OR ($h == 17)) {
						   $HTML .= '<tr><th class="tb-abstg">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-abstg"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-abstg">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
					}
				}
				elseif ($LigaAuswahl == "Liga3") {  // Tabelle der 3. Liga generieren
					for ($h=0; $h<count($BundesligaTabelleAR["Platz"]); $h++) {
						if (($h == 0) OR ($h == 1)) {
						   $HTML .= '<tr><th class="tb-cl">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-cl"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-cl">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-cl">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif ($h == 2) {
						   $HTML .= '<tr><th class="tb-clqual">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-clqual"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-clqual">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-clqual">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h >= 3) AND ($h <= 16)) {
						   $HTML .= '<tr><th class="tb-normal">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-normal"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-normal">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-normal">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
						elseif (($h == 17) OR ($h == 18) OR ($h == 19)) {
						   $HTML .= '<tr><th class="tb-abstg">'.$BundesligaTabelleAR["Platz"][$h].'</th><th class="tb-abstg"><img height="25" width="25" src="'.$BundesligaTabelleAR["VereinLogo"][$h].'"></img></th><th class="tb-abstg">'.$BundesligaTabelleAR["VereinName"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Spiele"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Siege"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Unentschieden"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Niederlagen"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Torverhaeltnis"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["TorDifferenz"][$h].'</th><th class="tb-abstg">'.$BundesligaTabelleAR["Punkte"][$h].'</th></tr>';
						}
					}
				}
				$HTML .= '</table></html>';
				
				
				//HTML-Tabelle in Variable schreiben
				$this->SetValueString("HTMLBoxTabelle", $HTML);
    }

   
    private function SetValueString($Ident, $Value)
    {
    		$id = $this->GetIDforIdent($Ident);
    		if (GetValueString($id) <> $Value)
    		{
    				SetValueString($id, $Value);
    				return true;
    		}
    		return false;
  	}

    protected function RegisterTimer($Name, $Interval, $Script)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            $id = 0;


        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception("Ident with name " . $Name . " is used for wrong object type", E_USER_WARNING);

            if (IPS_GetEvent($id)['EventType'] <> 1)
            {
                IPS_DeleteEvent($id);
                $id = 0;
            }
        }

        if ($id == 0)
        {
            $id = IPS_CreateEvent(1);
            IPS_SetParent($id, $this->InstanceID);
            IPS_SetIdent($id, $Name);
        }
        IPS_SetName($id, $Name);
        IPS_SetHidden($id, true);
        IPS_SetEventScript($id, $Script);
        if ($Interval > 0)
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);

            IPS_SetEventActive($id, true);
        } else
        {
            IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, 1);

            IPS_SetEventActive($id, false);
        }
    }

    protected function UnregisterTimer($Name)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id > 0)
        {
            if (!IPS_EventExists($id))
                throw new Exception('Timer not present', E_USER_NOTICE);
            IPS_DeleteEvent($id);
        }
    }

    protected function SetTimerInterval($Name, $Interval)
    {
        $id = @IPS_GetObjectIDByIdent($Name, $this->InstanceID);
        if ($id === false)
            throw new Exception('Timer not present', E_USER_WARNING);
        if (!IPS_EventExists($id))
            throw new Exception('Timer not present', E_USER_WARNING);

        $Event = IPS_GetEvent($id);

        if ($Interval < 1)
        {
            if ($Event['EventActive'])
                IPS_SetEventActive($id, false);
        }
        else
        {
            if ($Event['CyclicTimeValue'] <> $Interval)
                IPS_SetEventCyclic($id, 0, 0, 0, 0, 1, $Interval);
            if (!$Event['EventActive'])
                IPS_SetEventActive($id, true);
        }
    }
}
?>