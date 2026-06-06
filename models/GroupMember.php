<?php

    class GroupMember{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //create MEMeber
        public function createMember($groupId,$userId,$role,$status = 'pending'){
            $stmt = $this->pdo->prepare("INSERT INTO groupmember (groupId,userId,role,status) VALUES (?,?,?,?)");
            return $stmt->execute([$groupId,$userId,$role,$status]);
        }

        //update Member
        public function updateMember($groupId,$userId,$role, $status = null){
            if ($status !== null) {
                $stmt = $this->pdo->prepare("UPDATE groupmember SET role=?, status=? WHERE groupId=? AND userId=?");
                return $stmt->execute([$role,$status,$groupId,$userId]);
            }
            $stmt = $this->pdo->prepare("UPDATE groupmember SET role=? WHERE groupId=? AND userId=?");
            return $stmt->execute([$role,$groupId,$userId]);
        }

        //find Group By user Role
        public function findGroupByRole($userId,$role){
            $stmt = $this->pdo->prepare("SELECT * FROM groupmember WHERE userId=? AND role=?");
            $stmt->execute([$userId,$role]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find users by group 
        public function findUsersByGroup($groupId){
            $stmt = $this->pdo->prepare("SELECT * FROM groupmember WHERE groupId=?");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function findMemberByGroupAndUser($groupId, $userId){
            $stmt = $this->pdo->prepare("SELECT * FROM groupmember WHERE groupId=? AND userId=?");
            $stmt->execute([$groupId, $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function findPendingByGroup($groupId){
            $stmt = $this->pdo->prepare("SELECT gm.*, u.name AS userName, u.lastName AS userLastName, u.email FROM groupmember gm INNER JOIN users u ON u.id = gm.userId WHERE gm.groupId=? AND gm.status='pending'");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function findPendingRequestsForOwner($ownerId){
            $stmt = $this->pdo->prepare("SELECT gm.*, g.name AS groupName, u.name AS userName, u.lastName AS userLastName, u.email FROM groupmember gm INNER JOIN `group` g ON g.idGroup = gm.groupId INNER JOIN users u ON u.id = gm.userId INNER JOIN groupmember owner ON owner.groupId = gm.groupId AND owner.userId = ? AND owner.role = 'owner' AND owner.status = 'approved' WHERE gm.status = 'pending'");
            $stmt->execute([$ownerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find group by user
        public function findGroupByUser($userId){
            $stmt = $this->pdo->prepare("SELECT * FROM groupmember WHERE userId=? AND status='approved'");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // delete groupMember
        public function deleteGroupMember($groupId,$userId){
            $stmt = $this->pdo->prepare("DELETE FROM groupmember WHERE groupId=? AND userId=?");
            return $stmt->execute([$groupId,$userId]);
        }
    }

?>