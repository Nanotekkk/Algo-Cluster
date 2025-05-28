import numpy as np 
from typing import List, Dict

class affinity_matrix():
    """
    Builds a matrix of affinities between students..
    Each student has a 100 points to distribute and the matrix is filled with scores based on what score the student has given to all the other student."""
    def __init__(self, dico_choices : Dict[int, List[int]]) -> None:
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary.")
        self.number_of_students = len(dico_choices)
        self.dico_choices = dico_choices
        #filling the affinity matrix 
        self.matrix = np.zeros((self.number_of_students, self.number_of_students), dtype=int)
        self.fill_matrix(dico_choices)

        
    def verify_total_points(self, expected_total=100):
        """
        Verify if each student has distributed exactly `expected_total` points across all students.
        :param affinity_dict: A dictionary where keys are student IDs and values are dictionaries of choices with scores.
        :param expected_total: The total points each student should distribute.
        :raises ValueError: If any student has not distributed the expected total points.
        """
        for student, choices in self.dico_choices.items():
            print(np.array(choices.values()))
            total = sum(choices.values())
            if total != expected_total:
                raise ValueError(f"Student {student} did not distribute exactly {expected_total} points. Total given: {total}")

    
    def add_affinity(self, student1 : int, student2 : int, affinity : int) -> None:
        """
        Adds an affinity score between two students in the matrix."""
        self.matrix[student1, student2] = affinity

    def get_affinity(self, student1 : int, student2 : int) -> int:
        """
        Returns the affinity score between two students."""
        return self.matrix[student1-1, student2-1]
    
    def get_affinity_matrix(self) -> np.ndarray:
        """Returns the affinity matrix."""
        return self.matrix
    
    
    def fill_matrix(self, dico_choices: Dict[int, List[int]]) -> None:
        """fills the affinity matrix based on the choices of each student, each student has a 100 point to distribute to each student."""
        self.verify_total_points()
        # Iterate through the dictionary and fill the matrix

        for student, choices in dico_choices.items():
            for other_student, score in choices.items():
                if student != other_student:
                    self.add_affinity(student-1, other_student-1, score)
        print("Affinity matrix filled successfully.")
        print(self.matrix)
        



#g = affinity_matrix(dico_choices)
#print(g.get_affinity(3, 5) ) # Example usage to get affinity between student 1 and student 2
