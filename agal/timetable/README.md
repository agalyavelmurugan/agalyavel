# CS Dept Timetable — PHP + MySQL Backend
## Annamalai University

---

## 📁 File Structure

```
project/
├── index.php               ← Main frontend (merge with 2.html)
├── .htaccess               ← Apache routing + CORS
├── config/
│   └── database.php        ← DB credentials
├── api/
│   ├── faculty.php         ← Faculty CRUD API
│   ├── subjects.php        ← Subjects CRUD API
│   ├── timetable.php       ← Timetable save/load API
│   └── dashboard.php       ← Dashboard stats API
└── database/
    └── setup.sql           ← Run once to create DB + tables + seed data
```

---

## ⚙️ Setup Steps

### 1. Create the Database
Open **phpMyAdmin** (or MySQL CLI) and run:
```sql
SOURCE /path/to/database/setup.sql;
```

### 2. Configure Database Credentials
Edit `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'annamalai_timetable');
```

### 3. Merge the Frontend
Open `index.php` and follow the two comments:
- Paste all CSS from `2.html` (lines 10–478) into the `<style>` tag
- Paste all HTML body content from `2.html` (lines 479–761) into the `<body>`
- Paste remaining JS functions from `2.html` (lines 882–1699) at the bottom of the `<script>` block

### 4. Deploy to Web Server
Copy the entire project folder to your Apache/XAMPP `htdocs` directory.

---

## 🌐 API Endpoints

| Method | URL | Description |
|--------|-----|-------------|
| GET    | `/api/faculty.php` | List all faculty |
| GET    | `/api/faculty.php?id=1` | Get single faculty |
| POST   | `/api/faculty.php` | Add faculty |
| PUT    | `/api/faculty.php?id=1` | Update faculty |
| DELETE | `/api/faculty.php?id=1` | Delete faculty |
| GET    | `/api/subjects.php` | List all subjects |
| GET    | `/api/subjects.php?prog=UG&sem=I&sem_type=Odd` | Filtered subjects |
| POST   | `/api/subjects.php` | Add subject |
| PUT    | `/api/subjects.php?id=5` | Update subject |
| DELETE | `/api/subjects.php?id=5` | Delete subject |
| GET    | `/api/timetable.php` | List all saved timetables |
| GET    | `/api/timetable.php?prog=UG&class=I+B.Sc&sem_type=Odd` | Load timetable |
| POST   | `/api/timetable.php` | Save timetable |
| DELETE | `/api/timetable.php?prog=UG&class=I+B.Sc&sem_type=Odd` | Delete timetable |
| GET    | `/api/dashboard.php` | Dashboard statistics |

---

## 🔧 Requirements
- PHP 7.4+ with MySQLi extension
- MySQL 5.7+ or MariaDB 10.3+
- Apache with `mod_rewrite` enabled
