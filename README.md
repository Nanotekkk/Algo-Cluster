# Cluster Project

## Overview
Cluster Project is a web application designed to help professors manage student groups efficiently. Professors can create group requests, select specific students, and automatically generate groups based on predefined criteria.

---

## System Architecture
The system is designed with the following components:

1. **Frontend**:
   - Built using PHP for rendering dynamic web pages.
   - HTML, CSS, and JavaScript are used for the user interface.

2. **Backend**:
   - PHP handles server-side logic, including user authentication, group request management, and database interactions.

3. **Database**:
   - MySQL/MariaDB is used to store user data, group requests, and generated groups.
   - Key tables include:
     - `user`: Stores user information.
     - `demand`: Stores group requests.
     - `group`: Stores generated groups.

4. **Algorithm**:
   - Python scripts are used for group generation based on predefined criteria (e.g., group size, selected students).
   - The backend triggers these scripts and stores the results in the database.

5. **Local Server**:
   - The application is designed to run on a local server environment like Laragon, XAMPP, or WAMP.

---

## Technology Choices
The following technologies were chosen for this project:

- **PHP**: Chosen for its simplicity and wide adoption in web development.
- **MySQL/MariaDB**: A reliable and efficient relational database for managing structured data.
- **Python**: Used for its powerful libraries and ease of implementing algorithms.
- **HTML/CSS/JavaScript**: For building a responsive and user-friendly interface.

These technologies were selected to balance ease of development, performance, and compatibility with local server environments.

---

## Subject Interpretations
The project is based on the following interpretations of the subject:

1. Professors need a tool to manage group requests and automate group creation.
2. Students should have visibility into the group requests they are part of and their assigned groups.
3. The algorithm should prioritize simplicity while meeting the requirements for group size and student selection.

---

## Algorithm Functionality
The group generation algorithm works as follows:

1. **Input**:
   - A list of selected students.
   - The desired group size.

2. **Process**:
   - The Python script divides the students into groups of the specified size.
   - If the number of students is not divisible by the group size, the remaining students are distributed evenly among the groups.

3. **Output**:
   - The generated groups are stored in the `group` and `group_user` tables in the database.

4. **Execution**:
   - The backend triggers the Python script when the professor clicks "Generate Groups."

---

## Installation and Setup
Follow these steps to set up the project locally:

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Nanotekkk/Algo-Cluster.git
   cd Algo-Cluster
   ```
--- 

2. **Install Python dependencies**:

   - The project uses external Python libraries. Make sure Python is installed on your machine, then install the required dependencies using the following command:

   ```bash
   pip install mysql-connector-python pytest
   ```

3. **Configure the environment**:
   - Rename the `.env.example` file to `.env`.
   - Update the database credentials and other settings in the `.env` file.

4. **Import the database**:
   - Navigate to the `db/` folder and import the SQL file into your database.

5. **Start the local server**:
   - Use a local server environment like Laragon, XAMPP, or WAMP.
   - Place the project in the appropriate directory (e.g., `www/` for Laragon).
   - Access the application in your browser at `http://localhost/Algo-Cluster`.
6. **Database diagrams**:

   - The database diagrams (MLD and MCD) are available in the `Db/Diagramme` folder. These diagrams provide a clear understanding of the structure and relationships of the tables used in the project.
---

## Future Improvements
- Add email notifications for students when they are added to a group request.
- Implement a more advanced algorithm for group generation (e.g., based on student preferences or skills).
- Add an admin panel for managing users and requests.

---

## Authors
- Yasmina MOUSSAOUI
- Reda SEBAAOUI
- Matheo LANCEA