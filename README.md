# Aplikasi Manajemen Pengguna SSO

## Aplikasi apakah ini?

Aplikasi ini merupakan sebuah sistem yang digunakan untuk melakukan manajemen pengguna pada server LDAP. Aplikasi ini dibangun menggunakan bahasa pemrograman PHP dan framework [Codeigniter 4](http://codeigniter.com). Aplikasi ini tidak dapat berdiri sendiri karena membutuhkan sebuah server LDAP yang nanti terhubung untuk dilakukan manajemen.

## Kebutuhan Server Hosting

- NGINX/Apache
- PHP 7.2
- Composer
- Postgresql 12
- Ldap Server
- git

## Instalasi Aplikasi

Lakukan clone aplikasi dari repository ke server hosting yang sudah memenuhi kriteria kebutuhan sistem.

```bash
git clone https://github.com/harunnoviar/akunstmikelrahma.git public_html
```

Masuk ke direktori `public_html` dan install composer

```bash
cd public_html
composer install
```

Buat file `.env` yang berisikan konfigurasi dari aplikasi dan sesuaikan dengan servernya misalkan domain atau koneksi database

```bash
# CI_ENVIRONMENT = development
app.baseURL = 'https://akun.stmikelrahma.ac.id/' # Silakan ganti domain

### Koneksi database, ganti dan sesuaikan
database.default.DSN = 'pgsql:host=dbhost;port=5432;dbname=dbname;user=dbuser;password=dbpassword'
database.default.hostname = dbhost
database.default.database = dbname
database.default.username = dbuser
database.default.password = dbpassword
database.default.DBDriver = Postgre

#--------------------
## LDAP SERVER, ganti dan sesuaikan dengan koneksi LDAP
#--------------------
ldap.stmikelrahma.host = '192.168.xxx.xxx'
ldap.stmikelrahma.port = '389'
ldap.stmikelrahma.proto = 'ldap://'
ldap.stmikelrahma.user = "cn=admin,dc=stmikelrahma,dc=ac,dc=id"
ldap.stmikelrahma.pass = 'gantiPasswordLDAP'
ldap.stmikelrahma.usetls = TRUE
ldap.stmikelrahma.searchbase = 'dc=stmikelrahma,dc=ac,dc=id'

#--------------------
# Email, sesuaikan email dengan SMTP yang akan digunakan
#--------------------
email.fromEmail = 'no-reply.akun@stmikelrahma.ac.id'
email.fromName = 'Admin Password Email STMIK EL RAHMA'
email.protocol = 'smtp'
email.SMTPHost = 'smtp-relay.gmail.com'
email.SMTPUser = 'exampleemail@stmikelrahma.ac.id'
email.SMTPPass = 'gantiPassEmailApp'
email.SMTPPort = '587'

##Myconfig
myconfig.google.redirect_url = 'https://akun.stmikelrahma.ac.id/register/gauth'
myconfig.google.client_id = 'gantiDenganGoogleClientId'
myconfig.google.client_secret = 'GantiDenganGoogleClientSecret'
myconfig.googleRecaptchaSiteKey= 'gantiGoogleRecaptchaSiteKey'
myconfig.googleRecaptchaSecretKey= 'gantiGoogleRecaptchaSecretKey'
```

Lakukan Import database dari file `default_database.sql` yang ada di dalam public_html.

```bash
psql -Udbuser -hdbhost dbname < default_database.sql
```

Lakukan testing masuk ke aplikasi halaman admin dengan mengakses https://domainanda.com/admin `default username: admin` dan `password: Elrahma123.`
