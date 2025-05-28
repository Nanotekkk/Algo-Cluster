from affinity import affinity_matrix
from typing import List, Dict
import random
from onegroup import onegroup
from group_generation import group_generation

class research_groups():
    def __init__(self, group_size : int, dico_choices :  Dict[int, List[int]],max_iter=1000, patience=50, epsilon=0.01, threshold_var=0.01,
                 alpha=1.0, beta=1.0, T0=1.0, cooling_rate=0.995):
        """
        Initializes the research groups with a specified group size and a dictionary of student choices."""
        self.group_size = group_size
        self.dico_choices = dico_choices
        self.max_iter = max_iter
        self.patience = patience
        self.epsilon = epsilon
        self.threshold_var = threshold_var
        self.alpha = alpha
        self.beta = beta
        self.T0 = T0
        self.cooling_rate = cooling_rate
        #generate the affinity matrix and the groups
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary.")
        self.group_gen = group_generation(group_size, dico_choices)
        self.affinity_matrix = self.group_gen.affinity_matrix
        self.list_of_groups = self.group_gen.list_of_groups
        self.dict_of_groups = self.group_gen.dict_of_groups
        self.isFinalGroups = False
        self.final_affinity = 0.0
        self.final_dict_of_groups = None
        self.final_list_of_groups = None

    
        def get_final_groups(self) -> List[onegroup]:
            """Returns the final groups after optimization."""
            if not self.isFinalGroups:
                raise ValueError("The optimization process has not been completed yet.")
            return self.final_list_of_groups
        
        def get_score(group1 : onegroup, group2 : onegroup) -> float:
            """Returns the score of the changed groups when 2 student were changed through intra_variance, and inter_variance.
            :return: The score of the changed groups.
            """
            avg_intra_bothgroups = (group1.variance_intra_group() + group2.variance_intra_group()) / 2

            return (self.alpha * avg_intra_bothgroups + self.beta * self.group_gen.variance_inter_group())
    
        


