<?php

namespace App\Models;
use PDO;

class Package
{
    private \PDO $db;

    private const STATUSES = ['draft', 'published', 'archived'];
    private const CURRENCIES = ['INR', 'USD', 'AED', 'EUR', 'GBP'];

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public static function currencies(): array
    {
        return self::CURRENCIES;
    }

    public function all(string $search = '', string $status = ''): array
    {
        $where = [];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.name LIKE :name_search OR p.slug LIKE :slug_search OR p.short_description LIKE :description_search)';
            $term = '%' . $search . '%';
            $params['name_search'] = $term;
            $params['slug_search'] = $term;
            $params['description_search'] = $term;
        }

        if (in_array($status, self::STATUSES, true)) {
            $where[] = 'p.status = :status_filter';
            $params['status_filter'] = $status;
        }

        $sql = "
            SELECT p.*, sc.name AS category_name,
                   GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') AS destination_names
            FROM packages p
            LEFT JOIN service_categories sc ON sc.id = p.category_id
            LEFT JOIN package_destinations pd ON pd.package_id = p.id
            LEFT JOIN destinations d ON d.id = pd.destination_id
        ";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY p.id ORDER BY p.updated_at DESC, p.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, sc.name AS category_name
            FROM packages p
            LEFT JOIN service_categories sc ON sc.id = p.category_id
            WHERE p.id = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $package = $stmt->fetch();

        if (!$package) {
            return null;
        }

        $destinations = $this->db->prepare("
            SELECT d.id, d.name, d.state, d.country
            FROM package_destinations pd
            INNER JOIN destinations d ON d.id = pd.destination_id
            WHERE pd.package_id = :package_id
            ORDER BY d.name
        ");
        $destinations->execute(['package_id' => $id]);
        $package['destinations'] = $destinations->fetchAll();

        return $package;
    }

    public function categories(): array
    {
        $stmt = $this->db->query("
            SELECT id, name
            FROM service_categories
            WHERE is_active = 1
            ORDER BY sort_order, name
        ");

        return $stmt->fetchAll();
    }

    
public function destinations(): array
{
    $stmt = $this->db->query("
        SELECT
            id,
            name,
            slug,
            state,
            country,
            description,
            subtitle,
            about,
            planning,
            source_url,
            source_name,
            image,
            is_featured
        FROM destinations
        WHERE is_active = 1
        ORDER BY is_featured DESC, country, state, name
    ");

    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$destinations) {
        return [];
    }

    $ids = array_column($destinations, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Load all active attractions for these destinations.
    $attractionQuery = $this->db->prepare("
        SELECT
            id,
            destination_id,
            name,
            slug,
            description,
            image,
            image_alt,
            photo_author,
            credit_url,
            map_url,
            sort_order
        FROM destination_attractions
        WHERE is_active = 1
        AND destination_id IN ($placeholders)
        ORDER BY sort_order, id
    ");

    $attractionQuery->execute($ids);
    $attractions = $attractionQuery->fetchAll(PDO::FETCH_ASSOC);

    // Load all active activities for these destinations.
    $activityQuery = $this->db->prepare("
        SELECT
            id,
            destination_id,
            title,
            description,
            sort_order
        FROM destination_activities
        WHERE is_active = 1
        AND destination_id IN ($placeholders)
        ORDER BY sort_order, id
    ");

    $activityQuery->execute($ids);
    $activities = $activityQuery->fetchAll(PDO::FETCH_ASSOC);

    // Group attractions and activities by destination.
    $attractionsByDestination = [];
    $activitiesByDestination = [];

    foreach ($attractions as $attraction) {
        $destinationId = (int)$attraction['destination_id'];
        $attractionsByDestination[$destinationId][] = $attraction;
    }

    foreach ($activities as $activity) {
        $destinationId = (int)$activity['destination_id'];
        $activitiesByDestination[$destinationId][] = $activity;
    }

    // Attach the related content to each destination.
    foreach ($destinations as &$destination) {
        $id = (int)$destination['id'];

        $destination['attractions'] =
            $attractionsByDestination[$id] ?? [];

        $destination['activities'] =
            $activitiesByDestination[$id] ?? [];
    }

    unset($destination);

    return $destinations;
}


    public function create(array $data, array $destinationIds): int
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO packages
                    (category_id, name, slug, short_description, description,
                     duration_days, duration_nights, indicative_price, currency,
                     image, status, is_featured)
                VALUES
                    (:category_id, :name, :slug, :short_description, :description,
                     :duration_days, :duration_nights, :indicative_price, :currency,
                     :image, :status, :is_featured)
            ");
            $stmt->execute($data);
            $id = (int) $this->db->lastInsertId();

            $this->syncDestinations($id, $destinationIds);
            $this->db->commit();

            return $id;
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    public function update(int $id, array $data, array $destinationIds): void
    {
        $this->db->beginTransaction();

        try {
            $data['id'] = $id;

            $stmt = $this->db->prepare("
                UPDATE packages
                SET category_id = :category_id,
                    name = :name,
                    slug = :slug,
                    short_description = :short_description,
                    description = :description,
                    duration_days = :duration_days,
                    duration_nights = :duration_nights,
                    indicative_price = :indicative_price,
                    currency = :currency,
                    image = :image,
                    status = :status,
                    is_featured = :is_featured
                WHERE id = :id
            ");
            $stmt->execute($data);

            $this->syncDestinations($id, $destinationIds);
            $this->db->commit();
        } catch (\Throwable $error) {
            $this->db->rollBack();
            throw $error;
        }
    }

    private function syncDestinations(int $packageId, array $destinationIds): void
    {
        $delete = $this->db->prepare(
            'DELETE FROM package_destinations WHERE package_id = :package_id'
        );
        $delete->execute(['package_id' => $packageId]);

        if (!$destinationIds) {
            return;
        }

        $insert = $this->db->prepare("
            INSERT INTO package_destinations (package_id, destination_id)
            SELECT :package_id, id
            FROM destinations
            WHERE id = :destination_id AND is_active = 1
        ");

        foreach ($destinationIds as $destinationId) {
            $insert->execute([
                'package_id' => $packageId,
                'destination_id' => $destinationId,
            ]);
        }
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT id FROM packages WHERE slug = :slug';
        $params = ['slug' => $slug];

        if ($ignoreId !== null) {
            $sql .= ' AND id <> :ignore_id';
            $params['ignore_id'] = $ignoreId;
        }

        $sql .= ' LIMIT 1';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (bool) $stmt->fetchColumn();
    }

    public function categoryExists(?int $id): bool
    {
        if ($id === null) {
            return true;
        }

        $stmt = $this->db->prepare(
            'SELECT id FROM service_categories WHERE id = :id AND is_active = 1 LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        return (bool) $stmt->fetchColumn();
    }
}
