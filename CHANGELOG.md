# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2024-01-01

### Added
- Complete KNET Payment Gateway integration
- Form-based payment processing with redirects
- Payment response validation with hash verification
- Refund processing support
- KFAST (KNET Fast Payment) support
- Apple Pay integration support
- Payment status tracking with database models
- Comprehensive error handling
- Laravel 10.x, 11.x, and 12.x compatibility
- Auto-discovery support
- Facade for easy access (`KPay`)
- Database migrations for:
  - Payments table
- Models:
  - `KPayPayment`
- Events:
  - `PaymentStatusUpdated`
- Service methods:
  - `generatePaymentForm()`
  - `validateResponse()`
  - `processResponse()`
  - `processRefund()`
  - `getPaymentByTrackId()`
  - `getPaymentByTransId()`
  - `getPaymentMethods()`

### Security
- SHA-256 hash validation for responses
- CSRF protection (response routes exempt)


