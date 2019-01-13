# Mutasi Bank
Class ini berfungsi untuk mengambil data mutasi rekening bank di Indonesia (Bank Mandiri)

## Installation
Install dengan composer:

	composer require ombapit/mutasi-bank

##Requirements
* PHP curl
* PHP openssl


## Example
fungsi kelas ini cuma 2 yaitu Login, dan mengambil tabel data transaksi berdasarkan range tanggal tertentu

### Login
ketika class ini di di buat, secara otomatis ia akan login ke mandiri melalui CURL
	
	use MutasiBank\Mandiri;
	$mb = new Mandiri('username', 'password');
	
### Mengambil Mutasi Rekening
mengambil mutasi rekening dapat menggunakan method `getMutasiRekening` dengan parameter range tanggal transaksi yang diinginkan `getMutasiRekening(dari, sampai)`. Contoh :
	
	$html = $mb->getMutasiRekening('2019-01-01','2019-01-10');

method ini me return element html `<table>` yang berisikan daftar transaksi

### Logout
diakhir pengambilan mutasi, secara otomatis ia akan logout


## Notes
-