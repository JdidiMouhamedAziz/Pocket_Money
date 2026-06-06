<?php

    class BudgetCategory{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //create budgetCategory
        public function createBudgetCategory($categoryId,$budgetId,$limitAmout){
            $stmt = $this->pdo->prepare("INSERT INTO budgetcategory (limitAmout,categoryId,budgetId) VALUES (?,?,?)");
            return $stmt->execute([$limitAmout,$categoryId,$budgetId]);
        }

        //find ctagoryu by budget
        public function findCategoryByBudget($budgetId){
            $stmt = $this->pdo->prepare("SELECT * FROM budgetCategory WHERE budgetId=?");
            $stmt->execute([$budgetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find budget By  category
        public function findBudgetByCategory($categotyId){
            $stmt = $this->pdo->prepare("SELECT * FROM budgetCategory WHERE categoryId=?");
            $stmt->execute([$categotyId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // update budget category link
        public function updateBudgetCategory($budgetId,$categoryId,$limitAmout){
            $stmt = $this->pdo->prepare("UPDATE budgetcategory SET limitAmout=? WHERE budgetId=? AND categoryId=?");
            return $stmt->execute([$limitAmout,$budgetId,$categoryId]);
        }

        // delete budget category link
        public function deleteBudgetCategory($budgetId,$categoryId){
            $stmt = $this->pdo->prepare("DELETE FROM budgetcategory WHERE budgetId=? AND categoryId=?");
            return $stmt->execute([$budgetId,$categoryId]);
        }
        
    }

?>