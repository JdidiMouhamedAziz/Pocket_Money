<?php

    class BudgetTransaction{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //creete budgetTransaction
        public function createBudgettransaction($budgetId,$transactionId){
            $stmt = $this->pdo->prepare("INSERT INTO budgettransaction VALUES(?,?)");
            return $stmt->execute([$budgetId,$transactionId]);
        }

        //findTransactionn by budget id
        public function findTransactionByBudgetId( $budgetId ){
            $stmt = $this->pdo->prepare("SELECT FROM budgettransaction WHERE budgetId=?");
            $stmt->execute([$budgetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // find budget by transaction
        public function findbugetBytransactionId( $transactionId ){
            $stmt = $this->pdo->prepare("SELECT * FROM budgettransaction WHERE transactionId=?");
            $stmt->execute([$transactionId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

?>