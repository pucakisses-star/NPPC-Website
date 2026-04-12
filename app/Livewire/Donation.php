<?php

namespace App\Livewire;

use App\Domains\Stripe;
use App\Enum\StripeDonationInterval;
use Illuminate\View\View;
use Livewire\Component;
use Stripe\Exception\ApiErrorException;

class Donation extends Component {
    public $count                           = 0;
    public StripeDonationInterval $interval = StripeDonationInterval::OneTime;
    public ?int $amount                     = 10;
    public ?int $customAmount               = 100;
    public ?int $setAmount                  = 10;
    public ?string $buttonTitle             = '';

    public array $intervalOptions = [
        'One-time' => StripeDonationInterval::OneTime,
        'Monthly'  => StripeDonationInterval::Monthly,
        'Yearly'   => StripeDonationInterval::Yearly,
    ];

    public array $amountOptions = [
        5, 8, 10, 15,
        20, 25, 30, 0,
    ];

    public function mount(): void {
        $this->setButtonTitle();
    }

    public function updated(): void {
        $this->setButtonTitle();
    }

    public function setButtonTitle(): void {
        $title = 'Contribute $'.$this->setAmount;
        if ($this->interval === StripeDonationInterval::Monthly) {
            $title .= ' per month';
        }
        if ($this->interval === StripeDonationInterval::Yearly) {
            $title .= ' per year';
        }

        $this->buttonTitle = $title;
    }

    public function updatedAmount(): void {
        $this->updateSetAmount();
    }

    public function updatedCustomAmount(): void {
        $this->updateSetAmount();
    }

    public function donate(): void {
        if (! $this->setAmount || $this->setAmount < 1 || $this->setAmount > 100000) {
            return;
        }

        try {
            $stripe  = new Stripe();
            $amount  = $this->setAmount * 100;
            $session = $stripe->createPaymentSession($this->interval, $amount);

            $this->dispatch('open-payment-tab', $session->url);
        } catch (ApiErrorException $e) {
            report($e);
            session()->flash('error', 'Payment setup failed. Please try again.');
        }
    }

    public function render(): View {
        return view('livewire.donation');
    }

    private function updateSetAmount(): void {
        $this->setAmount = $this->amount === 0 ? $this->customAmount : $this->amount;
        $this->setButtonTitle();
    }
}
