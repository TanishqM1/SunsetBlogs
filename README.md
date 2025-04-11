# Sunset Blogs

A dynamic blog platform where users can create, share, and engage with content in a vibrant community of writers and readers.

## Project Overview

Sunset Blogs is a full-featured blogging platform that allows users to:
- Create and manage personal accounts with profile images
- Write and publish blogs with rich content
- Discover content from other users
- Like and comment on posts
- Follow other users
- Contact site administrators
- Search for users and content

The platform includes both regular user and administrative functionality with real-time updates for comments, posts, and likes.

## Technologies Used

### Frontend
- HTML5
- CSS3 with responsive design
- JavaScript (ES6+)
- Asynchronous updates using AJAX/Fetch API

### Backend
- PHP 7+
- MySQL Database
- PDO for database interactions
- Session management for authentication

### Security Features
- Client-side form validation
- Password hashing and security
- Prepared SQL statements
- Input sanitization
- Session protection

## Detailed Website Features

### Authentication
- User signup with form validation and mandatory profile picture upload
- Password requirements include minimum 8 characters, letters and digits
- Profile images are stored as filepaths in the database
- User login with secure session creation
- Guest login (limited to viewing blogs, other actions prompt sign-in)
- Logout functionality

### User Profile & Blog Posts
- Profile page displays user information retrieved from database
- Profile editing (username, email, password, and profile picture)
- Create blog posts with metadata (title, subtitles, media links, images, tags, categories)
- View and interact with individual blog posts through comments/likes

### Home Page
- Filter posts by recency, popularity, discussion, category, or user-specific content
- Dynamic post fetching using AJAX without page reload
- Posts displayed with thumbnails and images when available
- Real-time search functionality to filter posts by metadata

### Admin Features
- Delete users or posts
- Edit blogs created by any user
- View site statistics and metrics over time (new users, posts, and various averages)
- Search for users by username, email, or post content

### Contact System
- Both users and guests can submit inquiries
- Messages are stored in the database for developer access

## Key Features

### User Features
- Account creation and management
- Profile customization with image uploads
- Blog creation and editing
- Comment and like functionality
- User following system
- Content discovery based on categories and tags
- Real-time updates without page reloads

### Admin Features
- User management (search, view, delete)
- Content moderation
- Site statistics and analytics
- Usage reports with filtering options

## File Structure Breakdown

### Signup System
- **signup.html**: User-facing form for registration
- **signup.js**: Handles client-side validation for registration
- **signup-validation.js**: Performs detailed validation of form fields
- **signup.php**: Processes registration, hashes passwords, uploads images

### Login System
- **login.html**: Frontend layout for user login
- **login.js**: Handles credential submission and displays errors
- **login.php**: Processes credentials and creates user sessions
- **set_guest_session.php**: Creates limited guest sessions
- **logout.php**: Destroys active sessions

### Homepage System
- **home.html**: Dashboard for logged-in users with filtering options
- **home_guest.html**: Limited view for guests
- **home.php**: Session validation and request routing
- **fetch_posts.php**: Creates SQL queries for filtered content
- **fetch_new_posts.php**: Real-time post updates

### User Dashboard
- **your-work.php**: Displays user's created and liked content

### Profile System
- **profile.php**: Displays user information and admin dashboard
- **update_profile.php**: Handles profile modifications
- **profile_script.js**: Client-side profile validation

### Blog Systems
- **view-blog.php**: Displays full blog content with comments
- **create-blog.html**: Interface for blog creation
- **create_post.php**: Processes new blog posts
- **blog-validation.js**: Validates blog content
- **like_post.php**: Handles post liking functionality
- **add_comment.php**: Processes comment submissions

### Admin System
- **admin_actions.php**: Backend support for admin tasks
- **delete_post.php**: Removes posts from database
- **delete_user.php**: Removes users and associated data
- **admin_functions.php**: Helper functions for admin operations

### Auxiliary Files
- **auth_check.php**: Session verification for protected routes
- **database.php**: Database connection management
- **contact.html/contact_guest.html**: Contact form interfaces
- **contact.js**: Validates contact submissions
- **contact.php**: Processes contact form data

## Database Structure

The system uses a MySQL database with the following main tables:
- `users`: Stores user account information
- `posts`: Contains all blog post content
- `comments`: Stores user comments on posts
- `likes`: Tracks post likes by users
- `followers`: Manages user following relationships
- `inquiry`: Stores user contact form submissions

## Installation and Setup

1. Clone the repository to your web server directory
2. Import the SQL schema from the `SQL/tables.txt` file
3. Configure the database connection in `config/database.php`
4. Ensure your web server has PHP 7+ and MySQL installed
5. Set appropriate read/write permissions for the `uploads` directory

## System Architecture

The system follows a structured architecture:
- `config/`: Contains database connection and core functions
- `CSS/`: Stylesheets for the application
- `JavaScript/`: Client-side validation and asynchronous functionality
- `Pages/`: PHP and HTML templates for different sections
- `SQL/`: Database schema and queries
- `uploads/`: Directory for user uploaded images

## Security Implementation

### Client-side Security
- Input validation for all forms
- Password strength requirements
- XSS prevention through input sanitization

### Server-side Security
- Prepared statements for all database queries
- Password hashing for user credentials
- Session management with secure cookies
- Protection against SQL injection

## Asynchronous Functionality

The platform implements real-time updates for:
- New posts appearing on the home feed
- Comments on blog posts
- Like counts and status
- User search results

These updates occur without requiring page reloads, enhancing the user experience.

## Testing Credentials

For testing purposes, two pre-created accounts are available:
- **Admin Account**: 
 -Contact Admin for info
  
- **Sample User Account**: 
  - Email: raad.sask@gmail.com 
  - Password: Raad7223

## Live Demo

The live version of this project is available at: [https://cosc360.ok.ubc.ca/kmercha1/SunsetBlogs/](https://cosc360.ok.ubc.ca/kmercha1/SunsetBlogs/)




