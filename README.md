# software-task test

This is a PHP backend and vanilla HTML/CSS/JS frontend project.

## How to Run the Project

This project uses PHP's built-in development server, which means it can run on any operating system (Linux, macOS, Windows) as long as PHP is installed.

### Prerequisites
- [PHP](https://www.php.net/downloads) installed and added to your system's PATH.

### 1. Start the Backend API Server
Open a terminal (or Command Prompt/PowerShell on Windows) in the root of the project directory and start the backend server on port 8000:

```bash
php -S localhost:8000 -t backend/
```
*(Keep this terminal running)*

### 2. Start the Frontend Server
Open a **second** terminal window in the root of the project directory, and start the frontend server on port 3000:

```bash
php -S localhost:3000 -t frontend/
```
*(Alternatively, if you have Python installed, you can run: `python3 -m http.server 3000 --directory frontend`)*

### 3. View the Application
Once both servers are running, open your web browser and navigate to:

```text
http://localhost:3000
```

You can verify the frontend successfully connected to the backend by opening your browser's Developer Tools (F12) and checking the **Console** tab for the success message from the API.
