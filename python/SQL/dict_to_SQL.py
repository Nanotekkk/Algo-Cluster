from db import get_db_connection

def dict_to_SQL(id_demand, dictionnary, score):
    """
    Inserts a dictionary into the database.
    """
    with get_db_connection() as (conn, cursor):
        conn.start_transaction()
        
        for group_name, user_ids in dictionnary.items():
            insert_group_query = """
            INSERT INTO `group` (id_demand, group_name)
            VALUES (%s, %s)
            """
            cursor.execute(insert_group_query, (id_demand, str(group_name)))
            
            id_group = cursor.lastrowid
            
            insert_group_user_query = """
            INSERT INTO group_user (id_group, id_user) 
            VALUES (%s, %s)
            """
            
            for user_id in user_ids:
                cursor.execute(insert_group_user_query, (id_group, user_id))

        # Update the demand table to set istreated to true
        update_treated = """
        UPDATE demand
        SET istreated = 1
        WHERE id_demand = %s
        """
        cursor.execute(update_treated, (id_demand,))

        # Update the repartition_score for the demand
        update_score = """
        UPDATE demand
        SET repartition_score = %s
        WHERE id_demand = %s"""
        cursor.execute(update_score, (score, id_demand))
        
        conn.commit()
        print(f"✅ Insertion réussie : {len(dictionnary)} groupes insérés")

# test
if __name__ == "__main__":
    test_dict = {
        "group1": [1, 2, 3],
        "group2": [4, 5, 6]
    }
    dict_to_SQL(1, test_dict, 85.5)