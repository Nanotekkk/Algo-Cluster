from affinity import affinity_matrix
from onegroup import onegroup
from group_generation import group_generation
from research_groups import research_groups
from research_groups import generate_finals_groups
import sys
##from python.SQL.dict_to_SQL import dict_to_SQL
##from python.SQL.SQL_to_dict import sql_to_dict

"""SETUP FOR TOMORROW"""

def main():
    if len(sys.argv) < 4:
        print("Usage: python main.py <id_demand> <group_size> <vote_size>")
        sys.exit(1)

    #get command line arguments
    id_demand = sys.argv[1]
    group_size = sys.argv[2]
    vote_size = sys.argv[3]
    dico_choices = sql_to_dict(id_demand)
    final_groups_plus_score = generate_finals_groups(
        group_size,
        dico_choices
    )

    final_groups= final_groups_plus_score[0]
    final_score = final_groups_plus_score[1]

    dict_to_sql(id_demand, final_groups,final_score)

    return 0

if __name__ == "__main__":
    main()