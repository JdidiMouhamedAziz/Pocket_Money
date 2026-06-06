<?php

    class Transaction{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //create transaction
        public function createTransaction($description,$transCategory,$date,$note,$amout,$transType,$userId,$categoryId){
            $stmt=$this->pdo->prepare("INSERT INTO transaction (description,transCategory,date,note,amout,transType,userId,categoryId) VALUES (?,?,?,?,?,?,?,?)");
            return $stmt->execute([$description,$transCategory,$date, $note,$amout,$transType,$userId,$categoryId]);
        }

        //find Transection by trans category
        public function findTransactionByTransCategory($transCategory){
            $stmt=$this->pdo->prepare("SELECT * FROM transaction WHERE transCategory=?");
            $stmt->execute([$transCategory]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find GTransaction by date
        public function findTransactionByDate($date){
            $stmt=$this->pdo->prepare("SELECT * FROM transaction WHERE date=?");
            $stmt->execute([$date]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Transaction by transType
        public function findtransactionByTransType($transType){
            $stmt=$this->pdo->prepare("SELECT * FROM transaction WHERE transType=?");
            $stmt->execute([$transType]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Transaction by user id
        public function findTransactionByUserId($userId){
            $stmt=$this->pdo->prepare("SELECT t.*, bt.budgetId, b.note AS budgetNote, b.`limit` AS budgetLimit FROM transaction t LEFT JOIN budgettransaction bt ON bt.transactionId = t.idTransaction LEFT JOIN budget b ON b.idBudget = bt.budgetId WHERE t.userId=?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // find transaction by id
        public function findTransactionById($idTransaction){
            $stmt=$this->pdo->prepare("SELECT t.*, bt.budgetId, b.note AS budgetNote, b.`limit` AS budgetLimit FROM transaction t LEFT JOIN budgettransaction bt ON bt.transactionId = t.idTransaction LEFT JOIN budget b ON b.idBudget = bt.budgetId WHERE t.idTransaction=?");
            $stmt->execute([$idTransaction]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // find transaction by categoryid
        public function findTransactionByCategoryId($categoryId){
            $stmt=$this->pdo->prepare("SELECT * FROM transaction WHERE categoryId=?");
            $stmt->execute([$categoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // update transaction
        public function updateTransaction($idTransaction,$description,$transCategory,$date,$note,$amout,$transType,$categoryId){
            $stmt = $this->pdo->prepare("UPDATE transaction SET description=?, transCategory=?, date=?, note=?, amout=?, transType=?, categoryId=? WHERE idTransaction=?");
            return $stmt->execute([$description,$transCategory,$date,$note,$amout,$transType,$categoryId,$idTransaction]);
        }

        // delete transaction
        public function deleteTransaction($idTransaction){
            $stmt = $this->pdo->prepare("DELETE FROM transaction WHERE idTransaction=?");
            return $stmt->execute([$idTransaction]);
        }
    }

?>