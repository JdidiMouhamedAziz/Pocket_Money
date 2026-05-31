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

            $stmt = $this->pdo->prepare("INSERT INTO group(name, description, budget, theme, spent, invitCode, budgetId) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([$name,$description,$budget,$theme,$spent,$inviteCode,$budgetId]);
        }

        // find group by name
        public function findGroupByName($name){
            $stmt = $this->pdo->prepare("SELECT * FROM group WHERE name LIKE");
            $stmt->execute(["%".$name."%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        }

        //frind FRoup By budgetId
        public function findGroupByBudgetId($budgetId){
            $stmt = $this->pdo->prepare("SELECT * FROM group WHERE budgetId=?");
            $stmt->execute([$budgetId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Group ById
        public function findGroupById($id){
            $stmt = $this->pdo->prepare("SELECT * FROM group WHERE idGroup=?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        //find All Groups
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //Update Group
        public function updateGroup($idGroup,$name,$description,$budget,$theme,$spent,$invitCode,$budgetId){
            $stmt = $this->pdo->prepare("UPDATE group SET name=?,description=?,budget=?,theme=?,spent=?,invitCode=?,budgetId=? WHERE idGroup=?");
            $invitCode= $this->generateInvitCode($name);
            return $stmt->execute([$name,$description,$budget,$theme,$spent,$invitCode,$budgetId,$idGroup]);
        }

        //delete Group
        public function deleteGroup($idGroup){
            $stmt = $this->pdo->prepare("DELETE FROM group WHERE idGroup=?");
            return $stmt->execute([$idGroup]);
        }
    }

?>