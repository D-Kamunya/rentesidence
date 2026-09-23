@php
    $map = [
        'draft'     => ['label' => __('Draft'),     'cls' => 'ag-badge--muted'],
        'sent'      => ['label' => __('Sent'),      'cls' => 'ag-badge--amber'],
        'viewed'    => ['label' => __('Viewed'),    'cls' => 'ag-badge--blue'],
        'signed'    => ['label' => __('Signed'),    'cls' => 'ag-badge--green'],
        'declined'  => ['label' => __('Declined'),  'cls' => 'ag-badge--coral'],
        'cancelled' => ['label' => __('Cancelled'), 'cls' => 'ag-badge--muted'],
    ];
    $s = $map[$status] ?? ['label' => ucfirst($status), 'cls' => 'ag-badge--muted'];
@endphp
<span class="ag-badge {{ $s['cls'] }}">{{ $s['label'] }}</span>
