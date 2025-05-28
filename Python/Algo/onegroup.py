from typing import List, Dict
from .affinity import affinity_matrix

class onegroup():
    def __init__(self, id_group :int, content_group : List[int], affinity_matrix : affinity_matrix) -> None:
        """
        Initializes a group with an ID, content (list of student IDs), just define the structure of the group
        :param id_group: The ID of the group.
        :param content_group: A list of student IDs in the group.
        """
        self.id_group = id_group
        self.content_group = content_group
        self.affinity_matrix = affinity_matrix

    def get_id_group(self) -> int:
        """
        Returns the ID of the group.
        :return: The ID of the group.
        """
        return self.id_group
    
    def get_content_group(self) -> List[int]:
        """
        Returns the content of the group.
        :return: A list of student IDs in the group.
        """
        return self.content_group
    
    def get_mutual_affinity(self, s1, s2):
        """"Calculates the mutual affinity between two students in the group.
        :param s1: The ID of the first student.
        :param s2: The ID of the second student.
        :return: The mutual affinity score between the two students."""
        return self.affinity_matrix.get_affinity(s1, s2) + self.affinity_matrix.get_affinity(s2, s1)

    
    def average_affinity(self) -> float:
        """
        Calculates the average affinity score for the group based on the provided affinity matrix.
        :param affinity_matrix: An instance of the affinity_matrix class containing affinity scores.
        :return: The average affinity score for the group.
        """
        total_affinity = 0
        count = 0
        
        for i in range(len(self.content_group)):
            for j in range(i + 1, len(self.content_group)):
                student1 = self.content_group[i]
                student2 = self.content_group[j]
                total_affinity += self.get_mutual_affinity(student1, student2)
                # Increment the count for each pair of students
                # This ensures that we only count each pair once
                count += 1
        # Calculate the average affinity score

        return total_affinity / count if count > 0 else 0.0
    
    def total_affinity(self) -> float:
        """
        Calculates the total affinity score for the group based on the provided affinity matrix.
        :return: The total affinity score for the group.
        """
        total_affinity = 0
        
        for i in range(len(self.content_group)):
            for j in range(i + 1, len(self.content_group)):
                student1 = self.content_group[i]
                student2 = self.content_group[j]
                total_affinity += self.get_mutual_affinity(student1, student2)

        
        return total_affinity
    
    def average_affinity_one_student(self, student_id:int) -> float:
        """
        Calculates the average affinity score for a specific student in the group based on the provided affinity matrix.
        :param student_id: The ID of the student for whom to calculate the average affinity.
        :param affinity_matrix: An instance of the affinity_matrix class containing affinity scores.
        :return: The average affinity score for the specified student in the group.
        """
        total_affinity = 0
        count = 0
        
        for other_student in self.content_group:
            if other_student != student_id:
                total_affinity += self.affinity_matrix.get_affinity(student_id, other_student)
                count += 1
        
        return total_affinity / count if count > 0 else 0.0
    
    def list_of_average_affinities(self) -> Dict[int, float]:
        """
        Returns a dictionary of average affinities for each student in the group.
        :return: A sorted dictionary from worst to best affinity within the group {student_id: average_affinity}
        """
        avg_dict = {
        student_id: self.average_affinity_one_student(student_id)
        for student_id in self.content_group
        }
    
        sorted_avg_dict = dict(sorted(avg_dict.items(), key=lambda item: item[1]))

        return sorted_avg_dict

    
    def variance_intra_group(self) -> float:
        """
        Calculates the variance of average affinities within the group.
        :return: The variance of average affinities within the group.
        """
        avg_affinities_dict = self.list_of_average_affinities()
        avg_affinities = list(avg_affinities_dict.values())
        group_average = self.average_affinity()

        variance = sum((x - group_average) ** 2 for x in avg_affinities) / len(avg_affinities)
        return variance
    
    def identify_weak_student(self) -> int:
        """
        Identifies the student with the lowest average affinity in the group.
        :return: The ID of the student with the lowest average affinity.
        """
        avg_affinities = self.list_of_average_affinities()
        return min(avg_affinities, key=avg_affinities.get)
    

    
