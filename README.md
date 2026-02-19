<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" />
</p>

<h1 align="center">Service Marketplace Platform</h1>

<p align="center">
Laravel API • Flutter iOS Apps • Next.js Dashboard • Realtime Messaging
</p>

<p align="center">
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-10-red"></a>
<a href="#"><img src="https://img.shields.io/badge/Flutter-iOS-blue"></a>
<a href="#"><img src="https://img.shields.io/badge/Next.js-Dashboard-black"></a>
<a href="#"><img src="https://img.shields.io/badge/Pusher-Realtime-purple"></a>
<a href="#"><img src="https://img.shields.io/badge/MySQL-Database-orange"></a>
</p>

---

## 🚀 About Project

This project is a **service marketplace platform** built with Laravel, Flutter, and Next.js.

It includes:

- 📱 Flutter iOS App (User)
- 📱 Flutter iOS App (Provider)
- 🧠 Laravel API (Backend)
- 🖥 Next.js Dashboard (Admin & Owner)

The system supports:

- User ↔ Provider chat  
- Job management  
- Provider verification  
- Real-time notifications  
- Role-based access (User / Provider / Admin / Owner)  
- Live status updates  

---

## 📐 System Architecture Diagram

View the full system flow (Flutter → Laravel → Pusher → Next.js):

👉 Draw.io Diagram: https://drive.google.com/file/d/1VxwrHLqg1kbH7ggktNXUR8aYQBSavt8a/view?pli=1

Open and edit using:

https://app.diagrams.net  
(File → Open From → Google Drive / URL)

---

## 🧱 Tech Stack

### Backend
- Laravel
- MySQL
- Pusher Channels

### Frontend
- Flutter (User & Provider iOS apps)
- Next.js (Admin / Owner dashboard)

---

## 👥 Roles

| Role | Description |
|------|------------|
| user | Service customer |
| provider | Technician / freelancer |
| admin | System administrator |
| owner | Business owner |

Roles are stored in `users.role`.

---
### 👤 User Module
 - The User Module provides full user management capabilities with scalable architecture and reusable logic. It supports user lifecycle operations, filtering, bulk actions, and real-time UI updates.
## ✨ Features
- Paginated user listing
- Search users by name or email
- Filter users by role (e.g. customer)
- Filter users by active / inactive status
- View single user details
- Create new users
- Update user profile information
- Upload and update user avatar
- Toggle user active status
- Update user status
- Bulk update user active status
- Bulk delete users
- Delete single users
- updated_at
Reusable table controller for user actions