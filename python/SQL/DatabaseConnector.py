import mysql.connector
from contextlib import contextmanager

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "clusterprojectbdd"
}

@contextmanager
def get_db_connection():
    """
    Context manager is used to handle the database connection.
    It ensures that the connection is properly closed after use.
    """
    conn = None
    cursor = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ connection established")
        yield conn, cursor
    except mysql.connector.Error as err:
        print(f"❌ error : {err}")
        if conn:
            conn.rollback()
        raise
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()
            print("\n🔒 close connection")

def get_simple_connection():
    """
    Fonction to get a simple database connection without context management.
    """
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        print("✅ connection established")
        return conn
    except mysql.connector.Error as err:
        print(f"❌ error : {err}")
        return None