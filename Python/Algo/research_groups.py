from affinity import affinity_matrix
from typing import List, Dict, Tuple
import random
import copy
import math
from onegroup import onegroup
from group_generation import group_generation

class research_groups():
    def __init__(self, group_size : int, dico_choices :  Dict[int, List[int]], max_iter=1000, patience=100, epsilon=0.01, threshold_var=0.01,
                 alpha=1.0, beta=1.0, T0=2.0, cooling_rate=0.995):
        """
        Initializes the research groups with a specified group size and a dictionary of student choices.
        
        Parameters:
        - group_size: Size of each group
        - dico_choices: Dictionary of student preferences
        - max_iter: Maximum number of iterations
        - patience: Number of iterations without improvement before stopping
        - epsilon: Minimum improvement threshold for accepting swaps
        - alpha: Weight for intra-group variance (higher = more important)
        - beta: Weight for inter-group variance (higher = more important)
        - T0: Initial temperature for simulated annealing
        - cooling_rate: Rate at which temperature decreases
        """
        self.group_size = group_size
        self.dico_choices = dico_choices
        self.max_iter = max_iter
        self.patience = patience
        self.epsilon = epsilon
        self.threshold_var = threshold_var
        self.alpha = alpha
        self.beta = beta
        self.T0 = T0
        self.current_temp = self.T0
        self.cooling_rate = cooling_rate
        
        # Generate the affinity matrix and the groups
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary.")
            
        self.group_gen = group_generation(group_size, dico_choices)
        self.affinity_matrix = self.group_gen.affinity_matrix
        self.list_of_groups = self.group_gen.list_of_groups
        self.dict_of_groups = self.group_gen.dict_of_groups
        self.score = self.group_gen.get_score(self.alpha, self.beta)
        self.isFinalGroups = False
        self.final_affinity = 0.0
        self.final_dict_of_groups = None
        self.final_list_of_groups = None
        
        # Statistics tracking
        self.iteration_stats = []
        self.best_score_history = []
        
    def get_final_groups(self) -> Dict[int, List[int]]:
        """Returns the final groups after optimization."""
        if not self.isFinalGroups:
            raise ValueError("The optimization process has not been completed yet.")
        return self.final_dict_of_groups
    
    def test_swap(self, student1: int, group1_id: int, student2: int, group2_id: int) -> float:
        """
        Tests a swap between two students without actually performing it.
        Returns the score improvement (positive = better, negative = worse).
        """
        # Current score
        old_score = self.score
        
        # Create temporary copies
        temp_group_gen = copy.deepcopy(self.group_gen)
        temp_dict = copy.deepcopy(self.dict_of_groups)
        
        # Perform temporary swap
        temp_dict[group1_id].remove(student1)
        temp_dict[group1_id].append(student2)
        temp_dict[group2_id].remove(student2)
        temp_dict[group2_id].append(student1)
        
        # Update the temporary group_gen with new groups
        temp_group_gen.change_new_groups(temp_dict)
        
        # Calculate new score
        new_score = temp_group_gen.get_score(self.alpha, self.beta)
        
        # Return improvement (positive = better, negative = worse)
        return old_score - new_score

    def do_swap(self, student1: int, group1_id: int, student2: int, group2_id: int):
        """
        Actually performs the swap between two students and updates all data structures.
        """
        # Update group dictionary
        self.dict_of_groups[group1_id].remove(student1)
        self.dict_of_groups[group1_id].append(student2)
        self.dict_of_groups[group2_id].remove(student2)
        self.dict_of_groups[group2_id].append(student1)
        
        # Update all structures
        self.group_gen.change_new_groups(self.dict_of_groups)
        self.list_of_groups = self.group_gen.get_list_of_groups()
        self.score = self.group_gen.get_score(self.alpha, self.beta)

    def controlled_swap(self) -> Tuple[bool, float]:
        """
        Performs a controlled swap: takes the worst student from the worst group
        and tests swaps with other groups.
        Returns (swap_performed, improvement).
        """
        # Identify worst group and worst student
        worst_group_id = self.group_gen.identify_weak_group()
        if worst_group_id is None:
            return False, 0.0
            
        worst_group = next(g for g in self.list_of_groups if g.id_group == worst_group_id)
        worst_student = worst_group.identify_weak_student()
        
        # Get list of other groups
        other_groups = [g for g in self.list_of_groups if g.id_group != worst_group_id]
        
        # 60% of time: ordered by quality (worst to best), 40% random
        if random.random() < 0.6:
            avg_affinities = self.group_gen.list_of_average_affinities_group()
            other_groups.sort(key=lambda g: avg_affinities[g.id_group])
        else:
            random.shuffle(other_groups)
        
        best_improvement = -float('inf')
        best_swap = None
        
        # Test swaps with each candidate group
        for candidate_group in other_groups:
            for candidate_student in candidate_group.content_group:
                improvement = self.test_swap(worst_student, worst_group_id, 
                                            candidate_student, candidate_group.id_group)
                
                if improvement > best_improvement:
                    best_improvement = improvement
                    best_swap = (worst_student, worst_group_id, candidate_student, candidate_group.id_group)
        
        # Decide if we accept the swap
        if best_swap and self._accept_swap(best_improvement):
            self.do_swap(*best_swap)
            return True, best_improvement
        
        return False, 0.0

    def random_swap(self) -> Tuple[bool, float]:
        """
        Performs a random swap between two random students from different groups.
        Returns (swap_performed, improvement).
        """
        # Choose two different groups randomly
        group_ids = list(self.dict_of_groups.keys())
        if len(group_ids) < 2:
            return False, 0.0
            
        group1_id, group2_id = random.sample(group_ids, 2)
        
        # Choose random student from each group
        student1 = random.choice(self.dict_of_groups[group1_id])
        student2 = random.choice(self.dict_of_groups[group2_id])
        
        # Test the swap
        improvement = self.test_swap(student1, group1_id, student2, group2_id)
        
        # Decide if we accept the swap
        if self._accept_swap(improvement):
            self.do_swap(student1, group1_id, student2, group2_id)
            return True, improvement
        
        return False, 0.0

    def _accept_swap(self, improvement: float) -> bool:
        """
        Decides whether to accept a swap based on improvement and simulated annealing probability.
        """
        # If improvement is significant, accept
        if improvement > self.epsilon:
            return True
        
        # For negative improvements, use simulated annealing probability
        if improvement < 0 and self.current_temp > 0:
            probability = math.exp(improvement / self.current_temp)
            return random.random() < probability
        
        # Small positive improvements are accepted with some probability
        if 0 <= improvement <= self.epsilon and self.current_temp > 0:
            probability = 0.3 * math.exp(improvement / self.current_temp)
            return random.random() < probability
        
        return False

    def multi_student_swap(self) -> Tuple[bool, float]:
        """
        Attempts to swap multiple students between groups for more complex reorganization.
        """
        if len(self.dict_of_groups) < 2:
            return False, 0.0
        
        # Choose two groups
        group_ids = random.sample(list(self.dict_of_groups.keys()), 2)
        group1_id, group2_id = group_ids
        
        # Try swapping 2 students from each group
        if len(self.dict_of_groups[group1_id]) >= 2 and len(self.dict_of_groups[group2_id]) >= 2:
            students1 = random.sample(self.dict_of_groups[group1_id], 2)
            students2 = random.sample(self.dict_of_groups[group2_id], 2)
            
            # Calculate combined improvement
            total_improvement = 0
            temp_dict = copy.deepcopy(self.dict_of_groups)
            temp_group_gen = copy.deepcopy(self.group_gen)
            
            # Perform swaps
            for s1, s2 in zip(students1, students2):
                temp_dict[group1_id].remove(s1)
                temp_dict[group1_id].append(s2)
                temp_dict[group2_id].remove(s2)
                temp_dict[group2_id].append(s1)
            
            temp_group_gen.change_new_groups(temp_dict)
            new_score = temp_group_gen.get_score(self.alpha, self.beta)
            total_improvement = self.score - new_score
            
            if self._accept_swap(total_improvement):
                # Perform actual swaps
                for s1, s2 in zip(students1, students2):
                    self.dict_of_groups[group1_id].remove(s1)
                    self.dict_of_groups[group1_id].append(s2)
                    self.dict_of_groups[group2_id].remove(s2)
                    self.dict_of_groups[group2_id].append(s1)
                
                self.group_gen.change_new_groups(self.dict_of_groups)
                self.list_of_groups = self.group_gen.get_list_of_groups()
                self.score = self.group_gen.get_score(self.alpha, self.beta)
                return True, total_improvement
        
        return False, 0.0

    def optimize_groups(self) -> Dict[int, List[int]]:
        """
        Main optimization method using enhanced simulated annealing with multiple swap strategies.
        """
        print("Starting group optimization...")
        print(f"Initial score: {self.score:.4f}")
        
        best_score = self.score
        best_groups = copy.deepcopy(self.dict_of_groups)
        current_score = best_score
        
        iterations_without_improvement = 0
        swap_counts = {'controlled': 0, 'random': 0, 'multi': 0}
        improvement_counts = {'controlled': 0, 'random': 0, 'multi': 0}
        
        for iteration in range(self.max_iter):
            # Choose swap strategy with adaptive probabilities
            rand_val = random.random()
            swap_performed = False
            improvement = 0.0
            swap_type = ""
            
            if rand_val < 0.5:  # 50% controlled swap
                swap_performed, improvement = self.controlled_swap()
                swap_type = 'controlled'
            elif rand_val < 0.85:  # 35% random swap
                swap_performed, improvement = self.random_swap()
                swap_type = 'random'
            else:  # 15% multi-student swap
                swap_performed, improvement = self.multi_student_swap()
                swap_type = 'multi'
            
            # Update statistics
            if swap_performed:
                swap_counts[swap_type] += 1
                if improvement > 0:
                    improvement_counts[swap_type] += 1
                
                current_score = self.score
                
                # Check if it's the best score found
                if current_score < best_score:
                    best_score = current_score
                    best_groups = copy.deepcopy(self.dict_of_groups)
                    iterations_without_improvement = 0
                    print(f"Iteration {iteration}: New best score = {best_score:.4f} (improvement: {improvement:.4f}) [{swap_type}]")
                    self.best_score_history.append((iteration, best_score))
                else:
                    iterations_without_improvement += 1
            else:
                iterations_without_improvement += 1
            
            # Adaptive cooling with occasional reheating
            self.current_temp *= self.cooling_rate
            if iteration % 200 == 0 and iteration > 0:
                self.current_temp = max(self.current_temp, self.T0 * 0.1)  # Reheat slightly
            
            # Stop criteria
            if iterations_without_improvement >= self.patience:
                print(f"Stopping: No improvement for {self.patience} iterations")
                break
            
            # Progress reporting
            if iteration % 100 == 0 and iteration > 0:
                print(f"Iteration {iteration}: Current = {current_score:.4f}, Best = {best_score:.4f}, Temp = {self.current_temp:.6f}")
                print(f"  Swaps - Controlled: {swap_counts['controlled']}/{improvement_counts['controlled']}, "
                      f"Random: {swap_counts['random']}/{improvement_counts['random']}, "
                      f"Multi: {swap_counts['multi']}/{improvement_counts['multi']}")
        
        # Restore best configuration
        self.dict_of_groups = best_groups
        self._rebuild_groups_from_dict()
        self.score = self.group_gen.get_score(self.alpha, self.beta)
        
        # Mark as finalized
        self.isFinalGroups = True
        self.final_affinity = self.score
        self.final_dict_of_groups = copy.deepcopy(self.dict_of_groups)
        self.final_list_of_groups = copy.deepcopy(self.list_of_groups)
        
        print(f"\nOptimization completed. Final score: {self.score:.4f}")
        print(f"Total improvement: {current_score - best_score:.4f}")
        print(f"Swap statistics:")
        for swap_type, count in swap_counts.items():
            success_rate = improvement_counts[swap_type] / max(count, 1) * 100
            print(f"  {swap_type.capitalize()}: {count} attempts, {improvement_counts[swap_type]} improvements ({success_rate:.1f}%)")
        
        return self.dict_of_groups

    def _rebuild_groups_from_dict(self):
        """
        Rebuilds the list of group objects from the dictionary of groups.
        """
        self.list_of_groups = []
        for id_group, content_group in self.dict_of_groups.items():
            self.list_of_groups.append(onegroup(id_group, content_group, self.affinity_matrix))
        
        self.group_gen.dict_of_groups = self.dict_of_groups
        self.group_gen.list_of_groups = self.list_of_groups

    def print_final_statistics(self):
        """
        Prints detailed statistics about the final group configuration.
        """
        if not self.isFinalGroups:
            print("Optimization not completed yet.")
            return
        
        print("\n" + "="*50)
        print("FINAL GROUP STATISTICS")
        print("="*50)
        print(f"Final Score: {self.final_affinity:.4f}")
        print(f"Global Average Affinity: {self.group_gen.average_affinity_global():.2f}")
        print(f"Inter-group Variance: {self.group_gen.variance_inter_group():.4f}")
        
        print("\nDetailed Group Analysis:")
        avg_affinities = self.group_gen.list_of_average_affinities_group()
        intra_variances = self.group_gen.list_of_variances_intra_group()
        
        for group_id in sorted(self.final_dict_of_groups.keys()):
            students = self.final_dict_of_groups[group_id]
            print(f"\nGroup {group_id}: {students}")
            print(f"  Average Affinity: {avg_affinities[group_id]:.2f}")
            print(f"  Intra Variance: {intra_variances[group_id]:.4f}")
            
            # Show individual student affinities within group
            group_obj = next(g for g in self.final_list_of_groups if g.id_group == group_id)
            individual_affinities = group_obj.list_of_average_affinities()
            print(f"  Individual affinities: {individual_affinities}")

    def debug_current_state(self):
        """Debug method to print current state information."""
        print(f"\nCurrent Score: {self.score:.4f}")
        print(f"Current Temperature: {self.current_temp:.6f}")
        print("Current Groups:")
        for group_id, students in self.dict_of_groups.items():
            group_obj = next(g for g in self.list_of_groups if g.id_group == group_id)
            print(f"  Group {group_id}: {students} (avg affinity: {group_obj.average_affinity():.2f})")

    def get_final_score(self) -> float:
        """Returns the final score after optimization."""
        if not self.isFinalGroups:
            raise ValueError("The optimization process has not been completed yet.")
        return self.score



def generate_finals_groups(group_size: int, dico_choices: Dict[int, List[int]], max_iter=1000, patience=100, epsilon=0.01,
                          alpha=1.0, beta=1.0, T0=2.0, cooling_rate=0.995) -> Dict[int, List[int]]:
    """
    Generates final groups using the research_groups optimizer.
    
    Parameters:
    - group_size: Size of each group
    - dico_choices: Dictionary of student preferences
    - max_iter: Maximum number of iterations
    - patience: Number of iterations without improvement before stopping
    - epsilon: Minimum improvement threshold for accepting swaps
    - alpha: Weight for intra-group variance
    - beta: Weight for inter-group variance
    - T0: Initial temperature for simulated annealing
    - cooling_rate: Rate at which temperature decreases
    
    Returns:
    A dictionary of final groups.
    """
    optimizer = research_groups(
        group_size=group_size,
        dico_choices=dico_choices,
        max_iter=max_iter,
        patience=patience,
        epsilon=epsilon,
        alpha=alpha,
        beta=beta,
        T0=T0,
        cooling_rate=cooling_rate
    )
    
    optimizer.optimize_groups()
    return optimizer.get_final_groups(), optimizer.get_final_score()



# Example usage with optimized parameters
if __name__ == "__main__":
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
        11: {9: 30, 7: 40, 10: 30},
        12: {1: 26, 5: 24, 3: 25, 4: 25},
    }
    
    a= generate_finals_groups(
        group_size=3,
        dico_choices=dico_choices
    )

    print(a[0])
    print(a[1])