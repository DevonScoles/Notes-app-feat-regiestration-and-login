![Screenshot of application](https://github.com/DevonScoles/Notes-app-feat-registration-and-login/blob/master/notes_home_snapshot.PNG?raw=true)

## Table of Contents
- [About This Project](#about-this-project)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Installation](#installation)
- [Usage](#usage)
- [License](#license)
- [Acknowledgements](#acknowledgements)

## About This Project
This is my first laravel project showcasing a simple app that lets the user view, store, edit, and delete notes. Most importantly the app features the use of an account system that registers users with an email and password as well as authentication. Restrictions are implemented so that when a user creates an account they cannot view other users' notes. The app uses css and js to be styled. While it's not very flashy building this app taught me the fundamentals of laravell and really engrained how MVC works under the laravel framework.  

## Features
- User registration and authentication
- Stripe scubscription
- Create, read, update, and delete notes
- User-specific notes (each user can only view their own notes)
- Simple and clean user interface
- Responsive design using CSS and JavaScript

## Technologies Used
- Laravel
- Stripe
- PHP
- Cashier
- sqlite
- HTML/CSS
- JavaScript
- Xampp
- composer

## Installation
1. Install:
    - xampp
    - composer (make sure path is in environment variables)
    - php (make sure path is in environment variables)
2. Clone the repository(using vscode):
   https://github.com/DevonScoles/Notes-app-feat-regiestration-and-login.git
3. Navigate to project in vscode
6. Open terminal in vscode
7. Install composer and artisan
8. setup the .env file by renaming .env.example to .env
9. To use the "Premium" button functionality
    create Stripe account and insert the secret key and publish key in the .env file
11. Start the development server by typing:
    `php artisan serve`
12. Run npm by typing:
    `npm run dev`
13. Visit http://localhost:8000 in your browser.
14. Install npm via terminal:
    `php artisan install`


## Usage
1. Register an account (does not need to be a real email address since this is just a local test)
2. Navigate to the file `C:\[projectpath]\storage\logs\laravel.log`  
NOTE: This is the log file and it's how we are going to locally authenticate
3. hit `CTRL+click` on the link that's shown on line 19 where it says "Verify Email Address:..."  
NOTE: If line 19 does not say this then delete the contents of the log file and go back to your browser and hit resend verification link then check the log again
4. Now that you have logged in and verified your account you can start creating notes and viewing them
5. You can create multiple accounts to see how the app does not allow you to view other accounts' notes
6. when logged into an account view a note and pay attention to the http it will be something like "localhost:8000/note/1" where 1 is the id of the note
7. log out of that account then log into a different account and try to view "localhost:8000/note/1" you shouldn't be able to since that note is under a different account 

## License
The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
