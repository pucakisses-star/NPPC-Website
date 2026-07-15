@php use App\DTOs\StripeCheckoutResponseDTO; @endphp
@php
    /** @var StripeCheckoutResponseDTO $DTO */
@endphp

@extends('app')

@section('title', 'Donation Complete | NPPC')

@section('body')
    <h1 class="text-6xl mt-12 mb-12 text-white text-center">Thank you for your donation</h1>

    <div class="border-t border-white w-full my-8"></div>

    <section class="flex justify-center">
        <div class="w-full max-w-2xl">
            <div class="space-y-4 text-white text-lg">
                <div class="flex justify-between border-b border-white py-3">
                    <span class="font-semibold">Amount</span>
                    <span>${{ number_format($DTO->amount / 100, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-white py-3">
                    <span class="font-semibold">Status</span>
                    <span>{{ ucfirst($DTO->status) }}</span>
                </div>
                <div class="flex justify-between border-b border-white py-3">
                    <span class="font-semibold">Payment Status</span>
                    <span>{{ ucfirst($DTO->paymentStatus) }}</span>
                </div>
                <div class="flex justify-between border-b border-white py-3">
                    <span class="font-semibold">Customer Email</span>
                    <span>{{ $DTO->customerEmail }}</span>
                </div>
                <div class="flex justify-between border-b border-white py-3">
                    <span class="font-semibold">Customer Name</span>
                    <span>{{ $DTO->customerName }}</span>
                </div>
            </div>

            <div style="display:flex; justify-content:center; gap:16px; margin-top:48px;">
                <a href="/" style="display:inline-block; border:1px solid rgba(255,255,255,0.3); color:#fff; padding:14px 28px; font-size:14px; font-weight:700; text-decoration:none; transition:all 0.2s;">Return Home</a>
                <a href="/donate" style="display:inline-block; background:#5660fe; color:#fff; padding:14px 28px; font-size:14px; font-weight:700; text-decoration:none; transition:all 0.2s;">Donate Again</a>
            </div>
        </div>
    </section>

@endsection
