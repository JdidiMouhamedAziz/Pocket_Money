<?php

    class Budget{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //Create Bubget
        public function createBdget($name,$limit,$period,$startDate,$note,$sendAlertAt,$userId){
            $stmt= $this->pdo->prepare("INSERT INTO budget (`name`, `limit`, period, startDate, note, sendAlertAt, userId) VALUES(?,?,?,?,?,?,?)");
            return $stmt->execute([$name,$limit,$period,$startDate,$note,$sendAlertAt,$userId]);
        }

        //Find All Budgets
        public function findAllBudget(){
            $stmt= $this->pdo->prepare("SELECT * FROM budget");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // find Budget By Id
        public function findBudgetById( $id ){
            $stmt= $this->pdo->prepare("SELECT * FROM budget WHERE idBudget=?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // find Budget By period
        public function findBudgetByPeriod($period){
            $stmt= $this->pdo->prepare("SELECT * FROM budget WHERE period =?");
            $stmt->execute([$period]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // find Budget By startDate
        public function findBudgetByStartDate($startDate){
            $stmt= $this->pdo->prepare("SELECT * FROM budget WHERE startDate=?");
            $stmt->execute([$startDate]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Budget By UserId
        public function findBudgetByUserId($userId){
            $stmt= $this->pdo->prepare("SELECT * FROM budget WHERE userId=?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //Find Budget Limit
        public function findBudgetByLimit($limit){
            $stmt= $this->pdo->prepare("SELECT * FROM budget WHERE limit=?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //update Budget
        public function updateBudget($idBudget,$name,$limit,$period,$startDate,$note,$sendAlertAt){
            $stmt= $this->pdo->prepare("UPDATE budget SET `name`=?, `limit`=?, period=?, startDate=?, note=?, sendAlertAt=? WHERE idBudget=?");
            return $stmt->execute([$name, $limit, $period, $startDate, $note, $sendAlertAt, $idBudget]);
        }

        //DELETE budget
        public function deleteBudget($idBudget){
            $stmt= $this->pdo->prepare("DELETE FROM budget WHERE idBudget=?");
            return $stmt->execute([$idBudget]);
        }

    }

?>