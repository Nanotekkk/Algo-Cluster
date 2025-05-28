import mysql.connector
def fetch_data_from_db(host, user, password, database, query):
    try:
        # Establish a database connection
        connection = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        
        cursor = connection.cursor(dictionary=True)  # Use dictionary cursor to get results as dict
        cursor.execute(query)
        
        # Fetch all rows from the executed query
        results = cursor.fetchall()
        
        return results
    
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None
    
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()