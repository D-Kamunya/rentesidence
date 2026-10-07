<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RentPaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;
    public $content, $subject;

    public function __construct($content)
    {
        $this->content = $content;
        $this->subject = $content['subject'];
    }

    public function build()
    {
        $invoiceId = $this->content['invoiceId'] ?? null;
        $invoice   = $invoiceId
            ? Invoice::with(['tenant.user', 'landlord', 'property', 'propertyUnit', 'order', 'invoiceItems.invoiceType'])->find($invoiceId)
            : null;

        // Link to the PUBLIC instant-pay page (which doubles as the receipt once paid), so the
        // tenant can open it whether or not they're logged in. The old link went to the auth-gated
        // receipt page, which 403'd anyone who paid via the bearer link without a session.
        if ($invoice && $invoice->payment_token) {
            $this->content['receiptUrl'] = route('instant.invoice.pay', ['token' => $invoice->payment_token]);
        } elseif ($invoiceId) {
            $this->content['receiptUrl'] = route('tenant.invoice.receipt', $invoiceId);
        } else {
            $this->content['receiptUrl'] = route('login');
        }

        $mail = $this->subject($this->subject)
            ->view('mail.rent-payment-success')
            ->with('content', $this->content);

        // Attach a downloadable PDF receipt so the tenant has it straight from the
        // email — best-effort: a PDF failure must never block the confirmation email.
        if ($invoice) {
            try {
                $pdf = Pdf::loadView('mail.pdf.rent-receipt', ['r' => $this->receiptData($invoice)]);
                $fileName = 'Receipt-' . ($invoice->invoice_no ?: $invoiceId) . '.pdf';
                $mail->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);
            } catch (\Throwable $e) {
                Log::warning('Rent receipt PDF attach failed: ' . $e->getMessage());
            }
        }

        return $mail;
    }

    /** Flatten the invoice into the dompdf-friendly receipt fields. */
    private function receiptData(Invoice $invoice): array
    {
        $order  = $invoice->order;
        $paidAt = $order?->updated_at ?? $order?->created_at ?? now();

        $tenantUser = optional($invoice->tenant)->user;
        $tenantName = $tenantUser ? trim($tenantUser->first_name . ' ' . $tenantUser->last_name) : null;

        $landlord     = $invoice->landlord;
        $landlordName = $landlord
            ? ($landlord->print_name ?: trim($landlord->first_name . ' ' . $landlord->last_name))
            : null;

        $propertyLine = trim(
            (optional($invoice->property)->name ?? '')
            . (optional($invoice->propertyUnit)->unit_name ? ' · ' . $invoice->propertyUnit->unit_name : '')
        );

        $type = $invoice->invoiceItems
            ->map(fn ($i) => optional($i->invoiceType)->name)
            ->filter()->unique()->implode(', ');

        return [
            'appName'      => getOption('app_name') ?: config('app.name'),
            'receiptNo'    => $invoice->invoice_no,
            'type'         => $type ?: null,
            'issuedAt'     => $paidAt,
            'billingMonth' => trim($invoice->month . ' ' . optional($paidAt)->format('Y')),
            'tenantName'   => $tenantName ?: null,
            'landlordName' => $landlordName ?: null,
            'propertyLine' => $propertyLine ?: null,
            'method'       => $this->content['method'] ?? __('Payment'),
            'code'         => $this->content['code'] ?? optional($order)->mpesa_transaction_code,
            'amount'       => currencyPrice($invoice->amount),
            'paid'         => true, // this mail only fires on a successful payment
        ];
    }
}
