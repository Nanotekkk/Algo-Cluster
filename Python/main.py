from Algo.research_groups import generate_finals_groups
import sys
from Connector.dict_to_SQL import dict_to_SQL
from Connector.SQL_to_dict import sql_to_dict

def main():
    if len(sys.argv) < 3:
        print("Usage: python main.py <id_demand> <group_size>")
        sys.exit(1)

    #get command line arguments
    id_demand = int(sys.argv[1])
    group_size = int(sys.argv[2])
    dico_choices = sql_to_dict(id_demand)
    final_groups_plus_score = generate_finals_groups(
        group_size,
        dico_choices
    )

    final_groups= final_groups_plus_score[0]
    final_score = final_groups_plus_score[1]

    dict_to_SQL(id_demand, final_groups,final_score)

    return 0

if __name__ == "__main__":
    main()