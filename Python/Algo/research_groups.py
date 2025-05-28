from typing import List, Dict, Tuple
import random
import copy
import math
import numpy as np
from .onegroup import onegroup
from .group_generation import group_generation

class research_groups:
    """
    Optimizes student group formation using simulated annealing to maximize intra-group affinity
    while maintaining balanced inter-group variance. Improved version that better handles
    unidirectional vs bidirectional relationships.
    """
    
    def __init__(self, group_size: int, dico_choices: Dict[int, List[int]], 
                 max_iter: int = 1000, patience: int = 100, 
                 alpha: float = 1.0, beta: float = 0.5, 
                 initial_temp: float = 10.0, cooling_rate: float = 0.995,
                 mutual_bonus: float = 1.5, unidirectional_penalty: float = 0.7):
        """
        Initialize the group optimizer.
        
        Args:
            group_size: Number of students per group
            dico_choices: Dictionary mapping student_id -> {other_student_id: affinity_score}
            max_iter: Maximum optimization iterations
            patience: Stop if no improvement for this many iterations
            alpha: Weight for maximizing intra-group affinity (higher = more important)
            beta: Weight for minimizing inter-group variance (higher = more important)
            initial_temp: Starting temperature for simulated annealing
            cooling_rate: Temperature decay rate per iteration
            mutual_bonus: Multiplier for bidirectional relationships (default 1.5)
            unidirectional_penalty: Multiplier for unidirectional relationships (default 0.7)
        """
        self.group_size = group_size
        self.dico_choices = dico_choices
        self.max_iter = max_iter
        self.patience = patience
        self.alpha = alpha
        self.beta = beta
        self.temperature = initial_temp
        self.cooling_rate = cooling_rate
        self.mutual_bonus = mutual_bonus
        self.unidirectional_penalty = unidirectional_penalty
        
        # Validate inputs
        if not isinstance(dico_choices, dict):
            raise TypeError("dico_choices must be a dictionary")
        
        num_students = len(dico_choices)
        if num_students % group_size != 0:
            raise ValueError(f"Number of students ({num_students}) must be divisible by group size ({group_size})")
        
        # Initialize group generation
        self.group_gen = group_generation(group_size, dico_choices)
        self.affinity_matrix = self.group_gen.affinity_matrix
        
        # Current state
        self.current_groups = self.group_gen.get_dict_of_groups()
        self.current_score = self._calculate_score(self.current_groups)
        
        # Best state tracking
        self.best_groups = copy.deepcopy(self.current_groups)
        self.best_score = self.current_score
        
        # Optimization completed flag
        self.is_optimized = False
        
        print(f"Initialized with {num_students} students in {len(self.current_groups)} groups")
        print(f"Initial score: {self.current_score:.4f}")
        print(f"Mutual bonus: {mutual_bonus}, Unidirectional penalty: {unidirectional_penalty}")
    
    def _get_improved_mutual_affinity(self, s1: int, s2: int) -> float:
        """
        Calculate mutual affinity between two students with improved logic:
        - Bidirectional relationships get a bonus
        - Unidirectional relationships get a penalty
        - No relationship gets zero score
        
        Args:
            s1: First student ID
            s2: Second student ID
            
        Returns:
            Weighted mutual affinity score
        """
        # Get raw affinity scores (note: matrix uses 0-based indexing)
        affinity_s1_to_s2 = self.affinity_matrix.get_affinity(s1, s2)
        affinity_s2_to_s1 = self.affinity_matrix.get_affinity(s2, s1)
        
        # Check relationship type
        has_s1_to_s2 = affinity_s1_to_s2 > 0
        has_s2_to_s1 = affinity_s2_to_s1 > 0
        
        if has_s1_to_s2 and has_s2_to_s1:
            # Bidirectional relationship - apply bonus
            base_score = (affinity_s1_to_s2 + affinity_s2_to_s1) / 2
            return base_score * self.mutual_bonus
        elif has_s1_to_s2 or has_s2_to_s1:
            # Unidirectional relationship - apply penalty
            base_score = max(affinity_s1_to_s2, affinity_s2_to_s1)
            return base_score * self.unidirectional_penalty
        else:
            # No relationship
            return 0.0
    
    def _calculate_score(self, groups_dict: Dict[int, List[int]]) -> float:
        """
        Calculate the quality score for a group configuration using improved affinity calculation.
        Lower scores are better.
        
        Score = -alpha * avg_intra_affinity + beta * inter_group_variance
        
        Args:
            groups_dict: Dictionary mapping group_id -> list of student_ids
            
        Returns:
            Quality score (lower is better)
        """
        # Calculate metrics for each group
        group_metrics = []
        total_affinity = 0
        total_pairs = 0
        
        for group_id, student_list in groups_dict.items():
            # Calculate group affinity using improved method
            group_affinity = 0
            pair_count = 0
            
            for i in range(len(student_list)):
                for j in range(i + 1, len(student_list)):
                    s1, s2 = student_list[i], student_list[j]
                    pair_affinity = self._get_improved_mutual_affinity(s1, s2)
                    group_affinity += pair_affinity
                    pair_count += 1
            
            avg_group_affinity = group_affinity / pair_count if pair_count > 0 else 0
            group_metrics.append(avg_group_affinity)
            total_affinity += group_affinity
            total_pairs += pair_count
        
        # Calculate average intra-group affinity (higher is better)
        avg_intra_affinity = total_affinity / total_pairs if total_pairs > 0 else 0
        
        # Calculate inter-group variance (lower is better)
        overall_average = sum(group_metrics) / len(group_metrics) if group_metrics else 0
        inter_group_variance = sum((avg - overall_average) ** 2 for avg in group_metrics) / len(group_metrics) if group_metrics else 0
        
        # Combined score (we want to maximize intra-affinity and minimize variance)
        score = -self.alpha * avg_intra_affinity + self.beta * inter_group_variance
        
        return score
    
    def _get_random_student_pair(self) -> Tuple[int, int, int, int]:
        """
        Get two random students from different groups.
        
        Returns:
            Tuple of (student1_id, group1_id, student2_id, group2_id)
        """
        # Get two different groups
        group_ids = list(self.current_groups.keys())
        group1_id, group2_id = random.sample(group_ids, 2)
        
        # Get random student from each group
        student1_id = random.choice(self.current_groups[group1_id])
        student2_id = random.choice(self.current_groups[group2_id])
        
        return student1_id, group1_id, student2_id, group2_id
    
    def _get_strategic_student_pair(self) -> Tuple[int, int, int, int]:
        """
        Get a strategic student pair based on improved affinity calculation.
        Focus on students with poor connections in their current groups.
        
        Returns:
            Tuple of (student1_id, group1_id, student2_id, group2_id)
        """
        # Find the student with the worst average affinity in their current group
        worst_student = None
        worst_affinity = float('inf')
        worst_group_id = None
        
        for group_id, student_list in self.current_groups.items():
            for student in student_list:
                # Calculate this student's average affinity with their groupmates
                student_affinity = 0
                count = 0
                for other_student in student_list:
                    if other_student != student:
                        student_affinity += self._get_improved_mutual_affinity(student, other_student)
                        count += 1
                
                avg_affinity = student_affinity / count if count > 0 else 0
                
                if avg_affinity < worst_affinity:
                    worst_affinity = avg_affinity
                    worst_student = student
                    worst_group_id = group_id
        
        # Get random student from a different group
        other_group_ids = [gid for gid in self.current_groups.keys() if gid != worst_group_id]
        other_group_id = random.choice(other_group_ids)
        other_student_id = random.choice(self.current_groups[other_group_id])
        
        return worst_student, worst_group_id, other_student_id, other_group_id
    
    def _test_swap(self, student1_id: int, group1_id: int, student2_id: int, group2_id: int) -> float:
        """
        Test a swap between two students and return the score change.
        
        Args:
            student1_id: First student ID
            group1_id: First student's current group ID
            student2_id: Second student ID  
            group2_id: Second student's current group ID
            
        Returns:
            Score improvement (positive = better, negative = worse)
        """
        # Create temporary groups with the swap
        temp_groups = copy.deepcopy(self.current_groups)
        
        # Perform the swap
        temp_groups[group1_id].remove(student1_id)
        temp_groups[group1_id].append(student2_id)
        temp_groups[group2_id].remove(student2_id)
        temp_groups[group2_id].append(student1_id)
        
        # Calculate new score
        new_score = self._calculate_score(temp_groups)
        
        # Return improvement (current_score - new_score, so positive = improvement)
        return self.current_score - new_score
    
    def _perform_swap(self, student1_id: int, group1_id: int, student2_id: int, group2_id: int):
        """
        Actually perform the swap between two students.
        
        Args:
            student1_id: First student ID
            group1_id: First student's current group ID
            student2_id: Second student ID
            group2_id: Second student's current group ID
        """
        # Update current groups
        self.current_groups[group1_id].remove(student1_id)
        self.current_groups[group1_id].append(student2_id)
        self.current_groups[group2_id].remove(student2_id)
        self.current_groups[group2_id].append(student1_id)
        
        # Update current score
        self.current_score = self._calculate_score(self.current_groups)
    
    def _accept_change(self, improvement: float) -> bool:
        """
        Decide whether to accept a change based on improvement and temperature.
        
        Args:
            improvement: Score improvement (positive = better)
            
        Returns:
            True if change should be accepted
        """
        if improvement > 0:
            return True
        
        if self.temperature <= 0:
            return False
        
        # Simulated annealing: accept bad moves with decreasing probability
        probability = math.exp(improvement / self.temperature)
        return random.random() < probability
    
    def optimize_groups(self) -> Dict[int, List[int]]:
        """
        Main optimization loop using simulated annealing.
        
        Returns:
            Dictionary of optimized groups {group_id: [student_ids]}
        """
        print("Starting optimization...")
        
        iterations_without_improvement = 0
        strategic_swaps = 0
        random_swaps = 0
        accepted_swaps = 0
        
        for iteration in range(self.max_iter):
            # Choose swap strategy (70% strategic, 30% random)
            if random.random() < 0.7:
                student1_id, group1_id, student2_id, group2_id = self._get_strategic_student_pair()
                strategic_swaps += 1
            else:
                student1_id, group1_id, student2_id, group2_id = self._get_random_student_pair()
                random_swaps += 1
            
            # Test the swap
            improvement = self._test_swap(student1_id, group1_id, student2_id, group2_id)
            
            # Decide whether to accept
            if self._accept_change(improvement):
                self._perform_swap(student1_id, group1_id, student2_id, group2_id)
                accepted_swaps += 1
                
                # Check if this is the best configuration so far
                if self.current_score < self.best_score:
                    self.best_score = self.current_score
                    self.best_groups = copy.deepcopy(self.current_groups)
                    iterations_without_improvement = 0
                    print(f"Iteration {iteration}: New best score = {self.best_score:.4f} "
                          f"(improvement: {improvement:.4f})")
                else:
                    iterations_without_improvement += 1
            else:
                iterations_without_improvement += 1
            
            # Cool down temperature
            self.temperature *= self.cooling_rate
            
            # Progress reporting
            if iteration % 200 == 0 and iteration > 0:
                print(f"Iteration {iteration}: Current = {self.current_score:.4f}, "
                      f"Best = {self.best_score:.4f}, Temp = {self.temperature:.4f}")
                print(f"  Strategic swaps: {strategic_swaps}, Random swaps: {random_swaps}, "
                      f"Accepted: {accepted_swaps}")
            
            # Early stopping
            if iterations_without_improvement >= self.patience:
                print(f"Stopping early: No improvement for {self.patience} iterations")
                break
        
        # Restore best configuration
        self.current_groups = copy.deepcopy(self.best_groups)
        self.current_score = self.best_score
        self.is_optimized = True
        
        print(f"\nOptimization completed!")
        print(f"Final score: {self.best_score:.4f}")
        print(f"Total swaps attempted: {strategic_swaps + random_swaps}")
        print(f"Swaps accepted: {accepted_swaps} ({100*accepted_swaps/(strategic_swaps + random_swaps):.1f}%)")
        
        return self.best_groups
    
    def get_final_groups(self) -> Dict[int, List[int]]:
        """
        Get the final optimized groups.
        
        Returns:
            Dictionary of final groups {group_id: [student_ids]}
            
        Raises:
            ValueError: If optimization hasn't been run yet
        """
        if not self.is_optimized:
            raise ValueError("Must run optimize_groups() first")
        return copy.deepcopy(self.best_groups)
    
    def get_final_score(self) -> float:
        """
        Get the final optimization score.
        
        Returns:
            Final score (lower is better)
            
        Raises:
            ValueError: If optimization hasn't been run yet
        """
        if not self.is_optimized:
            raise ValueError("Must run optimize_groups() first")
        return self.best_score
    
    def analyze_relationships(self) -> Dict[str, int]:
        """
        Analyze the types of relationships in the affinity matrix.
        
        Returns:
            Dictionary with counts of bidirectional, unidirectional, and no relationships
        """
        bidirectional = 0
        unidirectional = 0
        no_relationship = 0
        
        students = list(self.dico_choices.keys())
        
        for i, s1 in enumerate(students):
            for s2 in students[i+1:]:
                affinity_s1_to_s2 = self.affinity_matrix.get_affinity(s1, s2)
                affinity_s2_to_s1 = self.affinity_matrix.get_affinity(s2, s1)
                
                has_s1_to_s2 = affinity_s1_to_s2 > 0
                has_s2_to_s1 = affinity_s2_to_s1 > 0
                
                if has_s1_to_s2 and has_s2_to_s1:
                    bidirectional += 1
                elif has_s1_to_s2 or has_s2_to_s1:
                    unidirectional += 1
                else:
                    no_relationship += 1
        
        return {
            'bidirectional': bidirectional,
            'unidirectional': unidirectional,
            'no_relationship': no_relationship
        }
    
    def print_detailed_results(self):
        """
        Print detailed analysis of the final group configuration with relationship analysis.
        """
        if not self.is_optimized:
            print("Optimization not completed yet.")
            return
        
        print("\n" + "="*60)
        print("RELATIONSHIP ANALYSIS")
        print("="*60)
        
        relationship_stats = self.analyze_relationships()
        total_pairs = sum(relationship_stats.values())
        
        print(f"Total student pairs: {total_pairs}")
        print(f"Bidirectional relationships: {relationship_stats['bidirectional']} ({100*relationship_stats['bidirectional']/total_pairs:.1f}%)")
        print(f"Unidirectional relationships: {relationship_stats['unidirectional']} ({100*relationship_stats['unidirectional']/total_pairs:.1f}%)")
        print(f"No relationships: {relationship_stats['no_relationship']} ({100*relationship_stats['no_relationship']/total_pairs:.1f}%)")
        
        print("\n" + "="*60)
        print("DETAILED GROUP ANALYSIS")
        print("="*60)
        
        total_affinity = 0
        total_pairs_in_groups = 0
        group_affinities = []
        
        for group_id in sorted(self.best_groups.keys()):
            student_list = self.best_groups[group_id]
            
            # Calculate group affinity using improved method
            group_affinity = 0
            pair_count = 0
            relationship_types = {'bidirectional': 0, 'unidirectional': 0, 'none': 0}
            
            print(f"\nGroup {group_id}: {student_list}")
            
            for i in range(len(student_list)):
                for j in range(i + 1, len(student_list)):
                    s1, s2 = student_list[i], student_list[j]
                    
                    # Analyze relationship type
                    affinity_s1_to_s2 = self.affinity_matrix.get_affinity(s1, s2)
                    affinity_s2_to_s1 = self.affinity_matrix.get_affinity(s2, s1)
                    
                    has_s1_to_s2 = affinity_s1_to_s2 > 0
                    has_s2_to_s1 = affinity_s2_to_s1 > 0
                    
                    if has_s1_to_s2 and has_s2_to_s1:
                        relationship_types['bidirectional'] += 1
                        print(f"  {s1}↔{s2}: {affinity_s1_to_s2}↔{affinity_s2_to_s1} (bidirectional)")
                    elif has_s1_to_s2 or has_s2_to_s1:
                        relationship_types['unidirectional'] += 1
                        if has_s1_to_s2:
                            print(f"  {s1}→{s2}: {affinity_s1_to_s2} (unidirectional)")
                        else:
                            print(f"  {s2}→{s1}: {affinity_s2_to_s1} (unidirectional)")
                    else:
                        relationship_types['none'] += 1
                        print(f"  {s1}—{s2}: no relationship")
                    
                    pair_affinity = self._get_improved_mutual_affinity(s1, s2)
                    group_affinity += pair_affinity
                    pair_count += 1
            
            avg_affinity = group_affinity / pair_count if pair_count > 0 else 0
            
            total_affinity += group_affinity
            total_pairs_in_groups += pair_count
            group_affinities.append(avg_affinity)
            
            print(f"  Average weighted affinity: {avg_affinity:.2f}")
            print(f"  Total weighted affinity: {group_affinity:.0f}")
            print(f"  Relationships: {relationship_types['bidirectional']} bidirectional, "
                  f"{relationship_types['unidirectional']} unidirectional, "
                  f"{relationship_types['none']} none")
        
        # Global statistics
        overall_avg = total_affinity / total_pairs_in_groups if total_pairs_in_groups > 0 else 0
        variance = sum((avg - overall_avg) ** 2 for avg in group_affinities) / len(group_affinities)
        
        print(f"\n" + "-"*40)
        print("GLOBAL STATISTICS")
        print("-"*40)
        print(f"Overall average weighted affinity: {overall_avg:.2f}")
        print(f"Inter-group variance: {variance:.4f}")
        print(f"Total weighted affinity points: {total_affinity:.0f}")
        print(f"Total student pairs in groups: {total_pairs_in_groups}")
        print(f"Final optimization score: {self.best_score:.4f}")
        print(f"Mutual bonus: {self.mutual_bonus}, Unidirectional penalty: {self.unidirectional_penalty}")


def generate_finals_groups(group_size: int, dico_choices: Dict[int, List[int]], 
                          max_iter: int = 1000, patience: int = 100,
                          alpha: float = 1.0, beta: float = 0.5,
                          mutual_bonus: float = 1.5, unidirectional_penalty: float = 0.7) -> Tuple[Dict[int, List[int]], float]:
    """
    Generate optimal student groups using simulated annealing with improved relationship handling.
    
    Args:
        group_size: Number of students per group
        dico_choices: Dictionary mapping student_id -> {other_student_id: affinity_score}
        max_iter: Maximum optimization iterations
        patience: Stop if no improvement for this many iterations
        alpha: Weight for maximizing intra-group affinity
        beta: Weight for minimizing inter-group variance
        mutual_bonus: Multiplier for bidirectional relationships (default 1.5)
        unidirectional_penalty: Multiplier for unidirectional relationships (default 0.7)
        
    Returns:
        Tuple of (final_groups_dict, final_score)
    """
    optimizer = research_groups(
        group_size=group_size,
        dico_choices=dico_choices,
        max_iter=max_iter,
        patience=patience,
        alpha=alpha,
        beta=beta,
        mutual_bonus=mutual_bonus,
        unidirectional_penalty=unidirectional_penalty
    )
    
    optimizer.optimize_groups()
    optimizer.print_detailed_results()
    
    return optimizer.get_final_groups(), optimizer.get_final_score()


# Example usage
if __name__ == "__main__":
    # Test data with students numbered 1-12
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
    
    print("Testing improved group optimization...")
    final_groups, final_score = generate_finals_groups(
        group_size=3,
        dico_choices=dico_choices,
        max_iter=2000,
        patience=200,
        alpha=1.0,  # Prioritize high intra-group affinity
        beta=0.3,   # Less weight on variance between groups
        mutual_bonus=1.5,  # Bonus for mutual relationships
        unidirectional_penalty=0.7  # Penalty for one-way relationships
    )
    
    print(f"\nFinal groups: {final_groups}")
    print(f"Final score: {final_score:.4f}")