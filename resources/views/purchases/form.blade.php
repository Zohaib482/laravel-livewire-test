@extends('layouts.app')

@section('title', isset($purchaseId) ? 'Edit Purchase' : 'Create Purchase')

@section('content')
    <livewire:purchases.purchase-form :purchase-id="$purchaseId ?? null" />
@endsection
