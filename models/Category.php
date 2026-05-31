<?php

    class Category{
        private $pdo;
        public function __construct($pdo){
            $this->pdo = $pdo;
        }

        //create category
        public function createCategory($name,$type){
            $stmt = $this->pdo->prepare("INSERT INTO category (name,type) VALUES (?,?)");
            return $stmt->execute([$name,$type]);
        }

        //find Category By Neme
        public function findCategoryByName($name){
            $stmt = $this->pdo->prepare("SELECT * FROM category WHERE name LIKE");
            $stmt->execute(["%".$name."%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //find Category B Id
        public function findcategoryById($id){
            $stmt = $this->pdo->prepare("SELECT * FROM category WHERE idCategory=?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // find All Categories
        public function findAllCategories(){
            $stmt = $this->pdo->prepare("SELECT * FROM category ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        //Ypdate Ctagory
        public function updateCategory($id,$name,$type){
            $stmt = $this->pdo->prepare("UPDATE category SET name=?,type=? WHERE idCategory=?");
            return $stmt->execute([$name,$type,$id]);
        }

        //delete category
        public function deleteCategory($id){
            $stmt = $this->pdo->prepare("DELETE FROM category WHERE idCategory=?");
            $stmt->execute([$id]);
        }
    }

?>