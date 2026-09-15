@extends('layouts.customer')

@section('account-content')
    <div class="space-y-6">
        @include('invoices._details', ['downloadRoute' => route('account.invoices.download', $invoice)])
    </div>
@endsection
