import numpy as np 
from typing import List, Dict

class affinity_matrix:
    """
    Builds a matrix of affinities between students.
    Each student has a fixed number of points to distribute.
    The matrix is filled based on the score each student gives to others.
    """

    def __init__(self, dico_choices: Dict[int, Dict[int, int]]) -> None:
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary.")
        self.dico_choices = dico_choices
        self._initialize_mappings()
        self.fill_matrix(dico_choices)

    def _initialize_mappings(self):
        """Initializes ID ↔︎ position mappings based on the union of all student IDs."""
        all_ids = sorted(set(self.dico_choices.keys()) | {k for v in self.dico_choices.values() for k in v.keys()})
        self.id_to_pos = {id_: i for i, id_ in enumerate(all_ids)}
        self.pos_to_id = {i: id_ for i, id_ in enumerate(all_ids)}
        self.n = len(all_ids)
        self.matrix = np.zeros((self.n, self.n), dtype=int)

    def verify_total_points(self, expected_total=100):
        """Verifies that each student distributed exactly `expected_total` points."""
        for student, choices in self.dico_choices.items():
            total = sum(choices.values())
            if total != expected_total:
                raise ValueError(f"Student {student} did not distribute exactly {expected_total} points. Total given: {total}")

    def add_affinity(self, student1_id: int, student2_id: int, affinity: int) -> None:
        """Adds an affinity score between two students, using their IDs."""
        i = self.id_to_pos[student1_id]
        j = self.id_to_pos[student2_id]
        self.matrix[i, j] = affinity

    def get_affinity(self, student1_id: int, student2_id: int) -> int:
        """Returns the affinity score between two students, using their IDs."""
        i = self.id_to_pos[student1_id]
        j = self.id_to_pos[student2_id]
        return self.matrix[i, j]

    def get_affinity_matrix(self) -> np.ndarray:
        """Returns the full affinity matrix."""
        return self.matrix

    def fill_matrix(self, dico_choices: Dict[int, Dict[int, int]]) -> None:
        """Fills the matrix using the student ID-based interface."""
        for student_id, choices in dico_choices.items():
            for other_id, score in choices.items():
                if student_id != other_id:
                    self.add_affinity(student_id, other_id, score)

        print("✅ Affinity matrix filled successfully.")
        print(self.matrix)


        



#g = affinity_matrix(dico_choices)
#print(g.get_affinity(3, 5) ) # Example usage to get affinity between student 1 and student 2
