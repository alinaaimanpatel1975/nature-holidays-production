<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Enquiry
{
    public const STATUSES = ['new', 'contacted', 'qualified', 'quoted', 'converted', 'lost', 'on_hold'];
    public const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

    public function __construct(private PDO $db) {}

    public function paginate(string $search = '', string $status = '', int $page = 1, int $perPage = 15): array
    {
        $clauses = [];
        $params = [];
        if ($search !== '') {
            $clauses[] = '(e.enquiry_number LIKE :ref_term OR e.subject LIKE :subject_term OR c.name LIKE :name_term OR c.email LIKE :email_term OR c.phone LIKE :phone_term)';
            $term = '%' . $search . '%';
            $params += ['ref_term' => $term, 'subject_term' => $term, 'name_term' => $term, 'email_term' => $term, 'phone_term' => $term];
        }
        if (in_array($status, self::STATUSES, true)) {
            $clauses[] = 'e.status = :status';
            $params['status'] = $status;
        }
        $where = $clauses ? 'WHERE ' . implode(' AND ', $clauses) : '';
        $base = " FROM enquiries e LEFT JOIN customers c ON c.id=e.customer_id LEFT JOIN users u ON u.id=e.assigned_to {$where}";
        $count = $this->db->prepare('SELECT COUNT(*)' . $base);
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $stmt = $this->db->prepare('SELECT e.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone, u.name AS assignee_name' . $base . ' ORDER BY e.created_at DESC LIMIT :limit OFFSET :offset');
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT e.*, c.name AS customer_name, c.email AS customer_email, c.phone AS customer_phone, c.city AS customer_city, u.name AS assignee_name FROM enquiries e LEFT JOIN customers c ON c.id=e.customer_id LEFT JOIN users u ON u.id=e.assigned_to WHERE e.id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data, int $customerId): int
    {
        $number = 'NH-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt = $this->db->prepare('INSERT INTO enquiries (customer_id, assigned_to, enquiry_number, subject, travel_start_date, travel_end_date, traveller_count, budget, currency, source, priority, status, message, internal_notes) VALUES (:customer_id, :assigned_to, :enquiry_number, :subject, :travel_start_date, :travel_end_date, :traveller_count, :budget, :currency, :source, :priority, :status, :message, :internal_notes)');
        $stmt->execute([
            'customer_id' => $customerId,
            'assigned_to' => $this->assignee($data['assigned_to'] ?? null),
            'enquiry_number' => $number,
            'subject' => $this->nullable($data['subject'] ?? null),
            'travel_start_date' => $this->date($data['travel_start_date'] ?? null),
            'travel_end_date' => $this->date($data['travel_end_date'] ?? null),
            'traveller_count' => $this->positiveInt($data['traveller_count'] ?? null),
            'budget' => $this->decimal($data['budget'] ?? null),
            'currency' => in_array(strtoupper(trim((string) ($data['currency'] ?? 'INR'))), ['INR', 'USD', 'AED', 'EUR', 'GBP'], true) ? strtoupper(trim((string) ($data['currency'] ?? 'INR'))) : 'INR',
            'source' => $this->nullable($data['source'] ?? null),
            'priority' => in_array(($data['priority'] ?? ''), self::PRIORITIES, true) ? $data['priority'] : 'normal',
            'status' => 'new',
            'message' => $this->nullable($data['message'] ?? null),
            'internal_notes' => $this->nullable($data['internal_notes'] ?? null),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $status = in_array(($data['status'] ?? ''), self::STATUSES, true) ? $data['status'] : 'new';
        $priority = in_array(($data['priority'] ?? ''), self::PRIORITIES, true) ? $data['priority'] : 'normal';
        $stmt = $this->db->prepare('UPDATE enquiries SET assigned_to=:assigned_to, status=:status, priority=:priority, subject=:subject, travel_start_date=:start_date, travel_end_date=:end_date, traveller_count=:travellers, budget=:budget, source=:source, internal_notes=:notes WHERE id=:id');
        $stmt->execute([
            'assigned_to' => $this->assignee($data['assigned_to'] ?? null),
            'status' => $status,
            'priority' => $priority,
            'subject' => $this->nullable($data['subject'] ?? null),
            'start_date' => $this->date($data['travel_start_date'] ?? null),
            'end_date' => $this->date($data['travel_end_date'] ?? null),
            'travellers' => $this->positiveInt($data['traveller_count'] ?? null),
            'budget' => $this->decimal($data['budget'] ?? null),
            'source' => $this->nullable($data['source'] ?? null),
            'notes' => $this->nullable($data['internal_notes'] ?? null),
            'id' => $id,
        ]);
    }

    private function assignee(mixed $value): ?int
    {
        $id = $this->positiveInt($value);
        if ($id === null) return null;
        $stmt = $this->db->prepare("SELECT id FROM users WHERE id=? AND status='active' LIMIT 1");
        $stmt->execute([$id]);
        if (!$stmt->fetchColumn()) throw new \InvalidArgumentException('Choose an active team member for this enquiry.');
        return $id;
    }

    private function nullable(mixed $v): ?string { $v = trim((string) ($v ?? '')); return $v === '' ? null : $v; }
    private function positiveInt(mixed $v): ?int { if ($v === null || $v === '') return null; $n = filter_var($v, FILTER_VALIDATE_INT); return $n !== false && $n > 0 ? $n : null; }
    private function decimal(mixed $v): ?string { if ($v === null || $v === '') return null; return is_numeric($v) && (float) $v >= 0 ? number_format((float) $v, 2, '.', '') : null; }
    private function date(mixed $v): ?string { $v = trim((string) ($v ?? '')); if ($v === '') return null; $d = \DateTime::createFromFormat('Y-m-d', $v); return $d && $d->format('Y-m-d') === $v ? $v : null; }
}
