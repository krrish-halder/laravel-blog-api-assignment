
# Laravel Blog Management API

This is a Blog Management System API built with Laravel 12. It includes features like user registration, login with Sanctum, blog creation/editing/deletion, liking/unliking blogs, searching, filtering, and pagination.

## 🛠️ Tech Stack

- Laravel 12
- Sanctum for API authentication
- MySQL
- Eloquent Factories & Seeders

---

## 🔐 Authentication APIs

### ✅ Register
POST **/api/register**

Request Body:
```
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "test1234",
}
```

### ✅ Login
POST **/api/login**

Request Body:
```
{
  "email": "john@example.com",
  "password": "test1234"
}
```

Response:
Returns authentication token.

### ✅ Logout
POST **/api/logout**

(Requires Bearer token)

---

## 📝 Blog APIs (Require Auth Token)

### ✅ Get All Blogs (with Pagination, Filter, Search)
GET **/api/blogs**

Query Params (optional):
- To sort by most liked
```
/blogs?sort=most_liked
```
- To sort by latest
```
/blogs?sort=latest 
```
- To search in title/content
```
/blogs?search=keyword
```

### ✅ Create Blog
POST **/api/blogs**

Form Data:
```
- title: string
- description: text
- image: file (image)
```
### ✅ Show Blog Details
GET **/api/blogs/{id}**

### ✅ Update Blog
PUT **/api/blogs/{id}**

Form Data:
```
- title: string
- description: text
- image: file (optional)
```
### ✅ Delete Blog
DELETE **/api/blogs/{id}**

---

## ❤️ Blog Like Toggle

### ✅ Toggle Like/Unlike Blog
POST **/api/blogs/{id}/toggle-like**

This will like the blog if not liked, and unlike if already liked.

---

## 🧪 Dummy Data via Seeder

- 20 users (all with password test1234)
- 30 blogs
- 100 random blog likes

Run:
```bash
php artisan migrate:fresh --seed
```
---

## 🔐 Sanctum Token

Include the token in every protected API call:
Authorization: Bearer {token}

---

## 📁 Project Structure

- app/Models - User, Blog, Like models
- app/Http/Controllers/API - Controllers for Auth, Blogs, Likes
- routes/api.php - All API routes
- database/seeders - Seeders for users, blogs, and likes
- database/factories - Factories for fake data

---

## 📦 Installation & Run

```bash
git clone https://github.com/krrish-halder/laravel-blog-api-assignment

cd laravel-blog-api
```

```bash
composer install
cp .env.example .env
php artisan key:generate
```
# Set DB credentials in .env

```
php artisan migrate --seed

php artisan serve
```
---

## ✅ Author
Krrish Halder

---

## 📜 License
This project is open-source and available under the MIT License.
