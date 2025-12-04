# medicine management
I have noticed that since the COVID-19 pandemic, many families or individuals have chosen to stockpile some medicines at home for emergencies. However, people often forget the expiration dates of medicines. This project is mainly used to manage and check the expiration dates of medicines so that expired medicines can be updated and disposed of in a timely manner.  


## 📚 Table of Contents
- [Install](#-Install)
- [Limitation](#-limitation)
- [License](#-License)
- [Contributors](#-contributors)

---
## 📜 Install
Windows + XAMPP  
1. Copy the [medicine](./medicine) folder into your `/XAMPP/htdocs`  
2. Create the database table in MYSQL required by `medicine_system.sql`  
   The database initially contained only one user's data; the other tables were empty.  
3. Visit `http://localhost/medicine/auth/login.php`  
   The default username is `admin`  
   The default password is `123456`  
4. Configure database connection information at `/XAMPP/htdocs/medicine/config/db.php`  
   The default HOST is `localhost`  
   The default USER is `root`  
   The default PASS is (none)  
   The default NAME is `medicine_system`  



---
## 📜 License
This project is licensed under the GNU General Public License v3.0 (GPL-3.0).
This means any modified versions or derivative works must also be distributed under the same license.

See the [LICENSE](LICENSE) file for full details.


---
## 📜 Contributors
This section will not display correctly if the project is in a private setting.  
<a href="https://github.com/lqx1243/medicine-management/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=lqx1243/medicine-management" />
</a>

Made with [contrib.rocks](https://contrib.rocks).
