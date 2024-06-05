# TrendtroveMaya
 

# TrendTrove

TrendTrove is the ultimate destination for fashion enthusiasts. It serves as a unique platform where individuals can easily discover trendy outfits every day. Users can explore pins, add pins to their profile page as a board for future reference, and showcase outfit inspirations suitable for every season. Visitors to TrendTrove can discover a wide array of stylish outfit ideas, ranging from summer to winter looks. The website has a simple interface, akin to flipping through a digital fashion magazine, but even better because you can interact with everything.

Moreover, users have the ability to search for seasonal collection outfit ideas of their interest and also search for other users, ensuring a tailored browsing experience. With TrendTrove Analytics, content creators can gain valuable insights into their audience demographics, interests, and engagement metrics, helping them understand their audience better and optimize their Pinterest strategy accordingly. At TrendTrove, there's something for everyone, whether you love bold fashion or classic styles.

Additionally, users can message each other, view notifications, follow and unfollow accounts, and comment on posts, making the platform highly interactive and engaging.

## Features

- **User Authentication**: Sign up, log in, and log out functionality.
- **Pin Management**: Create, view, and save pins to boards.
- **Profile Management**: View and edit user profiles, follow/unfollow users.
- **Comments**: Add and view comments on pins.
- **Notifications**: Receive notifications for various activities.
- **Messaging**: Send and receive messages with other users.
- **Analytics**: Track profile and pin views.

## Database Structure

### Tables

- **Analytics**: Tracks user interactions such as profile and pin views.
- **Boards**: Stores information about user-created boards.
- **board_pins**: Links pins to specific boards.
- **comments**: Stores comments made by users on pins.
- **Followers**: Tracks follower/following relationships between users.
- **Messages**: Stores messages sent between users.
- **Notifications**: Stores notifications for user activities.
- **Pins**: Stores information about pins created by users.
- **SeasonCollection**: Stores information about different seasonal collections.
- **users**: Stores user profile information.

### Database Schema Overview

```
Analytics          | 4 rows
Boards             | 7 rows
board_pins         | 7 rows
comments           | 16 rows
Followers          | 6 rows
Messages           | 17 rows
Notifications      | 19 rows
Pins               | 17 rows
SeasonCollection   | 4 rows
users              | 20 rows
```

## Getting Started

### Prerequisites

- PHP 7.x or higher
- MySQL
- Composer (for dependency management)
- A web server like Apache or Nginx

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/MayaCamill2004/TrendtroveMaya/edit/main/README.md
   cd TrendTroveMaya
   ```

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Database setup:**

   - Create a new MySQL database.
   - Import the SQL file located in the `database` folder to create the necessary tables.

4. **Configure your environment:**

   - Copy the `.env.TrendTrove` file to `.env` and update the database credentials and other configuration settings.

     ```bash
     cp .env.TrendTrove .env
     ```

5. **Run the application:**

   - Start your web server and navigate to the project directory.
   - Access the application via your web browser.

## Folder Structure

```
TrendTrove/
├── assets/             # Images, logos, and other static assets
├── database/           # SQL files for database setup
├── style/              # CSS files
├── java/               # script files
├── Users/              # User-related PHP scripts
├── db_connection.php   # Database connection script
├── db_functions.php    # Database helper functions
├── index.php           # Main entry point of the application
├── login.php           # User login page
├── logout.php          # User logout script
├── register.php        # User registration page
├── README.md           # Project README file
```

## Main Files

- `index.php`: The main entry point of the application.
- `login.php`: User login functionality.
- `logout.php`: User logout functionality.
- `register.php`: User registration functionality.
- `account.php`: User profile page.
- `view_pin.php`: View pin details and comments.
- `db_connection.php`: Database connection setup.
- `db_functions.php`: Helper functions for database operations.

## CSS

All the CSS for the application is located in the `style/style.css` file. It includes styles for the layout, navbar, profile, pins, comments, and other components.

## JavaScript

The JavaScript functions are embedded within the HTML files for handling various functionalities like opening tabs, fetching messages, sending messages, etc.

## Contributing

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/my-new-feature`).
3. Commit your changes (`git commit -am 'Add some feature'`).
4. Push to the branch (`git push origin feature/my-new-feature`).
5. Create a new Pull Request.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

If you have any questions or feedback, feel free to reach out to me at [mayacam2004@gmail.com].
```

