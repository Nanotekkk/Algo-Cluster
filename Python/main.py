import sys
from python.SQL.dict_to_SQL import dict_to_SQL
from python.SQL.SQL_to_dict import sql_to_dict

"""SETUP FOR TOMORROW"""

def main():
    if len(sys.argv) < 4:
        print("Usage: python main.py <id_demand> <group_size> <vote_size>")
        sys.exit(1)

    id_demand = sys.argv[1]
    group_size = sys.argv[2]
    vote_size = sys.argv[3]
    print(f"Arguments received: id_demand={id_demand}, group_size={group_size}, vote_size={vote_size}")

if __name__ == "__main__":
    main()