@php
    $variant = [
        'active' => 'is-paid', 'paid' => 'is-paid', 'completed' => 'is-paid', 'approved' => 'is-paid',
        'pending' => 'is-pending', 'submitted' => 'is-pending', 'under_review' => 'is-pending',
        'partially_paid' => 'is-pending', 'pending_payment' => 'is-pending', 'deploying' => 'is-pending',
        'overdue' => 'is-danger', 'defaulted' => 'is-danger', 'rejected' => 'is-danger', 'written_off' => 'is-danger',
        'disbursed' => 'is-blue', 'deployed' => 'is-paid',
        'restructured' => 'is-purple',
        'draft' => 'is-grey', 'onboarding' => 'is-grey', 'cancelled' => 'is-grey', 'inactive' => 'is-grey',
    ][$status] ?? 'is-grey';
@endphp
<span class="cs-badge {{ $variant }}">{{ __(ucfirst(str_replace('_', ' ', $status))) }}</span>
