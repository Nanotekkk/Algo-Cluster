import mysql.connector

try:
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="clusterprojectbdd"
    )
    
    print("✅ connection established")
    
    cursor = conn.cursor()
    def sql_to_dict(id_demand):
        cursor.execute("SELECT a.id_user, b.id_user2 FROM answer_student a JOIN user_answer b ON b.id_answer = a.id_answer WHERE a.id_demand = " +  str(id_demand) + " AND a.ignore_student != 1 ORDER BY a.id_user ASC, b.affinity DESC;")
        tables = cursor.fetchall()
        
        dictionnary = {}
        for key, value in tables:
            if key not in dictionnary:
                dictionnary[key] = []
            dictionnary[key].append(value)
    
        return dictionnary
    
    print(sql_to_dict(1))

except mysql.connector.Error as err:
    print(f"❌ error : {err}")
    
finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals():
        conn.close()
        print("\n🔒 close connection")