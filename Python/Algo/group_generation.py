from .affinity import affinity_matrix
from typing import List, Dict
from random import sample
from .onegroup import onegroup

dico_choices = {
    1: {2: 30, 3: 40, 5: 30},
    2: {1: 25, 3: 25, 4: 25, 6: 25},
    3: {1: 50, 2: 25, 4: 25},
    4: {2: 50, 5: 25, 6: 25},
    5: {1: 30, 2: 30, 3: 40},
    6: {7: 60, 8: 40},
    7: {6: 20, 8: 20, 9: 20, 10: 40},
    8: {1: 50, 2: 20, 9: 30},
    9: {1: 40, 2: 30, 3: 30},
    10: {5: 50, 6: 30, 7: 20},
    11 :{ 9: 30, 7: 40, 10: 30},
    12: {1: 26, 5: 24, 3: 25, 4: 25},
}



class group_generation():
    """
    Represents a group of students, the user wants a group of n students build up depending of the m preference's choices that 
    each student made.
    """
    def __init__(self, group_size: int, dico_choices : Dict[int, List[int]]) -> None:
        """
        Initializes the group generation with a specified group size and a dictionary of student choices.
        :param group_size: The size of each group.
        :param dico_choices: A dictionary where keys are student IDs and values are lists of their choices.
        """
        self.group_size = group_size
        self.number_of_students = len(dico_choices)
        self.affinity_matrix = affinity_matrix(dico_choices)
        self.list_of_students = list(dico_choices.keys())
        #verification of the inputs
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary.")
        if group_size > self.number_of_students or group_size <= 0 or self.number_of_students% group_size != 0:
            raise ValueError("Group size must be a positive integer that divides the number of students evenly.")
        a= self.instanciate_all_groups()  # Instantiates all groups
        self.list_of_groups = a[0]  # Get the list of group instances
        self.dict_of_groups = a[1]  # Get the dictionary of group contents

    def get_list_of_groups(self) -> List[onegroup]:
        """
        Returns the list of group instances.
        :return: A list of onegroup instances.
        """
        return self.list_of_groups
    
    def get_score(self, alpha : float, beta : float) -> float :
        """Returns the score after changing groups, taking into account the total variance intra groups and inter groups"""
        avg_intra_all_groups = sum([g.variance_intra_group() for g in self.list_of_groups])/len(self.list_of_groups)
        return (alpha*avg_intra_all_groups + beta* self.variance_inter_group())
    
    def change_new_groups(self, new_groups: Dict[int, List[int]]) -> None:
        """
        Changes the current groups to a new set of groups.
        :param new_groups: A dictionary where keys are group IDs and values are lists of student IDs in each group.
        """
        self.dict_of_groups = new_groups
        self.list_of_groups = []
        for id_group, content_group in new_groups.items():
            self.list_of_groups.append(onegroup(id_group, content_group, self.affinity_matrix))
        
        
    
    def get_dict_of_groups(self) -> Dict[int, List[int]]:
        """
        Returns the dictionary of group contents.
        :return: A dictionary where keys are group IDs and values are lists of student IDs in each group.
        """
        return self.dict_of_groups

    
    def set_alpha(self, alpha: float) -> None:
        """
        Sets the weight for the intra-group variance in the score calculation.
        :param alpha: The weight for intra-group variance.
        """
        self.alpha = alpha

    def set_beta(self, beta: float) -> None:
        """
        Sets the weight for the inter-group variance in the score calculation.
        :param beta: The weight for inter-group variance.
        """
        self.beta = beta


    def generate_random_groups(self) -> Dict[int, List[int]]:
        """
        Generates a random group of students that we will use to find the best distribution of students in groups taking into account their affinities, so that the group has a good score. 
        """
        shuffled_students = sample(self.list_of_students, len(self.list_of_students))
        final = {i: shuffled_students[i * self.group_size : (i + 1) * self.group_size]
        for i in range(self.number_of_students // self.group_size)}
        print("Random group generated:", final)
        return final
    
    def instanciate_all_groups(self):
        """
        Instantiates all groups and returns both the group instances and a dict of group content.
        :return: (list_of_onegroup_instances, dict_of_group_contents)
        """
        random_groups = self.generate_random_groups()
        list_of_groups = []
        dict_of_groups = {}

        for id_group, content_group in random_groups.items():
            list_of_groups.append(onegroup(id_group, content_group, self.affinity_matrix))
            dict_of_groups[id_group] = content_group

        print("All groups instantiated.")
        return list_of_groups, dict_of_groups
    
    def average_affinity_global(self) -> float:
        """
        Calculates the average affinity score across all groups.
        :return: The average affinity score for all groups.
        """
        total_affinity = sum(group.average_affinity() for group in self.list_of_groups)
        return total_affinity / len(self.list_of_groups) if self.list_of_groups else 0.0


    def variance_inter_group(self) -> float:
        """
        Calculates the variance of the average affinity scores between groups.
        :return: The variance of the average affinity scores between groups.
        """
        avg_affinities = [group.average_affinity() for group in self.list_of_groups]
        overall_average = self.average_affinity_global()  # Utilisation de la méthode existante
        variance = sum((x - overall_average) ** 2 for x in avg_affinities) / len(avg_affinities) if avg_affinities else 0.0
        return variance
    
    def list_of_average_affinities_group(self) -> Dict[int, float]:
        """
        Returns a dictionary of average affinities for each group.
        :return: A sorted dictionary from worst to best group (affinity) {group_id: average_affinity}
        """
        avg_dict ={
            group.id_group: group.average_affinity()
            for group in self.list_of_groups
        }
    
        sorted_avg_dict = dict(sorted(avg_dict.items(), key=lambda item: item[1]))

        return sorted_avg_dict
    
    def list_of_variances_intra_group(self) -> Dict[int, float]:
        """
        Returns a dictionary of variances of average affinities within each group.
        :return: A dictionary {group_id: variance_intra_group}
        """
        return {
            group.id_group: group.variance_intra_group()
            for group in self.list_of_groups
        }
    
    def identify_weak_group(self) -> int:
        """
        Identifies the group with the lowest average affinity score.
        :return: The ID of the group with the lowest average affinity.
        """
        avg_affinities = self.list_of_average_affinities_group()
        if not avg_affinities:
            return None
        return min(avg_affinities, key=avg_affinities.get)
    
    


"""g= group_generation(3
                    , dico_choices)
print("List of groups instantiated:")
for group_id, students in g.get_dict_of_groups().items():
    print(f"Group {group_id}: {students}")"""
