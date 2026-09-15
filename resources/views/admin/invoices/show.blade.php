@extends('layouts.admin')

@section('title', 'Invoice '.$invoice->invoice_number)

@section('content')
    @include('invoices._details', ['downloadRoute' => route('admin.invoices.download', $invoice)])
@endsection
