## Wymagania

Przed rozpoczęciem instalacji należy zainstalować:

* PHP 8.2 
* Composer
* PostgreSQL
* Git
* Symfony CLI

W PHP należy mieć włączone następujące rozszerzenia:

* `curl`
* `mbstring`
* `openssl`
* `fileinfo`
* `intl`
* `pdo_pgsql`
* `pgsql`

## Instalacja

### 1. Pobranie projektu

Należy pobrać projekt z repozytorium:

```bash
git clone https://github.com/TWOJ_LOGIN/timesheet_project.git
cd timesheet_project
```

### 2. Instalacja zależności

```bash
composer install
```

### 3. Konfiguracja bazy danych

Należy utworzyć w głównym katalogu projektu plik `.env.local`.

W pliku należy skonfigurować połączenie z bazą danych:

```dotenv
DATABASE_URL="postgresql://postgres:HASLO@127.0.0.1:5432/timesheet_project?serverVersion=16&charset=utf8"
```

W miejscu `HASLO` należy podać hasło użytkownika PostgreSQL.

### 4. Utworzenie bazy danych

```bash
php bin/console doctrine:database:create
```

### 5. Wykonanie migracji

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Załadowanie danych testowych

```bash
php bin/console doctrine:fixtures:load
```

### 7. Uruchomienie aplikacji

```bash
symfony server:start
```

Następnie należy otworzyć w przeglądarce adres wyświetlony przez Symfony CLI.

## Dane logowania

### Administratorzy

| Imię i nazwisko | E-mail                                                         | Hasło         |
| --------------- | -------------------------------------------------------------- | ------------- |
| Anna Nowak      | anna.nowak@example.com| AnnaNowak     |
| Piotr Kowalski  | piotr.kowalski@example.com | PiotrKowalski |

### Pracownicy

| Imię i nazwisko   | E-mail                                                             | Hasło            |
| ----------------- | ------------------------------------------------------------------ | ---------------- |
| Jan Kowalski      | jan.kowalski@example.com| JanKowalski      |
| Maria Nowak       | maria.nowak@example.com| MariaNowak       |
| Tomasz Wiśniewski | tomasz.wisniewski@example.com| TomaszWiśniewski |
