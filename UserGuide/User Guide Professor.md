# Professor User Guide

Welcome to the Cluster Project! This guide will help you navigate the platform as a professor.

---

## **Getting Started**

1. **Login or Register**:
   - Visit the login page at `http://localhost/Algo-Cluster/login.php`.
   - If you don’t have an account, click on "Register" to create one.
   - Provide your details (name, email, password) and submit the form.

2. **Access Your Dashboard**:
   - After logging in, you will be redirected to your dashboard.
   - The dashboard displays all group requests you have created.

---

## **Features**

### **Create a Group Request**
1. Navigate to the "Create Request" section.
2. Fill in the following details:
   - **Request Name**: A descriptive name for the request.
   - **Start and End Dates**: The duration of the request.
   - **Group Size**: Specify the number of students per group.
3. Submit the form to save the request.

### **Select Students for a Request**
1. Go to the "Manage Requests" section.
2. Select a request from the list.
3. Add students to the request by selecting their names from the list.

### **Generate Groups**
1. Once students are added to a request, navigate to the "Generate Groups" section.
2. Click the "Generate" button to run the Python script for group generation.
3. Groups will be automatically created based on the specified criteria.

### **View and Manage Groups**
- After groups are generated, you can view them in the "Groups" section.
- You can:
  - See group members.
  - Edit group details if necessary.

---

## **Notifications**
- Students will be notified when:
  - They are added to a group request.
  - Groups are generated, and they are assigned to a group.

---

## **Troubleshooting**
- **Issues with Group Generation**:
  - Ensure all required students are added to the request.
  - Verify the group size and criteria before generating groups.
- **Database Errors**:
  - Check your `.env` file for correct database credentials.

---

## **Support**
If you encounter any issues, please contact the support team or refer to the project documentation.