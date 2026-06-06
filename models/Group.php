<?php

    class Group{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //GENEret Group invit COde

        public function generateInvitCode($name){
            // Générer les initiales du nom du groupe
            $words = explode(' ', trim($name));
            $prefix = '';

            foreach ($words as $word) {
                if (!empty($word)) {
                    $prefix .= strtoupper($word[0]);
                }
            }
            return $prefix . '-' . random_int(1000000, 9999999);

        }

        // Create group
        public function createGroup($name, $description, $budget, $spent, $theme, $budgetId)
{
            

            // Nombre aléatoire à 7 chiffres
            $inviteCode = $this->generateInvitCode($name);

            $stmt = $this->pdo->prepare("INSERT INTO `group`(name, description, budget, theme, spent, invitCode, budgetId) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $created = $stmt->execute([$name, $description, $budget, $theme, $spent, $inviteCode, $budgetId]);

            if ($created && isset($_SESSION['user']['id'])) {
                $groupId = $this->pdo->lastInsertId();
                $memberStmt = $this->pdo->prepare("INSERT INTO groupmember (groupId, userId, role, status) VALUES (?, ?, ?, ?)");
                $memberStmt->execute([$groupId, $_SESSION['user']['id'], 'owner', 'approved']);
            }

            return $created;
        }

        // find group by name
        public function findGroupByName($name){
            $stmt = $this->pdo->prepare("SELECT * FROM `group` WHERE name LIKE");
            $stmt->execute(["%".$name."%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }

        //frind FRoup By budgetId
        public function findGroupByBudgetId($budgetId){
            $stmt = $this->pdo->prepare("SELECT * FROM `group` WHERE budgetId=?");
            $stmt->execute([$budgetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function findGroupByInviteCode($inviteCode){
            $stmt = $this->pdo->prepare("SELECT * FROM `group` WHERE invitCode = ?");
            $stmt->execute([$inviteCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        //find Group ById
        public function findGroupById($id){
            $stmt = $this->pdo->prepare("SELECT * FROM `group` WHERE idGroup=?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        public function isOwner($groupId, $userId){
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM groupmember WHERE groupId=? AND userId=? AND role='owner' AND status='approved'");
            $stmt->execute([$groupId, $userId]);
            return (int) $stmt->fetchColumn() > 0;
        }

        //find All Groups
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Update Group
        public function updateGroup($idGroup,$name,$description,$budget,$theme,$spent,$invitCode,$budgetId){
            $stmt = $this->pdo->prepare("UPDATE `group` SET name=?,description=?,budget=?,theme=?,spent=?,invitCode=?,budgetId=? WHERE idGroup=?");
            $invitCode= $this->generateInvitCode($name);
            return $stmt->execute([$name,$description,$budget,$theme,$spent,$invitCode,$budgetId,$idGroup]);
        }

        //delete Group
        public function deleteGroup($idGroup){
            $this->pdo->beginTransaction();

            $memberStmt = $this->pdo->prepare("DELETE FROM groupMember WHERE groupId=?");
            $memberStmt->execute([$idGroup]);

            $stmt = $this->pdo->prepare("DELETE FROM `group` WHERE idGroup=?");
            $deleted = $stmt->execute([$idGroup]);

            if ($deleted) {
                $this->pdo->commit();
                return true;
            }

            $this->pdo->rollBack();
            return false;
        }
    }

?>