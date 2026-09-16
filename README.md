Wymagania

Przed rozpoczęciem instalacji należy zainstalować:

PHP 8.2,
Composer,
PostgreSQL,
Git,
Symfony CLI

W PHP należy mieć włączone następujące rozszerzenia:

-curl
-mbstring
-openssl
-fileinfo
-intl
-pdo_pgsql
-pgsql

Instalacja

Należy pobrać projekt z repozytorium:
git clone https://github.com/Gjorgix/timesheet_project.git
Następnie trzeba zainstalować zależności projektu:
composer install
Należy utworzyć w głównym katalogu projektu plik .env.local i skonfigurować połączenie z bazą danych:
DATABASE_URL="postgresql://postgres:HASLO@127.0.0.1:5432/timesheet_project?serverVersion=16&charset=utf8"

W miejscu HASLO należy wpisać hasło użytkownika PostgreSQL.

Należy utworzyć bazę danych:
php bin/console doctrine:database:create
Następnie trzeba wykonać migracje:
php bin/console doctrine:migrations:migrate
Należy załadować dane testowe:
php bin/console doctrine:fixtures:load
Aplikację należy uruchomić:
symfony server:start

Dane logowania

Administratorzy
Imię i nazwisko,	E-mail, 	Hasło
Anna Nowak, 	anna.nowak@example.com,  	AnnaNowak
Piotr Kowalski, 	piotr.kowalski@example.com, 	PiotrKowalski

Pracownicy
Imię i nazwisko,	E-mail, 	Hasło
Jan Kowalski,  	jan.kowalski@example.com,	JanKowalski
Maria Nowak,	maria.nowak@example.com,	MariaNowak
Tomasz Wiśniewski,	tomasz.wisniewski@example.com,	TomaszWiśniewski
