import numpy as np 
from typing import List, Dict

dico_choices = {
    1: [2, 3, 4, 5, 6],
    2: [3, 4, 5, 6, 1],
    3: [4, 5, 6, 1, 2],
    4: [5, 6, 1, 2, 3],
    5: [6, 1, 2, 3, 4],
    6: [1, 2, 3, 4, 5]
}

number_of_students = len(dico_choices)

class affinity_matrix():
    """
    Builds a matrix of affinities between students based on their choices.
    Each student has a list of choices, and the matrix is filled with scores based on the rank of each choice."""
    def __init__(self, dico_choices : Dict[int, List[int]], number_of_students : int):
        self.number_of_students = number_of_students
        self.matrix = np.zeros((number_of_students, number_of_students), dtype=int)
        self.fill_matrix(dico_choices)
        self.number_of_choices = len(dico_choices[1])  # Assuming all students have the same number of choices

    def add_affinity(self, student1 : int, student2 : int, affinity : int) -> None:
        """
        Adds an affinity score between two students in the matrix."""
        self.matrix[student1, student2] = affinity

    def get_affinity(self, student1 : int, student2 : int) -> int:
        """
        Returns the affinity score between two students."""
        return self.matrix[student1, student2]
    
    def get_affinity_matrix(self) -> np.ndarray:
        """Returns the affinity matrix."""
        return self.matrix
    
    def get_number_of_choices(self) -> int:
        """Returns the number of choices each student has."""
        return self.number_of_choices
    
    def fill_matrix(self, dico_choices: Dict[int, List[int]]) -> None:
        """fills the affinity matrix based on the choices of each student."""
        for student_id, choices in dico_choices.items():
            score_by_rank = [n for n in range(len(choices), 0, -1)]
            for rank, choice in enumerate(choices):
                self.add_affinity(student_id-1, choice-1, score_by_rank[rank])
        print("Matrice des affinités :")
        print(self.matrix)



g = affinity_matrix(dico_choices, number_of_students)
