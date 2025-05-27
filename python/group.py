from affinity import affinity_matrix
from typing import List, Dict

class group():
    """
    Represents a group of students, the user wants a group of n students build up depending of the m preference's choices that 
    each student made.
    """
    def __init__(self,affinity_matrix: affinity_matrix, group_size: int, number_of_students : int, dico_choices : Dict[int, List[int]]) -> None:
        self.group_size = group_size
        self.number_of_students = number_of_students
        if group_size > number_of_students or group_size <= 0 or number_of_students% group_size != 0:
            raise ValueError("Group size must be a positive integer that divides the number of students evenly.")
        self.affinity_matrix = affinity_matrix(dico_choices, number_of_students)

        def generate_random_group() -> List[int]:
            """
            Generates a random group of students based on the affinity matrix.
            Returns a list of student IDs in the group."""
            import random
            group = random.sample(range(self.number_of_students), self.group_size)
            return group
