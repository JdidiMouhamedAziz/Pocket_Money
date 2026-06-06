<?php

class GroupRequest {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createRequest($groupId, $userId, $type, $amount, $note = null) {
        $stmt = $this->pdo->prepare("INSERT INTO grouprequest (groupId, userId, type, amount, note) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$groupId, $userId, $type, $amount, $note]);
    }

    public function findRequestById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM grouprequest WHERE idRequest = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateRequestStatus($id, $status, $reviewedBy) {
        $stmt = $this->pdo->prepare("UPDATE grouprequest SET status = ?, reviewedBy = ?, reviewedAt = CURRENT_TIMESTAMP() WHERE idRequest = ?");
        return $stmt->execute([$status, $reviewedBy, $id]);
    }

    public function findPendingRequestsForOwner($ownerId) {
        $stmt = $this->pdo->prepare("SELECT gr.*, g.name AS groupName, u.name AS userName, u.lastName AS userLastName, u.email FROM grouprequest gr INNER JOIN `group` g ON g.idGroup = gr.groupId INNER JOIN users u ON u.id = gr.userId INNER JOIN groupmember owner ON owner.groupId = gr.groupId AND owner.userId = ? AND owner.role = 'owner' AND owner.status = 'approved' WHERE gr.status = 'pending'");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>