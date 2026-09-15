@extends(auth()->user()->isBusiness() ? 'layouts.business' : 'layouts.saler')

@section('title', 'Invoice '.$invoice->invoice_number)

@section('content')
    @include('invoices._details', ['downloadRoute' => route($routePrefix.'invoices.download', $invoice)])
@endsection
