<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Customer;
use App\Models\Enquiry;
use App\Models\Followup;
use PDO;
use Throwable;

class EnquiryController extends Controller
{
    private function db(): PDO { return \Database::connect(); }
    private function enquiryModel(): Enquiry { return new Enquiry($this->db()); }

    public function index(): void
    {
        Auth::requireLogin();
        if (!Auth::can('enquiries.view')) { http_response_code(403); exit('You do not have permission to view enquiries.'); }
        $search = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $data = $this->enquiryModel()->paginate($search, $status, (int) ($_GET['page'] ?? 1));
        $this->view('enquiries/index', ['data' => $data, 'search' => $search, 'status' => $status]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        if (!Auth::can('enquiries.manage')) { http_response_code(403); exit('Forbidden'); }
        $customers = $this->db()->query('SELECT id, name, email, phone FROM customers ORDER BY name ASC')->fetchAll();
        $staff = $this->staff();
        $this->view('enquiries/form', ['customers' => $customers, 'staff' => $staff, 'values' => [], 'error' => null]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!Auth::can('enquiries.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $values = $_POST;
        $name = trim((string) ($values['customer_name'] ?? ''));
        $email = trim((string) ($values['customer_email'] ?? ''));
        $customerId = filter_var($values['customer_id'] ?? '', FILTER_VALIDATE_INT);
        if (!$customerId && ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $this->renderCreate($values, 'Choose an existing customer or enter a valid new customer name and email.'); return;
        }
        if (!$this->validDates($values['travel_start_date'] ?? '', $values['travel_end_date'] ?? '')) {
            $this->renderCreate($values, 'The travel end date cannot be earlier than the start date.'); return;
        }
        $db = $this->db();
        try {
            $db->beginTransaction();
            if ($customerId) {
                $check = $db->prepare('SELECT id FROM customers WHERE id=?');
                $check->execute([$customerId]);
                if (!$check->fetchColumn()) throw new \InvalidArgumentException('The selected customer no longer exists.');
                $resolvedCustomerId = (int) $customerId;
            } else {
                $resolvedCustomerId = (new Customer($db))->create([
                    'name' => $name, 'email' => $email,
                    'phone' => $values['customer_phone'] ?? '',
                    'source' => $values['source'] ?? 'Enquiry',
                    'country' => 'India'
                ]);
            }
            $id = $this->enquiryModel()->create($values, $resolvedCustomerId);
            $db->commit();
            $this->redirect('/admin/enquiries/view?id=' . $id);
        } catch (Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log($e->getMessage());
            $this->renderCreate($values, $e instanceof \InvalidArgumentException ? $e->getMessage() : 'The enquiry could not be saved. Please check the details and try again.');
        }
    }

    public function show(): void
    {
        Auth::requireLogin();
        if (!Auth::can('enquiries.view')) { http_response_code(403); exit('Forbidden'); }
        $id = $this->id();
        $enquiry = $this->enquiryModel()->find($id);
        if (!$enquiry) { http_response_code(404); $this->view('errors/404'); return; }
        $followups = (new Followup($this->db()))->forEnquiry($id);
        $staff = $this->staff();
        $this->view('enquiries/show', ['enquiry' => $enquiry, 'followups' => $followups, 'staff' => $staff]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        if (!Auth::can('enquiries.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $id = $this->id();
        if (!$this->enquiryModel()->find($id)) { http_response_code(404); $this->view('errors/404'); return; }
        if (!$this->validDates($_POST['travel_start_date'] ?? '', $_POST['travel_end_date'] ?? '')) { $_SESSION['crm_error'] = 'The travel end date cannot be earlier than the start date.'; $this->redirect('/admin/enquiries/view?id=' . $id); }
        try {
            $this->enquiryModel()->update($id, $_POST);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['crm_error'] = $e->getMessage();
        }
        $this->redirect('/admin/enquiries/view?id=' . $id);
    }

    public function addFollowup(): void
    {
        Auth::requireLogin();
        if (!Auth::can('followups.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $id = $this->id();
        if (!$this->enquiryModel()->find($id)) { http_response_code(404); exit('Not found.'); }
        try {
            (new Followup($this->db()))->create($id, (int) Auth::user()['id'], $_POST);
        } catch (\InvalidArgumentException $e) {
            $_SESSION['crm_error'] = $e->getMessage();
        }
        $this->redirect('/admin/enquiries/view?id=' . $id . '#follow-ups');
    }

    public function completeFollowup(): void
    {
        Auth::requireLogin();
        if (!Auth::can('followups.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $enquiryId = $this->id();
        $followupId = filter_var($_POST['followup_id'] ?? null, FILTER_VALIDATE_INT);
        if ($followupId && $followupId > 0) (new Followup($this->db()))->complete((int) $followupId, $enquiryId);
        $this->redirect('/admin/enquiries/view?id=' . $enquiryId . '#follow-ups');
    }

    private function renderCreate(array $values, string $error): void
    {
        $customers = $this->db()->query('SELECT id, name, email, phone FROM customers ORDER BY name ASC')->fetchAll();
        $this->view('enquiries/form', ['customers' => $customers, 'staff' => $this->staff(), 'values' => $values, 'error' => $error]);
    }
    private function staff(): array { return $this->db()->query("SELECT id, name FROM users WHERE status='active' ORDER BY name ASC")->fetchAll(); }
    private function validDates(mixed $start, mixed $end): bool
    {
        $s = trim((string) $start); $e = trim((string) $end);
        $valid = static function (string $value): bool { if ($value === '') return true; $date = \DateTime::createFromFormat('!Y-m-d', $value); return $date && $date->format('Y-m-d') === $value; };
        if (!$valid($s) || !$valid($e)) return false;
        return $s === '' || $e === '' || $e >= $s;
    }
    private function verifyPost(): void { if (!Csrf::verify(is_string($_POST['_csrf'] ?? null) ? $_POST['_csrf'] : null)) { http_response_code(419); exit('Session expired. Refresh and try again.'); } }
    private function id(): int { $id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT); if (!$id || $id < 1) { http_response_code(404); exit('Not found.'); } return $id; }
   
    private function redirect(string $path): never { header('Location: ' . APP_BASE_PATH . $path); exit; }


public function storePublic(): void
{
    header('Content-Type: application/json; charset=utf-8');

    // Read the JSON submitted by the public React form.
    $payload = json_decode(file_get_contents('php://input'), true);

    if (!is_array($payload)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request data.'
        ]);
        return;
    }

    $customer = is_array($payload['customer'] ?? null)
        ? $payload['customer']
        : [];

    $trip = is_array($payload['trip'] ?? null)
        ? $payload['trip']
        : [];

    $name = trim((string) ($customer['name'] ?? ''));
    $email = strtolower(trim((string) ($customer['email'] ?? '')));
    $phone = trim((string) ($customer['phone'] ?? ''));

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Please provide a valid name and email address.'
        ]);
        return;
    }

    $startDate = trim((string) ($trip['travelDate'] ?? ''));
    $endDate = trim((string) ($trip['endDate'] ?? ''));

    if (!$this->validDates($startDate, $endDate)) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Please check your travel dates.'
        ]);
        return;
    }

    $travellers = filter_var(
        $trip['travellers'] ?? 1,
        FILTER_VALIDATE_INT
    );

    if ($travellers === false || $travellers < 1) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid number of travellers.'
        ]);
        return;
    }

    $packageName = trim((string) ($trip['packageName'] ?? ''));
    $tripType = trim((string) ($trip['tripType'] ?? ''));
    $destination = trim((string) ($trip['destination'] ?? ''));
    $departureCity = trim((string) ($trip['departureCity'] ?? ''));
    $requests = trim((string) ($trip['requests'] ?? ''));

    $subject = $packageName !== ''
        ? $packageName
        : ($destination !== '' ? $destination : 'Website travel enquiry');

    // Store additional trip information in the enquiry message.
    $messageParts = [];

    if ($tripType !== '') {
        $messageParts[] = 'Trip type: ' . $tripType;
    }

    if ($destination !== '') {
        $messageParts[] = 'Destination: ' . $destination;
    }

    if ($departureCity !== '') {
        $messageParts[] = 'Departure city: ' . $departureCity;
    }

    if ($requests !== '') {
        $messageParts[] = 'Additional requests: ' . $requests;
    }

    $price = is_array($payload['price'] ?? null)
        ? $payload['price']
        : [];

    $budget = $price['amount'] ?? null;

    if (!is_numeric($budget) || (float) $budget <= 0) {
        $budget = null;
    }

    
    // Include selected cart items in the enquiry message.
    $cart = is_array($payload['cart']['items'] ?? null)
        ? $payload['cart']['items']
        : [];

    foreach ($cart as $item) {
        if (!is_array($item)) {
            continue;
        }

        $itemName = trim((string) ($item['name'] ?? $item['title'] ?? ''));

        if ($itemName === '') {
            continue;
        }

        $itemDestination = trim((string) ($item['destination'] ?? ''));
        $itemPackage = trim((string) ($item['packageName'] ?? ''));
        $quantity = max(1, (int) ($item['quantity'] ?? 1));
        $itemDate = trim((string) ($item['date'] ?? ''));

        $messageParts[] = 'Selected experience: ' . $itemName;

        if ($itemDestination !== '') {
            $messageParts[] = 'Experience destination: ' . $itemDestination;
        }

        if ($itemPackage !== '') {
            $messageParts[] = 'Experience package: ' . $itemPackage;
        }

        $messageParts[] = 'Experience travellers: ' . $quantity;

        if ($itemDate !== '') {
            $messageParts[] = 'Experience date: ' . $itemDate;
        }
    }


    $message = implode("\n", $messageParts);

    $db = $this->db();

    try {
        $db->beginTransaction();

        // Reuse an existing customer with the same email.
        $customerQuery = $db->prepare(
            'SELECT id FROM customers WHERE email = ? LIMIT 1'
        );
        $customerQuery->execute([$email]);

        $customerId = (int) ($customerQuery->fetchColumn() ?: 0);

        if ($customerId === 0) {
            $customerId = (new Customer($db))->create([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'source' => 'Website',
                'country' => 'India'
            ]);
        }

        // Use the existing Enquiry model and its reference generator.
        $enquiryId = $this->enquiryModel()->create([
            'subject' => $subject,
            'travel_start_date' => $startDate,
            'travel_end_date' => $endDate,
            'traveller_count' => $travellers,
            'budget' => $budget,
            'currency' => 'INR',
            'source' => 'Website',
            'priority' => 'normal',
            'message' => $message
        ], $customerId);

        $enquiry = $this->enquiryModel()->find($enquiryId);

        $db->commit();

        http_response_code(201);

        echo json_encode([
            'success' => true,
            'message' => 'Your enquiry has been submitted successfully.',
            'id' => $enquiryId,
            'reference' => $enquiry['enquiry_number'] ?? null
        ]);

    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        error_log('Public enquiry error: ' . $e->getMessage());

        http_response_code(500);

        echo json_encode([
            'success' => false,
            'message' => 'We could not submit your enquiry. Please try again.'
        ]);
    }
}






}
