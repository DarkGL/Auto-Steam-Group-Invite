http://darkgl.pl/2012/10/21/automatyczne-zapraszanie-do-grupy-steam-auto-steam-invite/

Aby skrypt działał potrzebuje aktywnego ( czyli z kupionymi grami ) konta steam które musi być w grupie do której zaprasza
I niestety trzeba wyłączyć steam guard

Pierwsze co to ustawiamy dane w config.ini
dbHost   = "localhost";
dbUser   = "root";
dbPass   = "root";
dbDataBase  = "test";
oczywiście są tą dane sql

dalej widzicie nazwy tabel jakich używa skrypt nie ma potrzeby ich zmieniać 

Następnie uruchamiamy plik install.php ( który potem można usunąć )

Teraz dodajemy do crona uruchamianie skryptu invite.php co np. 10 min
Nie trzeba tego robić można też go uruchamiać samemu np. raz dziennie 

Skrypt generuje sobie listę graczy którzy byli zapraszani możemy ją wykorzystać poprzez skrypt spamInvite.php który pozwala zaprosić nam 200 osób w jednym "cyklu" ( 200 osób na 2h ponieważ takie są ograniczenia na steam dane od byczusia )
podajecie w get kolejno
login  - login do konta steam
pass  - hasło do konta steam
group - link do grupy
page - strona np. 1  to pierwsza 200 osób 2 to druga 200 osób i tak dalej
Skrypt generuje także logi kogo zaprosił tak abyście mogli to wykorzystać to w własnych skryptach
w sql jest to tabela z nazwą steaminv_logstable

Teraz plugin

Dane w pluginie możemy zakodować lub ustawiać je cvarami

wszystkim sterujemy za pomocą
#define HARDCODED

gdy makrodefinicja jest zadeklarowana to dane ustawiamy tutaj
new const szHost[]  = "HOST";
new const szUser[]  = "USER";
new const szPass[]  = "PASS";
new const szDb[]  = "DB";
new const accLogin[ ] = "login";
new const accPass[ ] = "pass";

gdy zakomentujemy to dane ustawiamy w cvarach

invite_sql_host host // host bazy danych
invite_sql_user user //user bazy danych
invite_sql_pass pass //haslo do usera
invite_sql_db db // db

invite_acc_login login // login do konta steam
invite_acc_pass pass // haslo do konta steam


ten cvar ustawiamy zawsze

invite_group_link link // link do grupy steam
