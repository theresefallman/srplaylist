SRplaylist
==========

## Introduktion
SRplaylist kan användas för att hämta en låtlista för de låtar som spelas just nu på någon av Sveriges Radios kanaler. Informationen hämtas från olika källor av [SR:s api](http://sverigesradio.se/api/documentation/v2) men selekterar sedan ut den information om kanaler och låtar som är mest intressant och relevant.

### Funktionalitet
SRplaylist kommer i dagsläget med två metoder, en för att hämta kanaler och en för att hämta spellista.
+ Hämta information om samtliga kanaler från Sveriges Radio
+ Hämta en spellista för specifik kanal

### Användning och metoder
#### getPlaylist()
Hämtar en spellista för en kanal som innehåller information om kanalen i sig samt titel och artist på låt som spelas just nu och för nästkommande låt.

__Tillåtna inparametrar__

Till getPlaylist() kan man skicka med ett namn på en kanal. Parametern måste vara en [giltig kanal](http://sverigesradio.se/sida/allakanaler.aspx) från Sveriges radio. Om en parameter inte skickas med används kanalen P3 som default.
