<?php
class User {
    private $pdo;


    public function __construct($pdo){
        $this->pdo = $pdo;
    
    }


    //Create User
    public function create($name,$lastname,$email,$password,$role,$status="pending"){
        // hash password
            $hashedpassword=password_hash($password,PASSWORD_DEFAULT);
            // insert into database
            $stmt=$this->pdo->prepare("INSERT INTO users (name,lastName,email,password,role,status) VALUES (?,?,?,?,?,?) 
            ");
            return $stmt->execute([$name,$lastname,$email,$hashedpassword,$role, $status]);
    }

    //Update  User Account Status
    public function updateStatus($id,$status){
        $stmt=$this->pdo->prepare("UPDATE users SET status=? WHERE id=?");
        return $stmt->execute([$status,$id]);
    }

    //Find User By Id
    public function findUserById($id){
        $stmt=$this->pdo->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Find User By email
    public function findUserByEmail($email){
        $stmt=$this->pdo->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Find User By Name
    public function findUserByName($name){
        $stmt=$this->pdo->prepare("SELECT * FROM users WHERE name LIKE");
        $stmt->execute(["%".$name."%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Find User By LastName
    public function findUserByLastName($lastName){
        $stmt=$this->pdo->prepare("SELECT * FROM users WHERE lastName LIKE");
        $stmt->execute(["%".$lastName."%"]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // FindAllUsers
    public function findAllUsers(){
        $stmt=$this->pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

   // Update User
        public function updateUser($id,$name, $lastName, $email, $password = null) {
            // if the password is not null update it 
          if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET name=?,lastName=?, email=?, password=? WHERE id=?");
            return $stmt->execute([$name,$lastName, $email,$hash, $id]);
          } else {
            // else do not update the password
                        $stmt = $this->pdo->prepare("UPDATE users SET name=?, lastName=?, email=? WHERE id=?");
                        return $stmt->execute([$name,$lastName, $email, $id]);
          }
        }

    // DELETE user
    public function deleteUser($id){
        $stmt=$this->pdo->prepare("DELETE FROM users WHERE id=?");
        return $stmt->execute([$id]);
    }

    // Soft delete user by setting status to deleted
    public function softDeleteUser($id){
        return $this->updateStatus($id, 'deleted');
    }
}

?>