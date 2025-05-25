# Sinema Yönetim Sistemi

Bu proje, bir sinema yönetim sistemi için geliştirilmiştir. Aşağıda, projeyi yerel ortamınızda çalıştırmak için gerekli adımları bulabilirsiniz.

## Kurulum Adımları

### 1. Veritabanı Kurulumu
- Proje kök dizininde bulunan `cinema_db.sql` dosyasını kullanın.
- **phpMyAdmin**'i açın ve veritabanınızı oluşturun.
- Oluşturduğunuz veritabanına `cinema_db.sql` dosyasını içe aktarın:
  1. phpMyAdmin'de veritabanınızı seçin.
  2. "İçe Aktar" (Import) sekmesine tıklayın.
  3. `cinema_db.sql` dosyasını seçin ve içe aktarma işlemini başlatın.

### 2. Veritabanı Bağlantı Ayarları
- Proje kök dizinindeki `config` klasöründe bulunan aşağıdaki dosyaları düzenleyin:
  - `db_connect.php`
  - `database.php`
- Her iki dosyada, veritabanı bağlantısı için aşağıdaki alanları kendi veritabanı bilgilerinizle doldurun:
  - **Kullanıcı Adı (username)**: Veritabanı kullanıcı adınızı girin.
  - **Parola (password)**: Veritabanı parolanızı girin.
- Örnek yapı (dosyaların içeriğine bağlı olarak değişebilir):
  ```php
  // db_connect.php örneği
  $username = "kullanici_adiniz";
  $password = "parolaniz";
  ```

### 3. Proje Çalıştırma
- Proje dosyalarını bir web sunucusuna (örneğin, XAMPP, WAMP veya MAMP) yerleştirin.
- Web tarayıcınızda projeyi çalıştırarak test edin.

## Notlar
- Veritabanı bağlantı ayarlarını doğru şekilde yapmazsanız, proje çalışmayabilir.
- Herhangi bir sorunla karşılaşırsanız, lütfen issue açarak bildirin.