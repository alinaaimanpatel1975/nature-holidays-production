<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Customer
{
    public function __construct(private PDO $db) {}

    public function paginate(string $search = '', int $page = 1, int $perPage = 15): array
    {
        $where = '';
        $params = [];
        if ($search !== '') {
            $where = 'WHERE c.name LIKE :name_term OR c.email LIKE :email_term OR c.phone LIKE :phone_term OR c.city LIKE :city_term';
            $term = '%' . $search . '%';
            $params = ['name_term' => $term, 'email_term' => $term, 'phone_term' => $term, 'city_term' => $term];
        }
        $count = $this->db->prepare("SELECT COUNT(*) FROM customers c {$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare("SELECT c.*, (SELECT COUNT(*) FROM enquiries e WHERE e.customer_id = c.id) AS enquiry_count FROM customers c {$where} ORDER BY c.created_at DESC LIMIT :limit OFFSET :offset");
        foreach ($params as $key => $value) $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO customers (name, email, phone, alternate_phone, address, city, state, country, source, notes) VALUES (:name, :email, :phone, :alternate_phone, :address, :city, :state, :country, :source, :notes)');
        $stmt->execute($this->fields($data));
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $values = $this->fields($data);
        $values['id'] = $id;
        $stmt = $this->db->prepare('UPDATE customers SET name=:name, email=:email, phone=:phone, alternate_phone=:alternate_phone, address=:address, city=:city, state=:state, country=:country, source=:source, notes=:notes WHERE id=:id');
        $stmt->execute($values);
    }

    private function fields(array $data): array
    {
        return [
            'name' => trim((string) ($data['name'] ?? '')),
            'email' => strtolower(trim((string) ($data['email'] ?? ''))),
            'phone' => $this->nullable($data['phone'] ?? null),
            'alternate_phone' => $this->nullable($data['alternate_phone'] ?? null),
            'address' => $this->nullable($data['address'] ?? null),
            'city' => $this->nullable($data['city'] ?? null),
            'state' => $this->nullable($data['state'] ?? null),
            'country' => trim((string) ($data['country'] ?? 'India')) ?: 'India',
            'source' => $this->nullable($data['source'] ?? null),
            'notes' => $this->nullable($data['notes'] ?? null),
        ];
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
