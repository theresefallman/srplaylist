SRplaylist
==========

## Introduktion
SRplaylist kan användas för att hämta en låtlista för de låtar som spelas just nu på någon av Sveriges Radios kanaler. Informationen hämtas från olika källor av [SR:s api](http://sverigesradio.se/api/documentation/v2) men selekterar sedan ut den information om kanaler och låtar som är mest intressant och relevant.

### Krav
+ Minst PHP 5.3
+ MySQL databas
+ APC (Alternative PHP Cache)

## Funktionalitet
SRplaylist kommer i dagsläget med två metoder, en för att hämta kanaler och en för att hämta spellista. Returnerad data kommer i formatet JSON.
+ Hämta information om samtliga kanaler från Sveriges Radio
+ Hämta en spellista för specifik kanal

## Metoder
### getPlaylist()
Hämtar en spellista för en kanal som innehåller information om kanalen i sig samt titel och artist på låt som spelas just nu och för nästkommande låt.

#### Tillåtna inparametrar
Till getPlaylist() kan man skicka med ett namn på en kanal. Parametern ska vara en sträng och måste vara en [giltig kanal](http://sverigesradio.se/sida/allakanaler.aspx) från Sveriges Radio. Om en parameter inte skickas med används kanalen P3 som default.

#### Returnerad data
Nedan visas ett exempel på hur ett svar från metoden kan se ut. Vid lyckat anrop kommer alltid information om kanalen med till exempel url för ljud, att returneras. Observera att api:et kan returnera null-värden för nuvarande låt eller nästkommande låt, ibland båda. Det beror helt på om det faktiskt spelas låtar på den kanalen just då.
	
	{
	  "channelInfo": [
		{
		  "channel_id": "164",
		  "name": "P3",
		  "audio_url": "http://sverigesradio.se/topsy/direkt/164.mp3",
		  "channel_url": "http://sverigesradio.se/p3"
		}
	  ],
	  "playlist": [
		{
			"currentSong": {
			  "title": "Adorn",
			  "artist": "Miguel"
			},
			"nextSong": {
			  "title": "Troublemaker",
			  "artist": "Olly Murs & Flo Rida"
			}
		  }
	  	]
	}
	
### getAllChannels()
Hämtar information om samtliga kanaler från Sveriges radio.

#### Returnerad data
Ett lyckat anrop returnerar kort information om alla kanaler och inkluderar kanal-id, namn, url till kanalens webbsida samt url till ljudstream.

	{
		"channel_id": "132",
		"name": "P1",
		"audio_url": "http://sverigesradio.se/topsy/direkt/132.mp3",
		"channel_url": "http://sverigesradio.se/p1"
	}
	
## Användning
För att använda api:et inkludera följande: `require_once("api/classes/SRplaylist.php")`. SRplaylist behöver tillgång till en MySQL-databas, vilket innebär att databasuppgifter måste skickas med vid skapande av instans till klassen. Api:et skapar själv de tabeller som behövs. Kanaler hämtas om till databasen en gång per dygn och låtlistor cachas med APC i 30 sekunder.

	<?php 
	
		require_once("api/classes/SRplaylist.php");
		
		$api = new SRplaylist("localhost", "root", "root", "sr_playlist");
		
		$result = $api->getPlaylist("P3");
		
		$output = json_decode($result);
		$channel = $output->channelInfo[0];
		$playlist = $output->playlist[0];
				
		echo "På " . $channel->name . " spelas just nu "  . $playlist->currentSong->title . " av ". $playlist->currentSong->artist . ".";
	
	?>
	
## Felhantering
Om den parameter som skickas med inte finns kommer ett kort felmeddelande i formatet JSON returneras. För eventuella databasfel kastas dock undantag. Återigen, observera att ett anrop i nuläget kan returnera null-värden för spellistan.
	
	{
		"error": "The requested resource does not exist."
	}
	
## Om 
SRplaylist är skapat av [Therese Andersson](http://twitter.com/tess_andersson_) som en del i kursen Webbutveckling med PHP II på Linnéuniversitet.

