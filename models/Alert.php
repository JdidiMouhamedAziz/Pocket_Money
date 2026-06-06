<?php

    class Alert{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        public function createAlert($name,$message,$about,$userId,$bugetId){
            $stmt= $this->pdo->prepare("INSERT INTO alert (name,message,about,userId,budgetId) VALUES (?,?,?,?,?)");
            return $stmt->execute([$name,$message,$about,$userId,$bugetId]);
        }

        // findAlert By About
        public function findAlertByAbout($about){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE about=?");
            $stmt->execute([$about]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find alert By id
        public function findAlertById($id){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE idAlert=?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        //find Alert By userId
        public function findAlertByUserId($userId){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE userId=?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find alert By Read Status isReaded=0 for unreaded isReaded=1 for readed
        public function findAlertByReadStatus($isReaded, $userId){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE isReaded=? AND userId=?");
            $stmt->execute([$isReaded,$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Alerts By budgetId
        public function findAlertByBudgetId( $budgetId , $userId){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE budgetId=? AND userId=?");
            $stmt->execute([$budgetId,$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function deleteAlert($id){
            $stmt = $this->pdo->prepare("DELETE FROM alert WHERE idAlert = ?");
            return $stmt->execute([$id]);
        }

        // find alerts by budgetid and readSataus
        public function findAlertByBudgetIdAndReadStatus($budgetId,$userId,$isReaded){
            $stmt= $this->pdo->prepare("SELECT * FROM alert WHERE budgetId=? AND userId=? AND isReaded=?");
            $stmt->execute([$budgetId,$userId,$isReaded]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //Read ALert
        public function readAlert($id){
            $stmt= $this->pdo->prepare("UPDATE alert SET isReaded=? WHERE idAlert=?");
            return $stmt->execute([1, $id]);
        }

        // Read All alerts
        public function readAllAlert($userId){
            $stmt= $this->pdo->prepare("UPDATE alert SET isReaded=? WHERE userId=?");
            return $stmt->execute([1,$userId]);
        }
    }

?>