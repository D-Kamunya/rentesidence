<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a payment can't start because the PLATFORM isn't configured yet — e.g. the central
 * M-Pesa receiving account (centresidence_mpesa_account_id) is unset, its gateway/currency is
 * missing, or a bucket has no price. The message is safe to show the user (it says what's wrong in
 * plain terms and points them at support), unlike a raw ModelNotFound/SQL error — so callers can
 * surface getMessage() directly instead of a generic "Payment failed, please try again."
 */
class PaymentConfigException extends RuntimeException
{
}
