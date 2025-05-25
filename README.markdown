# Sinema Yönetim Sistemi / Cinema Management System

Bu proje, bir sinema yönetim sistemi için geliştirilmiştir. Aşağıda, projeyi yerel ortamınızda çalıştırmak için gerekli adımlar Türkçe ve İngilizce olarak listelenmiştir.  
This project is developed for a cinema management system. Below are the steps required to set up and run the project in your local environment, provided in both Turkish and English.

---

## Türkçe Kurulum Adımları

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

### Notlar
- Veritabanı bağlantı ayarlarını doğru şekilde yapmazsanız, proje çalışmayabilir.
- Herhangi bir sorunla karşılaşırsanız, lütfen issue açarak bildirin.

---

## English Installation Steps

### 1. Database Setup
- Use the `cinema_db.sql` file located in the project root directory.
- Open **phpMyAdmin** and create a new database.
- Import the `cinema_db.sql` file into the created database:
  1. Select your database in phpMyAdmin.
  2. Click on the "Import" tab.
  3. Choose the `cinema_db.sql` file and start the import process.

### 2. Database Connection Settings
- Edit the following files located in the `config` folder in the project root directory:
  - `db_connect.php`
  - `database.php`
- In both files, fill in the following fields with your database credentials:
  - **Username**: Enter your database username.
  - **Password**: Enter your database password.
- Example structure (may vary depending on the file content):
  ```php
  // db_connect.php example
  $username = "your_username";
  $password = "your_password";
  ```

### 3. Running the Project
- Place the project files in a web server environment (e.g., XAMPP, WAMP, or MAMP).
- Run the project in your web browser to test it.

### Notes
- The project may not work if the database connection settings are not configured correctly.
- If you encounter any issues, please open an issue to report them.