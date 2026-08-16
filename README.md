# Vehicle Rental API

REST API aplikacija za upravljanje iznajmljivanjem vozila, izrađena u Laravel 11 okruženju.

Aplikacija omogućava upravljanje kategorijama i vozilima, registraciju i autentifikaciju korisnika, rezervaciju vozila, kontrolu pristupa na osnovu korisničkih uloga, filtriranje i paginaciju, kao i postavljanje fotografija vozila.

## Autori

- Ime i prezime: `Vukašin Sandić`
- Broj indeksa: `2023/0111`
- Ime i prezime: `Despot Mijajlović`
- Broj indeksa: `2023/0341`

## Korišćene tehnologije

- PHP
- Laravel 11
- Laravel Sanctum
- MySQL / MariaDB
- Eloquent ORM
- XAMPP
- Postman
- Git i GitHub

## Glavne funkcionalnosti

Aplikacija podržava:

- registraciju korisnika;
- prijavljivanje i odjavljivanje;
- autentifikaciju pomoću Sanctum tokena;
- tri korisničke uloge;
- CRUD operacije nad kategorijama;
- CRUD operacije nad vozilima;
- filtriranje, sortiranje i paginaciju vozila;
- kreiranje i pregled rezervacija;
- proveru preklapanja rezervacija;
- automatsko računanje ukupne cene;
- promenu statusa rezervacije;
- otkazivanje rezervacije;
- upload i brisanje fotografija vozila;
- validaciju zahteva;
- JSON odgovore i odgovarajuće HTTP statuse.

## Korisničke uloge

### Admin

Administrator može:

- pregledati sve podatke;
- dodavati, menjati i brisati kategorije;
- dodavati, menjati i brisati vozila;
- pregledati sve rezervacije;
- menjati status rezervacija;
- postavljati i brisati fotografije vozila.

### Employee

Zaposleni može:

- pregledati sve podatke;
- upravljati kategorijama i vozilima;
- pregledati sve rezervacije;
- menjati status rezervacija;
- postavljati i brisati fotografije vozila.

### Customer

Korisnik može:

- pregledati kategorije i vozila;
- filtrirati i sortirati vozila;
- kreirati rezervaciju;
- pregledati samo svoje rezervacije;
- otkazati svoju rezervaciju.

Customer ne može da upravlja kategorijama, vozilima ili statusima rezervacija.

## Modeli i veze

Aplikacija koristi sledeće modele:

- `User`
- `Category`
- `Vehicle`
- `Reservation`
- `VehicleImage`

Veze između modela:

```text
Category  1 ─── N  Vehicle
Vehicle   1 ─── N  Reservation
User      1 ─── N  Reservation
Vehicle   1 ─── N  VehicleImage
```

Jedna kategorija može imati više vozila.

Jedno vozilo pripada jednoj kategoriji i može imati više rezervacija i fotografija.

Jedan korisnik može imati više rezervacija.

## Instalacija projekta

### 1. Kloniranje repozitorijuma

```bash
git clone https://github.com/elab-development/serverske-veb-tehnologije-2025-26-vebaplikacijazaizvozila_2023_0111.git
```

Prelazak u folder projekta:

```bash
cd serverske-veb-tehnologije-2025-26-vebaplikacijazaizvozila_2023_0111
```

### 2. Instalacija PHP paketa

```bash
composer install
```

### 3. Kreiranje `.env` fajla

Na Windows sistemu:

```powershell
copy .env.example .env
```

Na Linux ili macOS sistemu:

```bash
cp .env.example .env
```

### 4. Generisanje aplikacionog ključa

```bash
php artisan key:generate
```

### 5. Podešavanje baze

U `.env` fajlu podesiti:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vehicle_rental_api
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
```

Pre pokretanja migracija potrebno je kreirati bazu:

```text
vehicle_rental_api
```

### 6. Pokretanje migracija i seedera

```bash
php artisan migrate --seed
```

### 7. Kreiranje javnog linka za fotografije

```bash
php artisan storage:link
```

### 8. Pokretanje aplikacije

```bash
php artisan serve
```

API će biti dostupan na adresi:

```text
http://127.0.0.1:8000/api
```

## Demo korisnici

Seeder kreira sledeće testne naloge:

| Uloga | Email | Lozinka |
|---|---|---|
| Admin | `admin@example.com` | `password123` |
| Employee | `employee@example.com` | `password123` |
| Customer | `customer@example.com` | `password123` |

Nalozi služe isključivo za demonstraciju i lokalno testiranje aplikacije.

## Autentifikacija

Aplikacija koristi Laravel Sanctum Bearer tokene.

Nakon uspešnog prijavljivanja korisnik dobija:

```json
{
    "access_token": "GENERISANI_TOKEN",
    "token_type": "Bearer"
}
```

Token se kod zaštićenih zahteva šalje kroz zaglavlje:

```text
Authorization: Bearer GENERISANI_TOKEN
```

Tokeni se ne čuvaju u repozitorijumu ili dokumentaciji.

## API rute

### Autentifikacija

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| POST | `/api/register` | Registracija korisnika | Javno |
| POST | `/api/login` | Prijavljivanje | Javno |
| GET | `/api/profile` | Podaci prijavljenog korisnika | Autentifikovan korisnik |
| POST | `/api/logout` | Odjavljivanje | Autentifikovan korisnik |

### Kategorije

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| GET | `/api/categories` | Pregled kategorija | Svi prijavljeni |
| GET | `/api/categories/{id}` | Pregled jedne kategorije | Svi prijavljeni |
| POST | `/api/categories` | Dodavanje kategorije | Admin, Employee |
| PATCH | `/api/categories/{id}` | Izmena kategorije | Admin, Employee |
| DELETE | `/api/categories/{id}` | Brisanje kategorije | Admin, Employee |

Kategorija koja sadrži vozila ne može biti obrisana.

### Vozila

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| GET | `/api/vehicles` | Pregled vozila | Svi prijavljeni |
| GET | `/api/vehicles/{id}` | Pregled jednog vozila | Svi prijavljeni |
| POST | `/api/vehicles` | Dodavanje vozila | Admin, Employee |
| PATCH | `/api/vehicles/{id}` | Izmena vozila | Admin, Employee |
| DELETE | `/api/vehicles/{id}` | Brisanje vozila | Admin, Employee |

Vozilo koje ima rezervacije ne može biti obrisano.

### Rezervacije

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| GET | `/api/reservations` | Pregled rezervacija | Svi prijavljeni |
| POST | `/api/reservations` | Kreiranje rezervacije | Svi prijavljeni |
| GET | `/api/reservations/{id}` | Pregled rezervacije | Svi prijavljeni |
| POST | `/api/reservations/{id}/cancel` | Otkazivanje rezervacije | Vlasnik, Admin, Employee |
| PATCH | `/api/reservations/{id}/status` | Promena statusa | Admin, Employee |

Customer vidi samo rezervacije koje pripadaju njegovom nalogu.

### Fotografije vozila

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| POST | `/api/vehicles/{vehicle}/images` | Upload fotografije | Admin, Employee |
| DELETE | `/api/vehicle-images/{id}` | Brisanje fotografije | Admin, Employee |

Upload se šalje kao `multipart/form-data`.

Dozvoljeni formati:

- JPG
- JPEG
- PNG
- WEBP

Maksimalna veličina fajla je 5 MB.

## Filtriranje i paginacija

Ruta:

```text
GET /api/vehicles
```

podržava sledeće parametre:

| Parametar | Opis |
|---|---|
| `brand` | Filtriranje po marki |
| `model` | Filtriranje po modelu |
| `category_id` | Filtriranje po kategoriji |
| `status` | Filtriranje po statusu |
| `transmission` | Filtriranje po menjaču |
| `fuel_type` | Filtriranje po gorivu |
| `min_price` | Minimalna dnevna cena |
| `max_price` | Maksimalna dnevna cena |
| `sort_by` | Polje za sortiranje |
| `sort_direction` | `asc` ili `desc` |
| `per_page` | Broj rezultata po stranici |
| `page` | Broj stranice |

Primer:

```text
GET /api/vehicles?brand=Toyota&status=available&min_price=40&max_price=70
```

Primer sortiranja:

```text
GET /api/vehicles?sort_by=daily_price&sort_direction=asc
```

Primer paginacije:

```text
GET /api/vehicles?per_page=5&page=1
```

## Rezervacije i dostupnost

Prilikom kreiranja rezervacije korisnik prosleđuje:

```json
{
    "vehicle_id": 1,
    "start_date": "2026-08-20",
    "end_date": "2026-08-22"
}
```

Aplikacija automatski:

- proverava da li je vozilo dostupno;
- proverava preklapanje sa postojećim rezervacijama;
- računa broj dana;
- izračunava ukupnu cenu;
- postavlja početni status `pending`.

Aktivne rezervacije sa statusom `pending` ili `approved` blokiraju zauzeti period.

Rezervacije sa statusom `cancelled` ili `completed` ne blokiraju novi termin.

Dozvoljene promene statusa:

```text
pending → approved
pending → cancelled
approved → completed
approved → cancelled
```

## HTTP statusi

Aplikacija koristi sledeće najvažnije HTTP statuse:

| Status | Značenje |
|---|---|
| `200 OK` | Zahtev uspešno izvršen |
| `201 Created` | Resurs uspešno kreiran |
| `401 Unauthorized` | Korisnik nije prijavljen |
| `403 Forbidden` | Korisnik nema odgovarajuću ulogu |
| `409 Conflict` | Konflikt podataka ili rezervacija |
| `422 Unprocessable Content` | Greška validacije |
| `404 Not Found` | Resurs nije pronađen |
| `500 Internal Server Error` | Serverska greška |

## Migracije

Migracijama su implementirani:

- kreiranje tabela;
- primarni ključevi;
- strani ključevi;
- jedinstvena ograničenja;
- indeksi;
- podrazumevane vrednosti;
- nullable kolone;
- dodavanje novih kolona u postojeće tabele;
- uklanjanje kolona prilikom rollback operacije.

## Testiranje

API je testiran pomoću Postmana.

Provereni su:

- registracija i login;
- pristup zaštićenim rutama;
- kontrola korisničkih uloga;
- CRUD operacije;
- validacija neispravnih zahteva;
- paginacija i filtriranje;
- sprečavanje preklapanja rezervacija;
- promena i otkazivanje statusa;
- upload fotografija;
- ograničenja prilikom brisanja povezanih podataka.

## Postman dokumentacija

Snimci Postman zahteva biće priloženi u dokumentaciji projekta.

Snimci prikazuju:

- HTTP metodu i URL;
- poslate podatke;
- Bearer autentifikaciju;
- JSON odgovor;
- HTTP status;
- datum testiranja.

## Pokretanje testova

```bash
php artisan test
```

## Git istorija

Projekat je razvijan kroz više smislenih commitova koji predstavljaju pojedinačne funkcionalne celine:

- inicijalizacija Laravel projekta;
- podešavanje Sanctum autentifikacije;
- povezivanje sa bazom;
- modeli i migracije;
- korisničke uloge;
- autentifikacija;
- CRUD kategorija;
- CRUD vozila;
- rezervacije;
- upload fotografija;
- seederi;
- dokumentacija.

---

## Dodatne funkcionalnosti

Pored prethodno navedenih funkcionalnosti, u aplikaciju su dodate i sledeće mogućnosti:

- javni pregled kategorija i vozila bez prijavljivanja;
- izmena sopstvene rezervacije dok je u statusu `pending`;
- pregled istorije rezervacija korisnika;
- pregled rezervacija konkretnog vozila;
- administratorska statistika rezervacija;
- integracija sa NHTSA VPIC servisom za pregled modela vozila prema marki;
- integracija sa Frankfurter servisom za pregled kursa valuta;
- dodatne provere prilikom izmene rezervacije korišćenjem transakcije i `lockForUpdate`;
- feature testovi za javne REST servise.

### Dodatne API rute

| Metoda | Ruta | Opis | Pristup |
|---|---|---|---|
| GET | `/api/categories` | Pregled kategorija | Javno |
| GET | `/api/categories/{id}` | Pregled jedne kategorije | Javno |
| GET | `/api/vehicles` | Pregled i filtriranje vozila | Javno |
| GET | `/api/vehicles/{id}` | Pregled jednog vozila | Javno |
| PUT | `/api/reservations/{reservation}` | Izmena rezervacije | Autentifikovan korisnik |
| GET | `/api/users/{user}/reservations` | Istorija rezervacija korisnika | Autentifikovan korisnik |
| GET | `/api/vehicles/{vehicle}/reservations` | Rezervacije konkretnog vozila | Autentifikovan korisnik |
| GET | `/api/admin/statistics/rentals` | Statistika rezervacija | Admin |
| GET | `/api/external/vehicles/models/{make}` | Modeli vozila prema marki | Javno |
| GET | `/api/external/exchange/{from}/{to}` | Kurs između dve valute | Javno |

### Javni REST servisi

Za pribavljanje dodatnih podataka koriste se dva javna REST servisa.

NHTSA VPIC servis koristi se za pregled modela vozila prema prosleđenoj marki:

```text
GET /api/external/vehicles/models/BMW
```

Frankfurter servis koristi se za pregled kursa između dve valute:

```text
GET /api/external/exchange/EUR/USD
```

Za ove rute nije potrebna autentifikacija.

### Istorija i izmena rezervacija

Prijavljeni korisnik može da izmeni rezervaciju preko rute:

```text
PUT /api/reservations/{reservation}
```

Customer može da menja samo svoju rezervaciju i to dok je rezervacija u statusu `pending`. Prilikom izmene ponovo se proveravaju dostupnost vozila i preklapanje termina, a ukupna cena se ponovo računa na serverskoj strani.

Istorija rezervacija dostupna je preko rute:

```text
GET /api/users/{user}/reservations
```

Customer može da pregleda samo svoju istoriju rezervacija.

### Administratorska statistika

Administrator može da pristupi statistici preko rute:

```text
GET /api/admin/statistics/rentals
```

Statistika obuhvata ukupan broj rezervacija, broj završenih iznajmljivanja, ukupan prihod, broj rezervacija po statusu i najčešće iznajmljivana vozila.

### Dodatno testiranje

Za javne REST servise dodati su Laravel feature testovi korišćenjem `Http::fake`, tako da testovi ne zavise od trenutne dostupnosti eksternih servisa.

Testovi se pokreću postojećom komandom:

```bash
php artisan test
```

Testirane su rute za NHTSA VPIC i Frankfurter servis, uključujući proveru HTTP statusa i JSON odgovora.