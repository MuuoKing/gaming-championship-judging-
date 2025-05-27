# Gaming Championship Judging System

A comprehensive web-based judging system for gaming tournaments that allows administrators to manage judges and participants, judges to score participants, and provides real-time scoreboard viewing with live updates.

## 🎮 Live Demo

**🌐 Demo URL**: https://gaming-championship-judging-production.up.railway.app

### Demo Credentials

**Admin Access:**
- Username: `admin`
- Password: `password`
- URL: [Admin Panel](https://gaming-championship-judging-production.up.railway.app/admin/login.php)

**Judge Access:**
- Username: `judge1`
- Password: `12345678`
- Username: `judge2`, `judge3`, `judge4`, or `judge5`
- Password: `P%ssw2rd` (will prompt to change on first login)
- URL: [Judge Portal](https://gaming-championship-judging-production.up.railway.app/judges/login.php)

**Live Scoreboard:**
- URL: [Scoreboard](https://gaming-championship-judging-production.up.railway.app/scoreboard.php)

> **Note**: Demo includes sample data with 10 participants and 5 judges. Data resets periodically.

## 🚀 Features

### Core Functionality
- **Admin Panel**: Complete tournament management system
  - Add/manage judges with secure authentication
  - Add/manage participants by category
  - Clear all scores with confirmation modal
  - Real-time participant statistics

- **Judge Portal**: Intuitive scoring interface
  - Secure login with password change enforcement
  - Search and filter participants
  - Slider-based scoring (1-100 points)
  - Real-time score submission with AJAX
  - Visual feedback and animations

- **Live Scoreboard**: Dynamic ranking display
  - Real-time updates without page refresh
  - Detailed judge score breakdowns
  - Medal system (Gold, Silver, Bronze)
  - Progress bars and visual indicators
  - Responsive design for all devices

### Technical Features
- **Security**: CSRF protection, password hashing, session management
- **Real-time Updates**: AJAX-powered live scoring and scoreboard updates
- **Responsive Design**: Mobile-first CSS Grid and Flexbox layout
- **Database Flexibility**: Supports both MySQL (local) and PostgreSQL (cloud)
- **Audit Trail**: Complete database change tracking with triggers
- **Error Handling**: Comprehensive error management and user feedback

## 🛠 Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+ 
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with CSS Grid, Flexbox and CSS Variables
- **Icons**: Font Awesome 6.4.0
- **AJAX**: Vanilla JavaScript with Fetch API
- **Deployment**: Railway (MySQL

## 📋 Prerequisites

- PHP 7.4 or higher with PDO extension
- MySQL 5.7+ 
- Web server (Apache/Nginx) or PHP built-in server
- Modern web browser with JavaScript enabled
- Git (for cloning the repository)

## 🔧 Local Setup Instructions

### 1. Clone the Repository

```bash
# Clone the repository
git clone https://github.com/MuuoKing/gaming-championship-judging-.git
cd gaming-championship-judging-
```

### 2. Database Setup

**Option A: Automatic Setup (Recommended)**
1. Create a MySQL database named `judging_system`
2. Update database credentials in `includes/dbConnection.php` and `setup.php`:
   ```php
   $host = 'localhost';
   $dbname = 'judging_system';
   $username = 'your_username';
   $password = 'your_password';
   ```
3. Visit `http://localhost/setup.php` in your browser
4. Tables and sample data will be created automatically

**Option B: Manual Setup**
1. Create database and run the SQL statements from the Database Schema section below
2. Update `includes/dbConnection.php` with your credentials

### 3. Start Development Server

```bash
# Using PHP built-in server
php -S localhost:8000

# Or place files in your web server directory
# Example: /var/www/html/ or C:\xampp\htdocs\
```

### 4. Access the Application

- **Homepage**: `http://localhost:8000/`
- **Admin Panel**: `http://localhost:8000/admin/login.php`
- **Judge Portal**: `http://localhost:8000/judges/login.php`
- **Scoreboard**: `http://localhost:8000/scoreboard.php`

### 5. Default Credentials

**Admin:**
- Username: `admin`
- Password: `password`

**Judges:**
- Usernames: `judge1`, `judge2`, `judge3`, `judge4`, `judge5`
- Default Password: `P%ssw2rd` (system will prompt to change)

## 🗄 Database Schema

### Core Tables

```sql
-- Admins table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_username VARCHAR(50) NOT NULL UNIQUE,
    admin_password VARCHAR(255) NOT NULL,
    admin_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_username (admin_username)
);

-- Judges table
CREATE TABLE judges (
    judge_id INT AUTO_INCREMENT PRIMARY KEY,
    judge_username VARCHAR(50) NOT NULL UNIQUE,
    judge_display_name VARCHAR(100) NOT NULL,
    judge_password_hash VARCHAR(255) NOT NULL,
    judge_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    judge_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_judge_username (judge_username),
    INDEX idx_judge_display_name (judge_display_name)
);

-- Participants table
CREATE TABLE participants (
    participant_id INT AUTO_INCREMENT PRIMARY KEY,
    participant_name VARCHAR(100) NOT NULL,
    participant_category VARCHAR(50) NOT NULL,
    participant_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    participant_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_participant_name (participant_name),
    INDEX idx_participant_category (participant_category)
);

-- Scores table
CREATE TABLE scores (
    score_id INT AUTO_INCREMENT PRIMARY KEY,
    score_participant_id INT NOT NULL,
    score_judge_id INT NOT NULL,
    score_value INT NOT NULL CHECK (score_value >= 1 AND score_value <= 100),
    score_created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score_updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (score_participant_id) REFERENCES participants(participant_id) ON DELETE CASCADE,
    FOREIGN KEY (score_judge_id) REFERENCES judges(judge_id) ON DELETE CASCADE,
    INDEX idx_score_participant (score_participant_id),
    INDEX idx_score_judge (score_judge_id),
    INDEX idx_score_value (score_value),
    UNIQUE KEY unique_judge_participant (score_judge_id, score_participant_id)
);
```

### Audit Tables (For change tracking)

```sql
-- Audit tables track all changes for accountability
CREATE TABLE admins_audit (
    admin_audit_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_audit_operation_type ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    admin_audit_admin_id INT NOT NULL,
    admin_audit_old_username VARCHAR(50),
    admin_audit_new_username VARCHAR(50),
    admin_audit_old_password VARCHAR(255),
    admin_audit_new_password VARCHAR(255),
    admin_audit_changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_audit_changed_by VARCHAR(50)
);

-- Similar audit tables exist for judges, participants, and scores
-- See setup.php for complete audit table definitions and triggers
```

## 📁 Project Structure

```
gaming-championship-judging/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Main admin dashboard
│   ├── login.php             # Admin authentication
│   └── logout.php            # Admin logout
├── judges/                     # Judge portal
│   ├── dashboard.php         # Judge scoring interface
│   ├── login.php            # Judge authentication
│   └── logout.php           # Judge logout
├── api/                       # AJAX endpoints
│   ├── check_username.php    # Username availability check
│   ├── get_judge_scores.php  # Detailed judge scores
│   ├── get_scores.php        # Participant rankings
│   ├── get_updates.php       # Real-time update polling
│   └── submit_score.php      # Score submission
├── assets/                    # Static assets
│   ├── css/
│   │   └── styles.css        # Main stylesheet (responsive)
│   ├── js/
│   │   ├── admin.js         # Admin panel functionality
│   │   ├── judges.js        # Judge portal interactions
│   │   ├── main.js          # Shared utilities
│   │   └── scoreboard.js    # Real-time scoreboard
│   └── images/
│       └── background.jpg    # Gaming-themed background
├── includes/                  # Shared PHP components
│   ├── dbConnection.php     # Database connection handler
│   ├── functions.php        # Utility functions
│   ├── setup_functions.php  # Database setup automation
│   ├── header.php          # Common HTML header
│   └── footer.php          # Common HTML footer
├── index.php                 # Homepage/landing page
├── scoreboard.php           # Live scoreboard display
├── setup.php               # Database initialization
├── railway.json            # Railway deployment config
├── nixpacks.toml          # Build configuration
└── README.md              # This file
```

## 🏗 Design Choices & Architecture

### Database Design Philosophy

**1. Consistent Naming Convention**
- All columns prefixed with table name (e.g., `admin_id`, `judge_username`)
- Prevents naming conflicts and improves clarity
- Makes joins and queries more readable

**2. Comprehensive Audit Trail**
- Every table has corresponding audit table
- Database triggers automatically track all changes
- Provides accountability and change history
- Essential for tournament integrity

**3. Normalized Structure**
- Separate entities for admins, judges, participants, scores
- Proper foreign key relationships with cascade options
- Prevents data duplication and maintains consistency

**4. Flexible Scoring System**
- One score per judge-participant combination (enforced by unique constraint)
- Integer scores (1-100) with database-level validation

### PHP Architecture Decisions

**1. Separation of Concerns**
- `includes/` for shared functionality and configuration
- `api/` for AJAX endpoints (clean separation of data layer)
- Role-based directories (`admin/`, `judges/`) for access control
- Modular structure allows easy feature additions

**2. Security-First Approach**
- **CSRF Protection**: All forms include and validate CSRF tokens
- **Password Security**: `password_hash()` with strong defaults
- **SQL Injection Prevention**: Prepared statements throughout
- **Session Security**: Regeneration on login, proper cleanup
- **Input Validation**: Server-side validation for all user inputs

**3. Error Handling Strategy**
- Comprehensive try-catch blocks with logging
- User-friendly error messages (no technical details exposed)
- Graceful degradation when JavaScript is disabled
- Database connection fallbacks and retry logic

**4. Environment Flexibility**
- Automatic detection of local vs. production environments
- Environment variable configuration for deployment
- Automatic database setup for demo purposes

### Frontend Design Philosophy

**1. Progressive Enhancement**
- Core functionality works without JavaScript
- JavaScript enhances user experience (AJAX, animations)
- Responsive design using modern CSS (Grid, Flexbox)
- Mobile-first approach with breakpoint optimization

**2. Real-time User Experience**
- AJAX-powered score submission with immediate feedback
- Live scoreboard updates using polling (every 3 seconds)
- Visual animations for score changes and updates
- Toast notifications for user actions

**3. Gaming-Themed Design**
- Purple/cyan/yellow color scheme for gaming aesthetic
- Smooth animations and hover effects
- Card-based layout with depth and shadows
- Font Awesome icons for visual consistency

**4. Accessibility Considerations**
- Semantic HTML structure
- Proper ARIA labels and roles
- Keyboard navigation support
- Screen reader friendly content structure

## 🔍 Key Assumptions Made

### Tournament Structure
1. **Single Tournament**: System designed for one active tournament at a time
2. **Score Range**: All scores are integers between 1-100 points
3. **Judge Independence**: Each judge scores independently without seeing other scores
4. **Score Finality**: Judges can update their scores, but no approval workflow needed

### Technical Assumptions
1. **Browser Support**: Modern browsers with ES6+ JavaScript support
2. **Network Reliability**: Stable internet connection for real-time features
3. **Single Server**: No load balancing or distributed database considerations
4. **Session Management**: Standard PHP sessions sufficient for user management

### Business Logic
1. **Average Scoring**: Final rankings based on average scores from all judges
2. **Partial Scoring**: Participants can be ranked even if not all judges have scored them
3. **Real-time Updates**: 3-10 second polling interval acceptable for live updates
4. **Demo Data**: Automatic sample data creation for demonstration purposes

### Security Model
1. **Role-based Access**: Clear separation between admin and judge capabilities
2. **Password Policy**: Default passwords must be changed on first login
3. **Session Timeout**: Standard PHP session timeout acceptable
4. **HTTPS**: Assumed to be handled at web server/proxy level

## 🚀 Deployment Options

### Railway (Recommended - MySQL Support)
```bash
# 1. Push to GitHub
git push origin main

# 2. Connect Railway to GitHub repository
# 3. Add MySQL database service
# 4. Deploy automatically
```



### Local Development
```bash
# Using PHP built-in server
php -S localhost:8000

# Using XAMPP/WAMP/MAMP
# Place files in htdocs directory
```

## 🧪 Testing the Application

### Manual Testing Checklist

**Admin Panel Testing:**
- [ ] Login with admin credentials
- [ ] Add new judge (check username validation)
- [ ] Add new participant
- [ ] View participant scores
- [ ] Clear all scores (with confirmation)

**Judge Portal Testing:**
- [ ] Login with judge credentials
- [ ] Change default password on first login
- [ ] Search for participants
- [ ] Submit scores using slider
- [ ] Update existing scores

**Scoreboard Testing:**
- [ ] View live rankings
- [ ] Check score calculations (averages)
- [ ] Verify judge score breakdowns
- [ ] Test real-time updates
- [ ] Check responsive design on mobile

**Security Testing:**
- [ ] Attempt to access admin panel without login
- [ ] Try SQL injection in forms
- [ ] Test CSRF protection
- [ ] Verify password hashing

## 🔧 Troubleshooting

### Common Issues

**Database Connection Errors:**
- Check database credentials in `includes/dbConnection.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

**Permission Denied:**
- Check file permissions (755 for directories, 644 for files)
- Ensure web server has read access to all files
- Verify PHP has write access for sessions

**Scores Not Updating:**
- Check browser console for JavaScript errors
- Verify API endpoints are accessible
- Check database foreign key constraints
- Ensure CSRF tokens are being generated

**Login Issues:**
- Clear browser cache and cookies
- Check session configuration in PHP
- Verify default credentials haven't been changed
- Check password hashing compatibility

## 🎯 Future Enhancements

If given more development time, the following features would significantly enhance the system:


**1. Advanced Tournament Management**
- **Multi-Tournament Support**: Handle multiple concurrent tournaments
- **Tournament Brackets**: Elimination rounds and playoff systems
- **Weighted Scoring**: Different point values for different categories
- **Time-based Rounds**: Automatic round progression with timers

**2. Enhanced Real-time Features**
- **WebSocket Integration**: True real-time updates without polling
- **Live Streaming Integration**: Embed tournament streams

**3. Advanced Analytics & Reporting**
- **Performance Trends**: Historical participant performance tracking
- **Export Functionality**: PDF reports, CSV exports, Excel integration
- **Custom Dashboards**: Configurable analytics views



**4. Security & Administration**
- **Two-Factor Authentication**: Enhanced security for admin accounts
- **Role-based Permissions**: Granular access control system
- **Audit Dashboard**: Visual audit trail and change tracking
- **API Rate Limiting**: Protection against abuse and spam



**5. Social & Community Features**
- **Public Voting**: Audience participation in scoring
- **Social Sharing**: Share results on social media
- **Participant Profiles**: Detailed team/player information
- **Comment System**: Judge feedback and comments



**6. Internationalization & Accessibility**
- **Multi-language Support**: Full internationalization


## 📄 License

This project is provided as-is for educational and tournament use. Please ensure compliance with any applicable licenses for third-party components (Font Awesome, etc.).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For issues, questions or contributions:
1. Check the troubleshooting section above
2. Review the code comments for implementation details
3. Create an issue on GitHub with detailed information
4. Ensure all prerequisites are met and setup is complete

---

**Built with ❤️ for the gaming community**

*This system demonstrates modern web development practices with PHP, real-time features and responsive design. Perfect for gaming tournaments, competitions and educational purposes.*
```

This comprehensive README provides everything needed to understand, set up, and deploy your Gaming Championship Judging System. It includes clear setup instructions, complete database schema, design rationale, and a roadmap for future enhancements.

