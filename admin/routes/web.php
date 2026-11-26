
<?php

use App\Controllers\HomeController;
use App\Controllers\AuthController;

use App\Controllers\AdminController;
use App\Controllers\CustomerController;
use App\Controllers\EnquiryController;
use App\Controllers\PackageController;
use App\Controllers\OperationsController;
use App\Controllers\ManagementController;
use App\Controllers\ApiController;
// Home & Authentication
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

// Admin Dashboard
$router->get('/admin', [AdminController::class, 'index']);

// Customers
$router->get('/admin/customers', [CustomerController::class, 'index']);
$router->get('/admin/customers/create', [CustomerController::class, 'create']);
$router->post('/admin/customers/store', [CustomerController::class, 'store']);
$router->get('/admin/customers/view', [CustomerController::class, 'show']);
$router->get('/admin/customers/edit', [CustomerController::class, 'edit']);
$router->post('/admin/customers/update', [CustomerController::class, 'update']);

// Enquiries & CRM
$router->get('/admin/enquiries', [EnquiryController::class, 'index']);
$router->get('/admin/enquiries/create', [EnquiryController::class, 'create']);
$router->post('/admin/enquiries/store', [EnquiryController::class, 'store']);
$router->get('/admin/enquiries/view', [EnquiryController::class, 'show']);
$router->post('/admin/enquiries/update', [EnquiryController::class, 'update']);
$router->post('/admin/enquiries/followups/store', [EnquiryController::class, 'addFollowup']);
$router->post('/admin/enquiries/followups/complete', [EnquiryController::class, 'completeFollowup']);

// Packages
$router->get('/admin/packages', [PackageController::class, 'index']);
$router->get('/admin/packages/create', [PackageController::class, 'create']);
$router->post('/admin/packages/store', [PackageController::class, 'store']);
$router->get('/admin/packages/view', [PackageController::class, 'show']);
$router->get('/admin/packages/edit', [PackageController::class, 'edit']);
$router->post('/admin/packages/update', [PackageController::class, 'update']);

// Operations
$router->get('/admin/operations', [OperationsController::class, 'dashboard']);

$router->get('/admin/destinations', [OperationsController::class, 'destinations']);
$router->get('/admin/destinations/create', [OperationsController::class, 'destinationCreate']);
$router->post('/admin/destinations/store', [OperationsController::class, 'destinationStore']);
$router->get('/admin/destinations/edit', [OperationsController::class, 'destinationEdit']);
$router->post('/admin/destinations/update', [OperationsController::class, 'destinationUpdate']);
$router->post('/admin/destinations/toggle', [OperationsController::class, 'destinationToggle']);
$router->post('/admin/destinations/delete', [OperationsController::class, 'destinationDelete']);

$router->get('/admin/suppliers', [OperationsController::class, 'suppliers']);
$router->get('/admin/suppliers/create', [OperationsController::class, 'supplierCreate']);
$router->post('/admin/suppliers/store', [OperationsController::class, 'supplierStore']);
$router->get('/admin/suppliers/edit', [OperationsController::class, 'supplierEdit']);
$router->post('/admin/suppliers/update', [OperationsController::class, 'supplierUpdate']);
$router->post('/admin/suppliers/toggle', [OperationsController::class, 'supplierToggle']);

$router->get('/admin/bookings', [OperationsController::class, 'bookings']);
$router->get('/admin/bookings/create', [OperationsController::class, 'bookingCreate']);
$router->post('/admin/bookings/store', [OperationsController::class, 'bookingStore']);
$router->get('/admin/bookings/view', [OperationsController::class, 'bookingShow']);
$router->post('/admin/bookings/status', [OperationsController::class, 'bookingStatus']);
$router->post('/admin/bookings/travellers', [OperationsController::class, 'travellerStore']);
$router->post('/admin/bookings/itinerary', [OperationsController::class, 'itineraryStore']);

$router->get('/admin/payments', [OperationsController::class, 'payments']);
$router->post('/admin/payments/store', [OperationsController::class, 'paymentStore']);
$router->post('/admin/packages/archive-delete', [OperationsController::class, 'packageArchiveDelete']);

// Management Centre
$router->get('/admin/management', [ManagementController::class, 'dashboard']);
$router->get('/admin/management/reports', [ManagementController::class, 'reports']);

$router->get('/admin/management/staff', [ManagementController::class, 'staff']);
$router->get('/admin/management/staff/create', [ManagementController::class, 'staffCreate']);
$router->post('/admin/management/staff/store', [ManagementController::class, 'staffStore']);
$router->get('/admin/management/staff/edit', [ManagementController::class, 'staffEdit']);
$router->post('/admin/management/staff/update', [ManagementController::class, 'staffUpdate']);
$router->post('/admin/management/staff/toggle', [ManagementController::class, 'staffToggle']);
$router->post('/admin/management/staff/delete', [ManagementController::class, 'staffDelete']);

$router->get('/admin/management/roles', [ManagementController::class, 'roles']);
$router->get('/admin/management/audit', [ManagementController::class, 'audit']);

$router->get('/admin/management/settings', [ManagementController::class, 'settings']);
$router->post('/admin/management/settings/save', [ManagementController::class, 'settingSave']);
$router->post('/admin/management/settings/delete', [ManagementController::class, 'settingDelete']);

$router->get('/admin/management/content', [ManagementController::class, 'content']);
$router->post('/admin/management/content/store', [ManagementController::class, 'contentStore']);
$router->get('/admin/management/content/edit', [ManagementController::class, 'contentEdit']);
$router->post('/admin/management/content/update', [ManagementController::class, 'contentUpdate']);
$router->post('/admin/management/content/delete', [ManagementController::class, 'contentDelete']);
// Public API
$router->get('/api/packages', [ApiController::class, 'packages']);
$router->get('/api/destinations', [ApiController::class, 'destinations']);
$router->get('/api/categories', [ApiController::class, 'categories']);

$router->post('/api/enquiries', [EnquiryController::class, 'storePublic']);


// Quotations
$router->get('/admin/quotations', [OperationsController::class, 'quotations']);
$router->get('/admin/quotations/create', [OperationsController::class, 'quotationCreate']);
$router->post('/admin/quotations/store', [OperationsController::class, 'quotationStore']);
$router->get('/admin/quotations/view', [OperationsController::class, 'quotationShow']);
$router->get('/admin/quotations/edit', [OperationsController::class, 'quotationEdit']);
$router->post('/admin/quotations/update', [OperationsController::class, 'quotationUpdate']);
$router->post('/admin/quotations/status', [OperationsController::class, 'quotationStatus']);
$router->post('/admin/quotations/delete', [OperationsController::class, 'quotationDelete']);

