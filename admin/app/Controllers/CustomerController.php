<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Customer;
use PDOException;

class CustomerController extends Controller
{
    private function model(): Customer { return new Customer(\Database::connect()); }

    public function index(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.view')) { http_response_code(403); exit('You do not have permission to view customers.'); }
        $search = trim((string) ($_GET['q'] ?? ''));
        $data = $this->model()->paginate($search, (int) ($_GET['page'] ?? 1));
        $this->view('customers/index', ['data' => $data, 'search' => $search]);
    }

    public function create(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.manage')) { http_response_code(403); exit('You do not have permission to add customers.'); }
        $this->view('customers/form', ['customer' => [], 'mode' => 'create', 'error' => null]);
    }

    public function store(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $error = $this->validate($_POST);
        if ($error) { $this->view('customers/form', ['customer' => $_POST, 'mode' => 'create', 'error' => $error]); return; }
        try {
            $id = $this->model()->create($_POST);
            $this->redirect('/admin/customers/view?id=' . $id);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->view('customers/form', ['customer' => $_POST, 'mode' => 'create', 'error' => 'The customer could not be saved. Check the details and try again.']);
        }
    }

    public function show(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.view')) { http_response_code(403); exit('Forbidden'); }
        $id = $this->id();
        $customer = $this->model()->find($id);
        if (!$customer) { http_response_code(404); $this->view('errors/404'); return; }
        $stmt = \Database::connect()->prepare('SELECT id, enquiry_number, subject, status, priority, created_at FROM enquiries WHERE customer_id=? ORDER BY created_at DESC LIMIT 20');
        $stmt->execute([$id]);
        $this->view('customers/show', ['customer' => $customer, 'enquiries' => $stmt->fetchAll()]);
    }

    public function edit(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.manage')) { http_response_code(403); exit('Forbidden'); }
        $customer = $this->model()->find($this->id());
        if (!$customer) { http_response_code(404); $this->view('errors/404'); return; }
        $this->view('customers/form', ['customer' => $customer, 'mode' => 'edit', 'error' => null]);
    }

    public function update(): void
    {
        Auth::requireLogin();
        if (!Auth::can('customers.manage')) { http_response_code(403); exit('Forbidden'); }
        $this->verifyPost();
        $id = $this->id();
        $customer = $this->model()->find($id);
        if (!$customer) { http_response_code(404); $this->view('errors/404'); return; }
        $error = $this->validate($_POST);
        if ($error) { $this->view('customers/form', ['customer' => array_merge($customer, $_POST), 'mode' => 'edit', 'error' => $error]); return; }
        try {
            $this->model()->update($id, $_POST);
            $this->redirect('/admin/customers/view?id=' . $id);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $this->view('customers/form', ['customer' => array_merge($customer, $_POST), 'mode' => 'edit', 'error' => 'The customer could not be updated. Please try again.']);
        }
    }

    private function validate(array $data): ?string
    {
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        if ($name === '' || strlen($name) > 600) return 'Enter a customer name (maximum 150 characters).';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) return 'Enter a valid email address.';
        return null;
    }
    private function verifyPost(): void { if (!Csrf::verify(is_string($_POST['_csrf'] ?? null) ? $_POST['_csrf'] : null)) { http_response_code(419); exit('Session expired. Refresh and try again.'); } }
    private function id(): int { $id = filter_var($_GET['id'] ?? $_POST['id'] ?? null, FILTER_VALIDATE_INT); if (!$id || $id < 1) { http_response_code(404); exit('Not found.'); } return $id; }
    private function redirect(string $path): never { header('Location: ' . APP_BASE_PATH . $path); exit; }
}
