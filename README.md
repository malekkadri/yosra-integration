# SAFEProject – Authentication Module

## Project Description

SAFEProject is a web project developed as part of the **Web Technologies** course at **ESPRIT**.

The main objective of this project is to implement a **secure user authentication and management system**.

The project provides:

* User registration and login
* User role management
* Secure session handling
* Email notifications (registration and password reset)

---

## Table of Contents

* [Installation](#installation)
* [Usage](#usage)
* [Contribution](#contribution)
* [License](#license)

---

## Installation

1. Clone the repository:

```bash
git clone https://github.com/FatmaGhabbara/SAFEProject.git
```

2. Navigate to the project directory:

```bash
cd SAFEProject
```

3. Install a local server environment (example: **XAMPP**)

* Start Apache and MySQL
* Place the project inside the `htdocs` directory

4. Create the database:

* Open phpMyAdmin
* Create a database named `safespace`
* Import the provided SQL file

5. Configure the database connection inside the project

---

## Usage

### PHP Setup

The project is developed using **PHP** and follows the **MVC architecture**.

* Recommended PHP version: **PHP 8**
* Database: **MySQL using PDO**

### Accessing the Application

* Main page:

```
http://localhost/SAFEProject/
```

* Login page:

```
http://localhost/SAFEProject/view/frontoffice/login.php
```

### Main Functionalities

* Login using email and password
* Fingerprint authentication (WebAuthn)
* User roles:

  * Admin
  * Adviser
  * Member

---

## Contribution

Contributions are welcome.

To contribute:

1. Fork the repository
2. Create a new branch
3. Make your changes
4. Commit your changes
5. Open a Pull Request

---

## License

This project is developed for **academic purposes only**.

---

Developed by **Fatma EZZAHRA  Ghabbara**
ESPRIT – Web Technologies
