<?php
class Payment {
    private int $payment_id;
    private string $invoice_number;
    private int $patient_id;
    private int $appointment_id;
    private float $amount;
    private string $card_number;
    private string $cvv;
    private string $expiry_date;
    private string $cardholder_name;
    private $payment_date;
    private string $status;

    public function __construct(array $data = []) {
        $this->payment_id       = (int) ($data['payment_id'] ?? 0);
        $this->invoice_number   = $data['invoice_number'] ?? '';
        $this->patient_id       = (int) ($data['patient_id'] ?? 0);
        $this->appointment_id   = (int) ($data['appointment_id'] ?? 0);
        $this->amount           = (float) ($data['amount'] ?? 0.0);
        $this->card_number      = $data['card_number'] ?? '';
        $this->cvv              = $data['cvv'] ?? '';
        $this->expiry_date      = $data['expiry_date'] ?? '';
        $this->cardholder_name  = $data['cardholder_name'] ?? '';
        $this->payment_date     = $data['payment_date'] ?? date('Y-m-d H:i:s');
        $this->status           = $data['status'] ?? 'Pending';
    }
    public function getPaymentId(): int { return $this->payment_id; }
    public function getInvoiceNumber(): string { return $this->invoice_number; }
    public function getAmount(): float { return $this->amount; }
    public function getStatus(): string { return $this->status; }
    public function getAppointmentId(): int { return $this->appointment_id; }
}