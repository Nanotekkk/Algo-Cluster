# Cluster Project

## Overview
Cluster Project is a web application designed to help professors manage student groups efficiently. Professors can create group requests, select specific students, and automatically generate groups based on predefined criteria.

## Prerequisites
To run this project, ensure you have the following installed:
- **PHP**: Version 8.x or higher
- **MySQL/MariaDB**: For database management
- **Python**: Version 3.x (used for group generation scripts)

## Installation
Follow these steps to set up the project locally:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Nanotekkk/Algo-Cluster.git
   cd Algo-Cluster
   ```

2. **Configure the environment**:
   - Rename the `.env.example` file to `.env`.
   - Update the database credentials and other settings in the `.env` file.

3. **Import the database**:
   - Navigate to the `db/` folder and import the SQL file into your database.

4. **Start the local server**:
   - Use a local server environment like Laragon, XAMPP, or WAMP.
   - Place the project in the appropriate directory (e.g., `www/` for Laragon).
   - Access the application in your browser at `http://localhost/Algo-Cluster`.

## Project Structure
The project is organized into the following directories:

- `php/`: Contains all PHP files for backend logic and views.
- `professor/`: Features specific to professors, such as creating group requests.
- `config/`: Configuration files, including database connection.
- `auth/`: Handles user authentication (login, registration, etc.).
- `python/`: Contains Python scripts for advanced group generation algorithms.
- `db/`: Includes SQL files for database setup and schema definitions.
- `assets/`: Contains static files like CSS, JavaScript, and images.

## Features
The Cluster Project offers the following key features:

### Authentication
- Secure login and registration for professors and students.
- Role-based access control (professors vs. students).

### Group Request Creation
- Professors can create group requests by specifying:
  - Start and end dates.
  - Group size (e.g., 2-10 students per group).
- Requests are stored in the `demand` table.

### Student Selection
- Professors can select specific students for a group request.
- Selected students are linked to the request in the `answer_student` table.

### Automatic Group Generation
- Python scripts are used to generate groups based on the selected students and group size.
- Results are stored in the `group` and `group_user` tables.

### Dashboard
- Professors can view and manage their group requests.
- Students can see the group requests they are part of.

## Database Schema
The project uses the following key tables:

- `user`: Stores user information (professors and students).
- `demand`: Stores group requests created by professors.
- `answer_student`: Links students to specific group requests.
- `group`: Stores generated groups.
- `group_user`: Links students to their respective groups.

## How It Works
1. A professor logs in and creates a group request.
2. The professor selects students who will participate in the request.
3. The request is saved in the database, and students can see it.
4. Groups are generated automatically using Python scripts.
5. Students can view their assigned groups in their dashboard.

## Future Improvements
- Add email notifications for students when they are added to a group request.
- Implement a more advanced algorithm for group generation (e.g., based on student preferences or skills).
- Add an admin panel for managing users and requests.

## Authors
- Yasmina MOUSSAOUI
- Reda SEBAAOUI
- Matheo LANCEA