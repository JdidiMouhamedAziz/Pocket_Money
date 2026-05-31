<?php

    class GroupMember{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //create MEMeber
        public function createMember($groupId,$userId,$role){
            $stmt = $this->pdo->prepare("INSERT INTO groupMember (groupId,userId,role) VALUES (?,?,?)");
            return $stmt->executr([$groupId,$userId,$role]);
        }

        //update Role
        public function updateMember($groupId,$userId,$role){
            $stmt = $this->pdo->prepare("UPDATE groupMember SET role=? WHERE groupId=? AND userId=?");
            return $stmt->execute([$role,$groupId,$userId]);
        }

        //fing$d Group By user Role
        public function findGroupByRole($userId,$role){
            $stmt = $this->pdo->prepare("SELECT * FROM groupMember WHERE userId=? AND role=?");
            $stmt->execute([$userId,$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find users by group 
        public function findUsersByGroup($groupId){
            $stmt = $this->pdo->prepare("SELECT * FROM grouMember WHERE groupId=?");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find group by user
        public function findGroupByUser($userId){
            $stmt = $this->pdo->prepare("SELECT * FROM groupMember WHERE userId=?");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // delete groupMember
        public function deleteGroupMember($groupId,$userId){
            $stmt = $this->pdo->prepare("DELETE FROM groupMember WHERE groupId=? AND userId=?");
            return $stmt->execute([$groupId,$userId]);
        }
    }

?>