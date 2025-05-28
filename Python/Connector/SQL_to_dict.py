from SQL.DatabaseConnector import get_db_connection

def sql_to_dict(id_demand):
    """
    gets the answers from the database and returns them as a dictionary.
    """
    with get_db_connection() as (conn, cursor):
        # Use a parameterized query to prevent SQL injection
        query = """
        SELECT a.id_user, b.id_user2, b.Affinity
        FROM answer_student a 
        JOIN user_answer b ON b.id_answer = a.id_answer 
        WHERE a.id_demand = %s AND a.ignore_student != 1 AND a.as_answer = 1
        ORDER BY a.id_user ASC, b.affinity DESC
        """
        cursor.execute(query, (id_demand,))
        tables = cursor.fetchall()
        
        dictionnary = {}
        for cle_principale, cle_secondaire, valeur in tables:
            if cle_principale not in dictionnary:
                dictionnary[cle_principale] = {}
            dictionnary[cle_principale][cle_secondaire] = valeur
        
        return dictionnary

# test
if __name__ == "__main__":
    result = sql_to_dict(1)
    print(f"Résultat: {result}")