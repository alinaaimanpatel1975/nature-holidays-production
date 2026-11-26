<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Followup
{
    public function __construct(private PDO $db) {}

    public function forEnquiry(int $enquiryId): array
    {
        $stmt = $this->db->prepare('SELECT f.*, a.name AS assignee_name, c.name AS creator_name FROM followups f LEFT JOIN users a ON a.id=f.assigned_to LEFT JOIN users c ON c.id=f.created_by WHERE f.enquiry_id=? ORDER BY f.due_at ASC, f.id DESC');
        $stmt->execute([$enquiryId]);
        return $stmt->fetchAll();
    }

    public function create(int $enquiryId, int $userId, array $data): void
    {
        $due = trim((string) ($data['due_at'] ?? ''));
        $date = \DateTime::createFromFormat('Y-m-d\TH:i', $due);
        if (!$date || $date->format('Y-m-d\TH:i') !== $due) throw new \InvalidArgumentException('Choose a valid follow-up date and time.');
        $type = in_array(($data['type'] ?? ''), ['call', 'email', 'whatsapp', 'meeting', 'other'], true) ? $data['type'] : 'call';
        $assigned = filter_var($data['assigned_to'] ?? '', FILTER_VALIDATE_INT);
        $assigned = $assigned !== false && $assigned > 0 ? $assigned : $userId;
        $staffCheck = $this->db->prepare("SELECT id FROM users WHERE id=? AND status='active' LIMIT 1");
        $staffCheck->execute([$assigned]);
        if (!$staffCheck->fetchColumn()) throw new \InvalidArgumentException('Choose an active team member for this follow-up.');
        $stmt = $this->db->prepare('INSERT INTO followups (enquiry_id, assigned_to, created_by, due_at, type, notes, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$enquiryId, $assigned, $userId, $date->format('Y-m-d H:i:s'), $type, trim((string) ($data['notes'] ?? '')) ?: null, 'pending']);
    }

    public function complete(int $id, int $enquiryId): bool
    {
        $stmt = $this->db->prepare("UPDATE followups SET status='completed', completed_at=NOW() WHERE id=? AND enquiry_id=? AND status='pending'");
        $stmt->execute([$id, $enquiryId]);
        return $stmt->rowCount() > 0;
    }
}
