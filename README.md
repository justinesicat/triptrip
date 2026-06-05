# 🌍 TripTrip

A travel and tourism web application designed to help users explore destinations, discover featured tourist spots, and share travel experiences. TripTrip provides a user-friendly platform for browsing landmarks, destination details, and community reviews.

## 📌 Overview

TripTrip is a web-based tourism platform developed as an academic project. It allows users to explore travel destinations, create accounts, manage profiles, and interact with travel-related content.

The system aims to make travel discovery easier through featured destinations, searchable content, and user reviews.

## ✨ Features

### 👤 User Authentication
- User registration
- Login & logout system
- Account management

### 🌏 Travel Discovery
- Browse destinations
- Featured tourist attractions
- Landmark information
- Destination pages

### 🔎 Search Functionality
- Search for destinations and travel-related content

### ⭐ User Interaction
- User reviews and feedback system
- Contact page for inquiries

### 🛠 Dashboard / Management
- User dashboard
- Content management pages

## 📂 Project Structure

```txt
triptrip/
│── index.php                # Homepage
│── about.php                # About page
│── contact.php              # Contact page
│── destination.php          # Destination listings/details
│── featured.php             # Featured destinations
│── search.php               # Search functionality
│── userreviews.php          # User reviews
│── login.php                # Login page
│── register.php             # Registration page
│── account.php              # User account management
│── dashboard.php            # Dashboard page
│── manage.php               # Management functionality
│── auth.php                 # Authentication logic
│── logout.php               # Logout handler
│── config.php               # Database/configuration
│── css/                     # Stylesheets
│── js/                      # JavaScript files
│── images/                  # Website images & assets
│── font/                    # Custom fonts
```

## 🛠 Tech Stack

### Frontend
- HTML5
- CSS3
- JavaScript

### Backend
- PHP

### Database
- MySQL *(configured through `config.php`)*

## 🚀 Installation Guide

### 1. Clone the repository

```bash
git clone https://github.com/justinesicat/triptrip.git
```

### 2. Move project to local server

Place the project folder inside:

**XAMPP**
```txt
htdocs/
```

**WAMP**
```txt
www/
```

### 3. Configure the database

1. Open **phpMyAdmin**
2. Create a database
3. Import the SQL file *travel_db.sql* located in the sql folder
4. Update database credentials in:

```php
config.php
```

Example:

```php
// name of the host
$host = "localhost";  

// database name     
$dbname = "travel_db";  

// MySQL username 
$user = "root";   

// MySQL password         
$pass = "";     
```

### 4. Start Apache & MySQL

Open **XAMPP Control Panel** and start:

- Apache
- MySQL

### 5. Run the project

Open in browser:

```txt
http://localhost/triptrip
```

## 🎯 Purpose of the Project

This project was developed as an academic requirement to demonstrate knowledge in:

- Web Development
- PHP Programming
- Database Management
- User Authentication Systems
- Frontend Design

## 👨‍💻 Developers

**BSCS 3B – Group 4**

Developed by the project members as part of coursework requirements, look at the About section of the website.

## 📄 License

This project is intended for **educational purposes only**.

---

If you found this project interesting, feel free to ⭐ the repository.
